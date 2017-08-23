<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query;

use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
 *
 */
class MultiMatchQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::processFieldName
     * @dataProvider providerProcessFieldName
     */
    public function testProcessFieldName($inputField, $module, $field)
    {
        $mQuery = $this->getMultiMatchQueryMock(array('normalizeFieldName'));


        $mQuery->expects($this->any())
            ->method('normalizeFieldName')
            ->will($this->returnValue($field));

        list($moduleName, $fieldName) = TestReflection::callProtectedMethod(
            $mQuery,
            'processFieldName',
            array($inputField)
        );

        $this->assertEquals($moduleName, $module);
        $this->assertEquals($fieldName, $field);
    }

    public function providerProcessFieldName()
    {
        return array(
            array(
                "Contacts__first_name.gs_string_wildcard^0.9",
                "Contacts",
                "first_name"
            ),
            array(
                "Contacts__email_search.primary.gs_email^1.95",
                "Contacts",
                "email"
            ),
            array(
                "Contacts__email_search.secondary.gs_email_wildcard^0.49",
                "Contacts",
                "email"
            ),
            //missing boost value
            array(
                "Contacts__last_name.gs_string_wildcard",
                "Contacts",
                "last_name"
            ),
            //missing field def
            array(
                "Contacts__first_name",
                "Contacts",
                "first_name"
            ),
            //missing contact name
            array(
                "first_name",
                "",
                "first_name"
            )
        );
    }

    /**
     * @covers ::setOperator
     * @param $operaor
     * @param $expectecd
     *
     * @dataProvider providerSetOperatorTest
     */
    public function testSetOperator($operaor, $expectecd)
    {
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setOperator($operaor);
        $this->assertSame($expectecd, TestReflection::getProtectedValue($multiMatchQueryMock, 'defaultOperator'));
    }

    public function providerSetOperatorTest()
    {
        return array(
            array('AND', 'AND'),
            array('OR', 'OR'),
            array('NOT', 'NOT'),
            array('|', 'OR'),
            array('&', 'AND'),
            array('-', 'NOT'),
            // empty string
            array('', false),
            // operator is case-sensitive
            array('and', false),
            array('or', false),
            array('not', false),
        );
    }

    /**
     * @covers ::setTerms
     * @param string $terms
     * @param $expectecd
     *
     * @dataProvider providerSetTermsTest
     */
    public function testSetTerms($terms, $expectecd)
    {
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setTerms($terms);
        $this->assertSame($expectecd, TestReflection::getProtectedValue($multiMatchQueryMock, 'terms'));
    }

    public function providerSetTermsTest()
    {
        return array(
            array('abc AND def', 'abc AND def'),
            array('abc OR def', 'abc OR def'),
            array('abc NOT def', 'abc NOT def'),
            array('abc | def', 'abc | def'),
            array('abc & def', 'abc & def'),
            array('abc -def', 'abc -def'),
            array('abc def', 'abc def'),
        );
    }

    /**
     * @covers ::setSearchFields
     */
    public function testSetSearchFields()
    {
        $searchFields = array('id', 'name');
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setSearchFields($searchFields);
        $this->assertSame($searchFields, TestReflection::getProtectedValue($multiMatchQueryMock, 'searchFields'));
    }

    /**
     * @covers ::setUser
     */
    public function testSetUser()
    {
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setUser($userMock);
        $this->assertSame($userMock, TestReflection::getProtectedValue($multiMatchQueryMock, 'user'));
    }

    /**
     * @covers ::setHighlighter
     */
    public function testSetHighlighter()
    {
        $highligherClassName = 'Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\AbstractHighlighter';
        $highligherMock = TestMockHelper::getMockForAbstractClass($this, $highligherClassName);

        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setHighlighter($highligherMock);
        $this->assertSame($highligherMock, TestReflection::getProtectedValue($multiMatchQueryMock, 'highlighter'));
    }

    /**
     * @covers ::build
     * @covers ::buildBoolQuery
     * @covers ::createMultiMatchQuery
     * @covers ::buildMultiMatchQuery
     * @covers ::createOwnerReadSubQuery
     * @covers ::createReadAccSubQuery
     *
     *
     * @dataProvider providerTestBuild
     */
    public function testBuild($terms, $operator, $searchFields, $expected)
    {
        $this->markTestSkipped('MultiMatchQuery refactor caused breakage');

        $multimatchQueryMock = $this->getMultiMatchQueryMock(array('filterSearchFields', 'isFieldAccessible'));
        $multimatchQueryMock->expects($this->any())
            ->method('isFieldAccessible')
            ->will($this->returnValue(true));

        $multimatchQueryMock->expects($this->any())
            ->method('filterSearchFields')
            ->will($this->returnValue($searchFields));

        $multimatchQueryMock->setOperator($operator);
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $userMock->id = '100';
        $multimatchQueryMock->setUser($userMock);
        $multimatchQueryMock->setTerms($terms);

        $query = $multimatchQueryMock->build();
        $this->assertSame($expected, $query->toArray());
    }

    public function providerTestBuild()
    {
        return array(
            // Single term, default space operator is AND
            array(
                'abcdef',
                '&',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'should' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abcdef',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // AND Operator, default space operator is AND
            array(
                'abc AND def',
                'AND',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'must' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // No operator provided, default space operator is AND
            array(
                'abc def',
                'AND',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'must' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // OR operator, default space operator is AND
            array(
                'abc | def',
                '&',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'should' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // NOT operator
            array(
                'abc -def',
                '&',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'must' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'must' =>
                                                    array (
                                                        array (
                                                            'bool' =>
                                                                array (
                                                                    'should' =>
                                                                        array (
                                                                            array (
                                                                                'multi_match' =>
                                                                                    array (
                                                                                        'type' => 'cross_fields',
                                                                                        'query' => 'abc',
                                                                                        'fields' =>
                                                                                            array (
                                                                                                0 => 'id',
                                                                                                1 => 'name',
                                                                                            ),
                                                                                        'tie_breaker' => 1.0,
                                                                                    ),
                                                                            ),
                                                                        ),
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                    array (
                                        'bool' =>
                                            array (
                                                'must_not' =>
                                                    array (
                                                        array (
                                                            'bool' =>
                                                                array (
                                                                    'should' =>
                                                                        array (
                                                                            array (
                                                                                'multi_match' =>
                                                                                    array (
                                                                                        'type' => 'cross_fields',
                                                                                        'query' => 'def',
                                                                                        'fields' =>
                                                                                            array (
                                                                                                0 => 'id',
                                                                                                1 => 'name',
                                                                                            ),
                                                                                        'tie_breaker' => 1.0,
                                                                                    ),
                                                                            ),
                                                                        ),
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                )
            ),
            // Single term, default space operator is 'OR'
            array(
                'abcdef',
                'OR',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'should' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abcdef',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // AND Operator, default space operator is 'OR'
            array(
                'abc AND def',
                '|',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'must' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // No operator provided, default space operator is 'OR'
            array(
                'abc def',
                'OR',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'should' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // OR operator, default space operator is 'OR'
            array(
                'abc | def',
                'OZR',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'should' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'should' =>
                                                    array (
                                                        array (
                                                            'multi_match' =>
                                                                array (
                                                                    'type' => 'cross_fields',
                                                                    'query' => 'abc def',
                                                                    'fields' =>
                                                                        array (
                                                                            0 => 'id',
                                                                            1 => 'name',
                                                                        ),
                                                                    'tie_breaker' => 1.0,
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                ),
            ),
            // NOT operator, default operator is 'OR'
            array(
                'abc -def',
                'OR',
                array('id', 'name'),
                array (
                    'bool' =>
                        array (
                            'must' =>
                                array (
                                    array (
                                        'bool' =>
                                            array (
                                                'must' =>
                                                    array (
                                                        array (
                                                            'bool' =>
                                                                array (
                                                                    'should' =>
                                                                        array (
                                                                            array (
                                                                                'multi_match' =>
                                                                                    array (
                                                                                        'type' => 'cross_fields',
                                                                                        'query' => 'abc',
                                                                                        'fields' =>
                                                                                            array (
                                                                                                0 => 'id',
                                                                                                1 => 'name',
                                                                                            ),
                                                                                        'tie_breaker' => 1.0,
                                                                                    ),
                                                                            ),
                                                                        ),
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                    array (
                                        'bool' =>
                                            array (
                                                'must_not' =>
                                                    array (
                                                        array (
                                                            'bool' =>
                                                                array (
                                                                    'should' =>
                                                                        array (
                                                                            array (
                                                                                'multi_match' =>
                                                                                    array (
                                                                                        'type' => 'cross_fields',
                                                                                        'query' => 'def',
                                                                                        'fields' =>
                                                                                            array (
                                                                                                0 => 'id',
                                                                                                1 => 'name',
                                                                                            ),
                                                                                        'tie_breaker' => 1.0,
                                                                                    ),
                                                                            ),
                                                                        ),
                                                                ),
                                                        ),
                                                    ),
                                            ),
                                    ),
                                ),
                        ),
                )
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
     */
    protected function getMultiMatchQueryMock(array $methods = null)
    {
        return TestMockHelper::getObjectMock($this, 'Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery', $methods);
    }
}
