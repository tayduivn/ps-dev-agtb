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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Index;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
 *
 */
class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getIndexSettingsFromConfig
     * @dataProvider providerTestGetIndexSettingsFromConfig
     */
    public function testGetIndexSettingsFromConfig($indexName, $config, $output)
    {
        $index = $this->getIndexMock($indexName);
        $indexManager = $this->getIndexManagerMock();
        TestReflection::setProtectedValue($indexManager, 'config', $config);
        TestReflection::setProtectedValue($indexManager, 'defaultSettings', array('setting_Z' => 'core'));
        $settings = TestReflection::callProtectedMethod($indexManager, 'getIndexSettingsFromConfig', array($index));
        $this->assertEquals($settings, $output);
    }

    public function providerTestGetIndexSettingsFromConfig()
    {
        return array(
            // explicit index config + default config + default core
            array(
                'index_foo',
                array(
                    'index_foo' => array (
                        'setting_A' => 'foo',
                        'setting_B' => 'fox',
                    ),
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY =>
                        array(
                            'setting_A' => 'bar',
                            'setting_C' => 'foo',
                        ),
                    'index_bar' => array(),
                ),
                array(
                    'setting_Z' => 'core',
                    'setting_C' => 'foo',
                    'setting_A' => 'foo',
                    'setting_B' => 'fox',
                ),
            ),
            // default config + default core
            array(
                'index_foo',
                array(
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY =>
                        array(
                            'setting_A' => 'bar',
                            'setting_C' => 'foo',
                            'setting_Z' => 'nocore',
                        ),
                    'index_bar' => array(),
                ),
                array(
                    'setting_Z' => 'nocore',
                    'setting_A' => 'bar',
                    'setting_C' => 'foo',
                ),
            ),
            // explicit config with analysis settings (the latter is stripped)
            array(
                'index_foo',
                array(
                    'index_foo' => array (
                        'setting_A' => 'bar',
                        'setting_B' => 'fox',
                        AnalysisBuilder::ANALYSIS => 'quick'
                    ),
                ),
                array (
                    'setting_Z' => 'core',
                    'setting_A' => 'bar',
                    'setting_B' => 'fox',
                ),
            ),
        );
    }

    /**
     * Get IndexManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
     */
    protected function getIndexManagerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Index mock
     * @param string $name
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index
     */
    protected function getIndexMock($name)
    {
        $index = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $index->setBaseName($name);
        return $index;
    }
}
