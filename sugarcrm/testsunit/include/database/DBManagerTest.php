<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestUnit\inc\database;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * SugarCacheDb tests
 * @coversDefaultClass \SugarCacheDb
 *
 */
class DBManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {

    }

    /**
     * @covers ::updateParams
     * @covers ::getFieldType
     * @covers ::massageValue
     * @covers ::isNullable
     * @covers ::emptyValue
     * @covers ::getColumnWhereClause
     * @dataProvider providerTestUpdateParams
     */
    public function testUpdateParams($table, $field_defs, $data, array $where, $field_map, $usePreparedStatements, $expected)
    {
        $db = $this->getMockDB();

        $result = $db->updateParams($table, $field_defs, $data, $where, $field_map, false, $usePreparedStatements);
        //Remove newlines and excess whitespace
        $result = preg_replace('/[\s]+/', ' ', $result);

        $this->assertEquals($expected, $result);
    }

    public function providerTestUpdateParams()
    {
        return array(
            array(
                'test_table',
                array(
                    'id' => array('name' => 'id', 'type' => 'id'),
                    'none_req_field' => array('name' => 'none_req_field', 'type' => 'varchar'),
                    'req_field_with_default' => array(
                        'name' => 'req_field_with_default', 'type' => 'varchar', 'default' => 'foo', 'required' => true
                    ),
                ),
                array(
                    'none_req_field' => '',
                    'req_field_with_default' => '',
                ),
                array('id' => '1234'),
                null,
                false,
                "UPDATE test_table SET none_req_field=NULL, req_field_with_default='' WHERE test_table.id = '1234'",
            ),
            array(
                'test_table',
                array(
                    'id' => array('name' => 'id', 'type' => 'id'),
                    'bool_field' => array(
                        'name' => 'bool_field', 'type' => 'bool',
                    ),
                    'bool_field_truthy' => array(
                        'name' => 'bool_field', 'type' => 'bool',
                    ),
                    'req_bool_field' => array(
                        'name' => 'bool_field', 'type' => 'bool', 'required' => true
                    ),
                ),
                array(
                    'bool_field' => '',
                    'bool_field_truthy' => true,
                    'req_bool_field' => '',
                ),
                array('id' => '1234'),
                null,
                false,
                "UPDATE test_table SET bool_field=0, bool_field_truthy=1, req_bool_field=0 WHERE test_table.id = '1234'",
            ),
            array(
                'test_table',
                array(
                    'id' => array('name' => 'id', 'type' => 'id'),
                    'int_field' => array(
                        'name' => 'int_field', 'type' => 'int',
                    ),
                    'int_field_with_bool' => array(
                        'name' => 'int_field_with_bool', 'type' => 'int',
                    ),
                    'req_int_field' => array(
                        'name' => 'req_int_field', 'type' => 'int', 'required' => true
                    ),
                    'req_int_field_with_null' => array(
                        'name' => 'req_int_field_with_null', 'type' => 'int', 'required' => true
                    ),
                    'req_int_field_with_null_and_default' => array(
                        'name' => 'req_int_field_with_null_and_default', 'type' => 'int', 'required' => true,
                        'default' => -99
                    ),
                ),
                array(
                    'int_field' => '',
                    'int_field_with_bool' => true,
                    'req_int_field' => '',
                    'req_int_field_with_null' => null,
                    'req_int_field_with_null_and_default' => null,
                ),
                array('id' => '1234'),
                null,
                false,
                "UPDATE test_table SET int_field=NULL, int_field_with_bool=1, req_int_field=0, "
                . "req_int_field_with_null=0, req_int_field_with_null_and_default=0 WHERE test_table.id = '1234'",
            ),
        );
    }

    protected function getMockDB()
    {
        $mock = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods(array('quoted', 'convert'))
            ->getMockForAbstractClass();
        // stub quoted method
        $mock->expects($this->any())
            ->method('quoted')
            ->will($this->returnCallback(array($this, 'dbQuoted')));

        // stub convert method
        $mock->expects($this->any())
            ->method('convert')
            ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Callback for stubbed \DBManager::quoted
     */
    public function dbQuoted()
    {
        $args = func_get_args();
        return "'" . array_shift($args) . "'";
    }

}
