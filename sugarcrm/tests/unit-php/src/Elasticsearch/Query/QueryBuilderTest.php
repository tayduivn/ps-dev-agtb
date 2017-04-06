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

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\MatchAllQuery;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Factory\ElasticaFactory;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder
 *
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::buildPostFilters
     * @dataProvider providerBuildPostFilters
     *
     * @param array $filterParams : a list of post filters' parameters
     * @param array $outputArray : the expected value of the output filter in array format
     */
    public function testBuildPostFilters($filterParams, $outputArray)
    {
        $builder = $this->getQueryBuilderMock();

        $postFilters = array();
        foreach ($filterParams as $key => $value) {
            $termFilter = ElasticaFactory::createNewInstance('Term');
            $termFilter->setTerm($key, $value);
            $postFilters[] = $termFilter;
        }

        $result = TestReflection::callProtectedMethod($builder, 'buildPostFilters', array($postFilters));

        $this->assertEquals($result->toArray(), $outputArray);
    }

    public function providerBuildPostFilters()
    {
        return array(
            array(
                array("_type" => "Accounts", "assigned_user_id" => "seed_max_id"),
                array(
                    "bool" => array(
                        "must" => array(
                            "0" => array("term" => array("_type" => array("value" => "Accounts", "boost" => 1.0))),
                            "1" => array("term" => array(
                                "assigned_user_id" => array("value" => "seed_max_id", "boost" => 1.0),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $containerMock = TestMockHelper::getObjectMock($this, 'Sugarcrm\Sugarcrm\Elasticsearch\Container');
        $queryBuilderMock = new QueryBuilder($containerMock);

        $this->assertInstanceOf(
            '\Sugarcrm\Sugarcrm\Elasticsearch\Container',
            TestReflection::getProtectedValue($queryBuilderMock, 'container')
        );
    }

    /**
     * @covers ::getUser
     * @covers ::setUser
     */
    public function testSetUser()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $queryBuilderMock->setUser($userMock);
        $this->assertSame($userMock, $queryBuilderMock->getUser());
    }

    /**
     * @covers ::disableVisibility
     */
    public function testDisableVisibility()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->disableVisibility();
        $this->assertFalse(TestReflection::getProtectedValue($queryBuilderMock, 'applyVisibility'));
    }

    /**
     * @covers ::setQuery
     */
    public function testSetQuery()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $matchAllQuery = new MatchAllQuery();
        $queryBuilderMock->setQuery($matchAllQuery);
        $this->assertSame($matchAllQuery, TestReflection::getProtectedValue($queryBuilderMock, 'query'));
    }

    /**
     * @covers ::getModules
     * @covers ::setModules
     *
     * @dataProvider providerTestSetModules
     */
    public function testSetModules($disableVisibility, array $modules, array $allowedModules, $expectedModules)
    {
        $queryBuilderMock = $this->getQueryBuilderMock(array('getAllowedModules'));
        $queryBuilderMock->expects($this->any())
            ->method('getAllowedModules')
            ->will($this->returnValue($allowedModules));

        if ($disableVisibility) {
            $queryBuilderMock->disableVisibility();
        }

        $queryBuilderMock->setModules($modules);
        $this->assertSame($expectedModules, $queryBuilderMock->getModules());
    }

    public function providerTestSetModules()
    {
        return array(
            array(
                false,
                array('Accounts', 'Contacts'),
                array('Accounts', 'Emails'),
                array('Accounts', 'Emails'),
            ),
            array(
                true,
                array('Accounts', 'Contacts'),
                array('Accounts', 'Emails'),
                array('Accounts', 'Contacts'),
            ),
        );
    }

    /**
     * @covers ::setLimit
     * @covers ::setOffset
     * @covers ::setSort
     * @covers ::setExplain
     *
     * @dataProvider providerTestSets
     */
    public function testSets($limit, $offset, $sort, $explain, $expected)
    {
        $queryBuilderMock = $this->getQueryBuilderMock();

        if (!empty($limit)) {
            $queryBuilderMock->setLimit($limit);
        }

        if (!empty($offset)) {
            $queryBuilderMock->setOffset($offset);
        }

        if (!empty($sort)) {
            $queryBuilderMock->setSort($sort);
        }

        $queryBuilderMock->setExplain($explain);

        $properties = array('limit', 'offset', 'sort', 'explain');
        foreach($properties as $property) {
            $this->assertSame($expected[$property], TestReflection::getProtectedValue($queryBuilderMock, $property));
        }
    }

    public function providerTestSets()
    {
        return array(
            array(
                10,
                20,
                array('id', 'name'),
                true,
                array(
                    'limit' => 10,
                    'offset' => 20,
                    'sort' => array('id', 'name'),
                    'explain' => true,
                ),
            ),
            array(
                null,
                20,
                array('id', 'name'),
                false,
                array(
                    'limit' => null,
                    'offset' => 20,
                    'sort' => array('id', 'name'),
                    'explain' => false,
                ),
            ),
            array(
                null,
                20,
                array(),
                true,
                array(
                    'limit' => null,
                    'offset' => 20,
                    'sort' => array('_score'), //default sort
                    'explain' => true,
                ),
            ),
        );
    }

    /**
     * @covers ::setHighLighter
     */
    public function testSetHighLighter()
    {
        $highlighter = new Highlighter();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->setHighlighter($highlighter);
        $this->assertSame($highlighter, TestReflection::getProtectedValue($queryBuilderMock, 'highlighter'));
    }

    /**
     * @covers ::build
     * @covers ::buildQuery
     * @covers ::addSettingsAfterBuild
     * @covers ::addFilter
     * @covers ::buildFilters
     */
    public function testBuild()
    {
        // create MultiMatchQuery mock
        $query = TestMockHelper::getObjectMock(
            $this,
            'Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery',
            array('filterSearchFields')
        );
        $query->setTerms('abc');
        $query->expects($this->any())
            ->method('filterSearchFields')
            ->will($this->returnValue(array('id')));

        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->disableVisibility();
        $queryBuilderMock->setQuery($query);
        $queryBuilderMock->setLimit(100);
        $queryBuilderMock->setOffset(10);

        $returnQuery = $queryBuilderMock->build();
        $resultArray = $returnQuery->toArray();
        $this->assertSame(100, $resultArray['size']);
        $this->assertSame(10, $resultArray['from']);
        $this->assertSame(array('_score'), $resultArray['sort']);
        $expecteQuery = array (
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
                                                                'query' => 'abc',
                                                                'fields' =>
                                                                    array (
                                                                        0 => 'id',
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
        );

        $this->assertSame($expecteQuery, $resultArray['query']['bool']['must']);
    }

    /**
     * @covers ::executeSearch
     * @covers ::createResultSet
     */
    public function testExecuteSearch()
    {
        // create MultiMatchQuery mock
        $query = TestMockHelper::getObjectMock(
            $this,
            'Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery',
            array('filterSearchFields')
        );
        $query->setTerms('abc');
        $query->expects($this->any())
            ->method('filterSearchFields')
            ->will($this->returnValue(array('id')));

        // ResultSet Mock
        $resultSetMock = TestMockHelper::getObjectMock($this, 'Elastica\ResultSet');

        // Search Mock
        $searchMock = TestMockHelper::getObjectMock($this, 'Elastica\Search', array('search'));
        $searchMock->expects($this->any())
            ->method('search')
            ->will($this->returnValue($resultSetMock));

        // QueryBuilder Mock
        $queryBuilderMock = $this->getQueryBuilderMock(array('newSearchObject', 'getAllowedModules', 'getReadIndices'));
        $queryBuilderMock->expects($this->any())
            ->method('newSearchObject')
            ->will($this->returnValue($searchMock));

        $queryBuilderMock->expects($this->any())
            ->method('getAllowedModules')
            ->will($this->returnValue(array('Accounts')));

        $queryBuilderMock->expects($this->any())
            ->method('getReadIndices')
            ->will($this->returnValue(array('Accounts')));

        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $queryBuilderMock->setUser($userMock);
        $queryBuilderMock->setModules(array('Accounts'));
        $queryBuilderMock->setQuery($query);
        $queryBuilderMock->disableVisibility();

        // call executeSearch
        $searchResult = $queryBuilderMock->executeSearch();
        $this->assertEmpty($searchResult->getResponse());
    }

    /**
     * @covers ::executeSearch
     *
     * @expectedException \Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException
     */
    public function testExcuteSearchNoUserException()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->executeSearch();
    }

    /**
     * @covers ::executeSearch
     *
     * @expectedException \Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException
     */
    public function testExcuteSearchNoModuleException()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $queryBuilderMock->setUser($userMock);

        $queryBuilderMock->executeSearch();
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder
     */
    protected function getQueryBuilderMock(array $methods = null)
    {
        return TestMockHelper::getObjectMock($this, 'Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder', $methods);
    }
}
