<?php


class SugarQuery_Compiler_SQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @param SugarBean $bean
     * @param array $fields
     *
     * @dataProvider getData
     */
    public function testCompileSelect($bean, $fields)
    {
        $compiler = new SugarQuery_Compiler_SQL($GLOBALS['db']);
        $query = new SugarQuery();
        $query->from(new Contact());
        $query->select($fields);
        $rc = new ReflectionObject($compiler);

        $sugarQuery = $rc->getProperty('sugar_query');
        $sugarQuery->setAccessible(true);
        $sugarQuery->setValue($compiler, new SugarQuery());

        $compileFrom = $rc->getMethod('compileFrom');
        $compileFrom->setAccessible(true);
        $compileFrom->invokeArgs($compiler, array($bean));

        $compileSelect = $rc->getMethod('compileSelect');
        $compileSelect->setAccessible(true);
        $result = $compileSelect->invokeArgs($compiler, array($query->select));
        $result = explode(',', $result);
        $actual = array();
        foreach ($result as $field) {
            $field = explode(' ', trim($field));
            $field = end($field);
            $this->assertNotContains($field, $actual);
            $actual[] = $field;
        }
        $this->assertNotEmpty($actual);
    }

    public static function getData()
    {
        return array(
            // contacts.id should be removed because it's selected by contacts.*
            array(
                new Contact(),
                array(
                    array('contacts.id', 'id'),
                    'contacts.id',
                    'contacts.*',
                ),
                'contacts.*',
            ),
            // first_name, last_name, salutation, title from full_name should be ignored because they're already selected
            array(
                new Contact(),
                array(
                    'first_name',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.salutation',
                    'contacts.title',
                    'full_name',
                ),
            ),
            // we should be able select the same field with different aliases
            array(
                new Contact(),
                array(
                    array('first_name', 'a1'),
                    'first_name',
                ),
            ),
            // account.id should be ignored because we already selected id from contact, maybe we need to log error here
            array(
                new Contact(),
                array(
                    'contacts.id',
                    array('accounts.id', 'id'),
                ),
            ),
        );
    }

    /**
     * @dataProvider compileConditionProvider
     */
    public function testCompileCondition($input, $expected, $isDbCaseInsensitive)
    {
        $this->markTestIncomplete('[BR-3907] Testing SQL doesn\'t work with prepared statements');

        $db = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods(array('supports'))
            ->getMockForAbstractClass();

        $db->expects($this->any())
            ->method('supports')
            ->willReturnMap(array(
                array('case_insensitive', $isDbCaseInsensitive),
            ));

        $query = new SugarQuery();

        /** @var SugarQuery_Builder_Where $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->setMethods(array('dummy'))
            ->disableOriginalConstructor()
            ->getMock();
        $where->query = $query;
        $input($where);
        $condition = array_shift($where->conditions);
        $condition->field->table = 't';

        $compiler = new SugarQuery_Compiler_SQL($db);
        SugarTestReflection::setProtectedValue($compiler, 'sugar_query', $query);
        $sql = SugarTestReflection::callProtectedMethod($compiler, 'compileCondition', array($condition));
        $sql = trim($sql);

        $this->assertContains($expected, $sql);
    }

    public static function compileConditionProvider()
    {
        return array(
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->contains('foo', array('bar', 'baz'));
                },
                "(UPPER(t.foo) LIKE 'BAR' OR UPPER(t.foo) LIKE 'BAZ')",
                true,
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->contains('foo', array('bar', 'baz'));
                },
                "(t.foo LIKE 'bar' OR t.foo LIKE 'baz')",
                false,
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notContains('foo', array('bar', 'baz'));
                },
                "(UPPER(t.foo) NOT LIKE 'BAR' AND UPPER(t.foo) NOT LIKE 'BAZ' OR UPPER(t.foo) IS NULL)",
                true,
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notContains('foo', array('bar', 'baz'));
                },
                "(t.foo NOT LIKE 'bar' AND t.foo NOT LIKE 'baz' OR t.foo IS NULL)",
                false,
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->equals('foo', 'bar');
                },
                "t.foo = 'bar'",
                false,
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->gt('foo', array('$field' => 'bar'));
                },
                "t.foo > t.bar",
                false,
            ),
        );
    }

    /**
     * Testing correct casting usage if field type is text
     *
     * @dataProvider compileConditionOnTextFieldProvider
     * @param array $conditions
     * @param bool|string[] $expectation true/false means expectation of casted field, array means exact match for both base and casted field
     */
    public function testCompileConditionOnTextField(array $conditions, $expectation)
    {
        $castedField = create_guid();
        $baseField = create_guid();

        /** @var SugarQuery_Builder_Field_Condition|PHPUnit_Framework_MockObject_MockObject $field */
        $field = $this->createMock('SugarQuery_Builder_Field_Condition');
        $field->def = array(
            'name' => $baseField,
            'type' => create_guid(),
        );

        /** @var SugarQuery_Builder_Condition|PHPUnit_Framework_MockObject_MockObject $condition */
        $condition = $this->createMock('SugarQuery_Builder_Condition');
        $condition->field = $field;
        foreach ($conditions as $k => $v) {
            $condition->$k = $v;
        }

        /** @var DBManager|PHPUnit_Framework_MockObject_MockObject $db */
        $db = $db = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods(array('isTextType', 'convert'))
            ->getMockForAbstractClass()
        ;
        $db->expects($this->once())->method('isTextType')->with($this->equalTo($field->def['type']))->willReturn(true);
        $db->expects($this->once())->method('convert')->with($this->equalTo($baseField), $this->equalTo('text2char'))->willReturn($castedField);

        /** @var SugarQuery_Compiler_SQL|PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_SQL')
            ->setMethods(['compileField', 'prepareValue', 'getFieldCondition'])
            ->setConstructorArgs([$db])
            ->getMock();
        $compiler->expects($this->at(0))->method('compileField')->with($this->equalTo($field))->willReturn($baseField);

        $actual = $compiler->compileCondition($condition);
        if (is_array($expectation)) {
            $this->assertContains($baseField . ' ' . $expectation[0], $actual);
            $this->assertContains($castedField . ' ' . $expectation[1], $actual);
        } elseif ($expectation == false) {
            $this->assertContains($baseField, $actual);
            $this->assertNotContains($castedField, $actual);
        } else {
            $this->assertNotContains($baseField, $actual);
            $this->assertContains($castedField, $actual);
        }
    }

    /**
     * Data provider for testCompileConditionOnTextField test
     *
     * @see testCompileConditionOnTextField
     * @return array
     */
    public static function compileConditionOnTextFieldProvider()
    {
        return array(
            'isNullReturnsBaseField' => array(array('isNull' => true), false),
            'notNullReturnsBaseField' => array(array('notNull' => true), false),
            'operatorInReturnsCastedField' => array(array('operator' => 'IN'), true),
            'operatorNotInReturnsBothField' => array(array('operator' => 'NOT IN'), array('IS NULL', 'NOT IN')),
            'operatorBetweenReturnsBaseFields' => array(array('operator' => 'BETWEEN', 'values' => array('min' => 0, 'max' => 1)), false),
            'operatorStartsReturnsBaseField' => array(array('operator' => 'STARTS', 'values' => 'some'), false),
            'operatorContainsReturnsBaseField' => array(array('operator' => 'CONTAINS', 'values' => array(1, 2, 3)), false),
            'operatorDoesNotContainReturnsBaseField' => array(array('operator' => 'DOES NOT CONTAIN', 'values' => array(1, 2, 3)), false),
            'operatorEndsReturnsBaseField' => array(array('operator' => 'ENDS', 'values' => 'some'), false),
            'operatorEqualFieldReturnsCastedField' => array(array('operator' => 'EQUALFIELD', 'values' => 'some'), true),
            'operatorNotEqualFieldReturnsCastedField' => array(array('operator' => 'NOTEQUALFIELD', 'values' => 'some'), true),
            'anyOtherOperatorReturnsCastedField' => array(array('operator' => '=', 'values' => 'some'), true),
        );
    }

    /**
     * Test addition of order stability column
     *
     * @param array $args Arguments for SugarQuery_Compiler_SQL::applyOrderByStability
     * @param string $expColumn Expected stability column name to be added
     * @param string $expDirection Expected stability column order direction
     *
     * @covers SugarQuery_Compiler_SQL::applyOrderByStability
     * @group unit
     * @dataProvider dataProviderTestApplyOrderByStability
     */
    public function testApplyOrderByStability($args, $expColumn, $expDirection)
    {
        // SUT
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_SQL')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        // Mock SugarQuery for SUT
        $query = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();

        SugarTestReflection::setProtectedValue($compiler, 'sugar_query', $query);

        $result = SugarTestReflection::callProtectedMethod(
            $compiler,
            'applyOrderByStability',
            $args
        );

        // Test last element in result
        $added = array_pop($result);
        $this->assertInstanceOf('SugarQuery_Builder_Orderby', $added);
        $this->assertEquals(
            $expColumn,
            $added->column->field,
            'Incorrect column used for order stability'
        );

        $this->assertEquals(
            $expDirection,
            $added->column->direction,
            'Incorrect order direction used for the order stability column'
        );
    }

    public function dataProviderTestApplyOrderByStability()
    {
        /** @var SugarQuery_Builder_Orderby|PHPUnit_Framework_MockObject_MockObject $mockOrderBy */
        $mockOrderBy = $this->getMockBuilder('SugarQuery_Builder_Orderby')
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrderBy->direction = 'ASC';
        $mockOrderBy->addField('date_modified');

        return array(
            array(
                array(
                    array(),
                    'fieldx',
                ),
                'fieldx',
                'DESC',
            ),
            array(
                array(
                    array(),
                ),
                'id',
                'DESC',
            ),
            array(
                array(
                    array($mockOrderBy),
                    'fieldy',
                ),
                'fieldy',
                'ASC',
            ),
        );
    }

    /**
     * Test invocation of `ORDER BY` stability based on db capability
     *
     * @param boolean $orderByStability Apply order stability
     * @param boolean $capability DBManager order_stability capability
     * @param boolean $expectedApply Invocation expectation to apply order stability in `ORDER BY`
     *
     * @covers SugarQuery_Compiler_SQL::compileOrderBy
     * @group unit
     * @dataProvider dataProviderTestCompileOrderByStability
     */
    public function testCompileOrderByStability($orderByStability, $capability, $expectedApply)
    {
        // SUT
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_SQL')
            ->disableOriginalConstructor()
            ->setMethods(array('applyOrderByStability'))
            ->getMock();

        // DBManager Mock
        $db = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods(array('supports'))
            ->getMockForAbstractClass();

        $db->expects($this->any())
            ->method('supports')
            ->with($this->equalTo('order_stability'))
            ->will($this->returnValue($capability));

        SugarTestReflection::setProtectedValue($compiler, 'db', $db);

        $expected = $expectedApply ? $this->once() : $this->never();
        $compiler->expects($expected)
            ->method('applyOrderByStability')
            ->will($this->returnValue(array()));

        // Execute test call
        SugarTestReflection::callProtectedMethod(
            $compiler,
            'compileOrderBy',
            array(array(), $orderByStability)
        );

    }

    public function dataProviderTestCompileOrderByStability()
    {
        return array(
            array(true, false, true),
            array(true, true, false),
            array(false, false, false),
            array(false, true, false),
        );
    }
}
