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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Aggregation;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\AggregationHandler
 *
 */
class AggregationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getAggDef
     * @dataProvider providerGetAggDef
     *
     * @param string $field : the name of the field
     * @param array $aggDefs : the definitions of the cross module aggregations
     * @param array $moduleAggDefs : the definitions of the module-based aggregations
     * @param array $output : the expected output of the method
     */
    public function testGetAggDef($field, $aggDefs, $moduleAggDefs, $output)
    {
        $handler = $this->getAggregationHandlerMock(
            array('getModuleAggregations')
        );

        $handler->expects($this->any())
            ->method('getModuleAggregations')
            ->will($this->returnValue($moduleAggDefs));

        $result = TestReflection::callProtectedMethod($handler, 'getAggDef', array($field, $aggDefs));

        $this->assertEquals($result, $output);
    }

    public function providerGetAggDef()
    {
        return array(
            array(
                'date_modified',
                array(
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => false,
                            'aggregation' => array()
                        ),
                    ),
                ),
                null,
                array(
                    'name' => 'date_modified',
                    'type' => 'datetime',
                    'full_text_search' => array(
                        'enabled' => true,
                        'searchable' => false,
                        'aggregation' => array()
                    ),
                ),
            ),
            array(
                'Bugs.description',
                array(
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => false,
                            'aggregation' => array()
                        ),
                    ),
                ),
                array(
                    'Bugs.description' => array(
                        'name' => 'description',
                        'type' => 'text',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregation' => array()
                        ),
                    ),
                ),
                array(
                    'name' => 'description',
                    'type' => 'text',
                    'full_text_search' => array(
                        'enabled' => true,
                        'searchable' => true,
                        'aggregation' => array()
                    ),
                ),
            ),
        );
    }


    /**
     * @covers ::getAllAggDefs
     * @dataProvider providerGetAllAggDefs
     *
     * @param array $moduleList : the list of the modules
     * @param array $crossModuleAggDefs : the definitions of the cross-module aggregations
     * @param array $moduleAggDefsMap : the map of the module-based aggregation definitions
     * @param array $output : the expected output of the method
     */
    public function testGetAllAggDefs($moduleList, $crossModuleAggDefs, $moduleAggDefsMap, $output)
    {
        $handler = $this->getAggregationHandlerMock(
            array(
                'getCrossModuleAggregations',
                'getModuleAggregations'
            )
        );

        $handler->expects($this->any())
            ->method('getCrossModuleAggregations')
            ->will($this->returnValue($crossModuleAggDefs));

        $handler->expects($this->any())
            ->method('getModuleAggregations')
            ->will($this->returnValueMap($moduleAggDefsMap));

        $result = TestReflection::callProtectedMethod($handler, 'getAllAggDefs', array($moduleList));

        $this->assertEquals($result, $output);
    }

    public function providerGetAllAggDefs()
    {
        return array(
            array(
                array('Tasks', 'Bugs'),
                array(
                    'date_modified' => array(),
                    'work_log' => array(),
                ),
                array(
                   array(
                        'Tasks',
                        array(
                            'Tasks.name' => array(),
                            'Tasks.description' => array(),
                        ),
                   ),
                   array(
                        'Bugs',
                        array(
                            'Bugs.name' => array(),
                            'Bugs.email' => array(),
                        ),
                   ),
                ),
                array(
                    'date_modified' => array(),
                    'work_log' => array(),
                    'Tasks.name' => array(),
                    'Tasks.description' => array(),
                    'Bugs.name' => array(),
                    'Bugs.email' => array(),
                ),
            ),
        );
    }


    /**
     * @covers ::composeFiltersForAgg
     * @dataProvider providerComposeFiltersForAgg
     *
     * @param string $aggFieldName : the name of the field
     * @param array $aggFilters : the list of aggregation filters definitions
     * @param array $output : the expected output of the method
     */
    public function testComposeFiltersForAgg($aggFieldName, $aggFilters, $output)
    {
        $handler = $this->getAggregationHandlerMock();

        TestReflection::setProtectedValue($handler, 'aggFilters', $aggFilters);

        $result = TestReflection::callProtectedMethod($handler, 'composeFiltersForAgg', array($aggFieldName));

        $this->assertEquals($result->toArray(), $output);
    }

    public function providerComposeFiltersForAgg()
    {
        return array(
            array(
                'date_modified',
                array(
                    'date_modified' => array(),
                    'work_log' => array(),
                ),
                array(
                    "bool" => array("must" => array("0" => array()))
                ),
            ),
            array(
                'Cases.foo',
                array(
                    'date_modified' => array(),
                    'Cases.foo' => array(),
                    'work_log' => array(),
                ),
                array(
                    "bool" => array(
                        "must" => array(
                            "0" => array(),
                            "1" => array(),
                        )
                    )
                ),
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\AggregationHandler
     */
    protected function getAggregationHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\AggregationHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
