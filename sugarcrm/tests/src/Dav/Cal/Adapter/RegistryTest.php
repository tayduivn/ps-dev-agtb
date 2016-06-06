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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry;

/**
 * Class RegistryTest
 * @package Sugarcrm\SugarcrmTests\Dav\Cal\Adapter
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry
 */
class RegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var array */
    protected $originalModuleList;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->registryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry', array(
            'getModulesList'
        ));
    }

    /**
     * Checks getting Registry instance.
     *
     * @covers Registry::getInstance
     */
    public function testGetInstance()
    {
        $instance = Registry::getInstance();
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry', $instance);
    }

    /**
     * Data provider for testGetFactory.
     *
     * @see RegistryTest::testGetFactory
     * @return array
     */
    public function providerGetFactory()
    {
        return array(
            'module is Calls' => array(
                'module' => 'Calls',
                'expected' => 'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory'
            ),
            'module is Meetings' => array(
                'module' => 'Meetings',
                'expected' => 'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\Factory'
            ),
        );
    }

    /**
     * Checks getting Factory for the specified module.
     *
     * @dataProvider providerGetFactory
     * @covers       Registry::getFactory
     * @param string $module
     * @param string $expected
     */
    public function testGetFactory($module, $expected)
    {
        $instance = $this->registryMock->getFactory($module);
        $this->assertInstanceOf($expected, $instance);
    }

    /**
     * Checks Factory with a wrong module.
     * @covers Registry::getFactory
     */
    public function testGetFactoryWithWrongModule()
    {
        $module = 'Wrong' . rand(1000000, 99999999);
        $instance = $this->registryMock->getFactory($module);
        $this->assertNull($instance);
    }

    /**
     * Checks supported modules.
     * @covers Registry::getSupportedModules
     */
    public function testGetSupportedModules()
    {
        $this->registryMock->method('getModulesList')->willReturn(array(
            'Wrong' . rand(1000000, 99999999),
            'Calls',
            'Meetings',
        ));

        $modules = $this->registryMock->getSupportedModules();
        $this->assertEquals(array('Calls', 'Meetings'), $modules);
    }
}
