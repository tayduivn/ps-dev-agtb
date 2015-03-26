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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine\MetaDataHelper;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
 *
 */
class MetaDataHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getFtsFields
     * @dataProvider providerGetFtsFields
     *
     *
     * @param string $module
     * @param array $vardef
     * @param boolean $override
     * @param array $result
     */
    public function testGetFtsFields($module, array $vardef, $override, array $result)
    {
        $helper = $this->getMetaDataHelperMock(array('getModuleVardefs'));
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getModuleVardefs')
            ->will($this->returnValue($vardef));

        $fields = $helper->getFtsFields($module, $override);
        $this->assertEquals($result, $fields);
    }

    public function providerGetFtsFields()
    {
        return array(
            array(
                'Tasks',
                array(
                    'fields' => array(
                        'name' => array(
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => array('enabled' => true, 'searchable' => true),
                        ),
                        'description' => array(
                            'name' => 'description',
                            'type' => 'text',
                        ),
                        'work_log' => array(
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => array('enabled' => false),
                        ),
                        'date_modified' => array(
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                        ),
                    ),
                    'indices' => array(),
                    'relationship' => array(),
                ),
                true,
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'varchar',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
            ),
            // No type override
            array(
                'Tasks',
                array(
                    'fields' => array(
                        'name' => array(
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => array('enabled' => true, 'searchable' => true),
                        ),
                        'description' => array(
                            'name' => 'description',
                            'type' => 'text',
                        ),
                        'work_log' => array(
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => array('enabled' => false),
                        ),
                        'date_modified' => array(
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                        ),
                    ),
                    'indices' => array(),
                    'relationship' => array(),
                ),
                false,
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
            ),
        );
    }


    /**
     * @covers ::getAllAggDefsModule
     * @dataProvider providerGetModuleAggregations
     *
     *
     * @param string $module
     * @param array $vardef
     * @param array $result
     */
    public function testGetAllAggDefsModule($module, array $vardef, array $result)
    {
        $helper = $this->getMetaDataHelperMock(
            array('getFtsFields')
        );
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getFtsFields')
            ->will($this->returnValue($vardef));

        $fields = TestReflection::callProtectedMethod($helper, 'getAllAggDefsModule', array($module));
        $this->assertEquals($result, $fields);
    }

    public function providerGetModuleAggregations()
    {
        return array(
            array(
                'Tasks',
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'description' => array(
                        'name' => 'description',
                        'type' => 'text',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregation' => array(
                                'type' => 'term'
                            )
                        ),
                    ),
                    'work_log' => array(
                        'name' => 'work_log',
                        'type' => 'text',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregation' => array(
                                'type' => 'term',
                                'options' => array('size' => 21, 'order' => 'desc'),
                                'cross_module' => false,
                            ),
                        ),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregation' => array(
                                'type' => 'date_range',
                                'options' => array('from' => 'foo', 'to' => 'bar'),
                                'cross_module' => true,
                            ),
                        ),
                    ),
                ),
                array(
                    'cross' => array(
                        'date_modified' => array(
                            'type' => 'date_range',
                            'options' => array('from' => 'foo', 'to' => 'bar'),
                            'cross_module' => true,
                        ),
                    ),
                    'module' => array(
                        'Tasks.description' => array(
                            'type' => 'term',
                            'options' => array()
                        ),
                        'Tasks.work_log' => array(
                            'type' => 'term',
                            'options' => array('size' => 21, 'order' => 'desc'),
                            'cross_module' => false,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Get MetaDataHelper mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
     */
    protected function getMetaDataHelperMock(array $methods = null)
    {
        $mock = $this->getMockBuilder('Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        // stub out our logger
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        TestReflection::setProtectedValue($mock, 'logger', $logger);

        return $mock;
    }
}
