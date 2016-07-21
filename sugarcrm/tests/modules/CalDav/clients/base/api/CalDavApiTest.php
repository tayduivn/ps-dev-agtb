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

require_once 'include/api/RestService.php';
require_once 'modules/CalDav/clients/base/api/CalDavApi.php';
require_once 'modules/Configurator/Configurator.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Class CalDavApiTest
 *
 * @covers CalDavApi
 */
class CalDavApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User|PHPUnit_Framework_MockObject_MockObject
     */
    protected $userMock;

    /**
     * @var CalDavApi|PHPUnit_Framework_MockObject_MockObject
     */
    protected $calDavApi = null;

    /**
     * @var Configurator|PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurator = null;

    /**
     * @var RestService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $api = null;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry|PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterRegistry = null;

    /**
     * @var Sugarcrm\Sugarcrm\JobQueue\Manager\Manager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobQueueManager = null;

    /**
     * @var array
     */
    protected $supportedModules = array('Calls', 'Meetings');

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(false, true));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('mod_strings', array('CalDav'));

        $this->jobQueueManager = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', array('CalDavRebuild'));
        $this->adapterRegistry = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry');
        $this->adapterRegistry->method('getSupportedModules')->willReturn($this->supportedModules);
        $this->configurator = $this->getMock('Configurator');

        $this->calDavApi = $this->getMock(
            'CalDavApi',
            array('clearMetaDataAPICache', 'getConfigurator', 'getAdapterRegistry', 'getJQManager')
        );
        $this->calDavApi->method('getConfigurator')->willReturn($this->configurator);
        $this->calDavApi->method('getAdapterRegistry')->willReturn($this->adapterRegistry);
        $this->calDavApi->method('getJQManager')->willReturn($this->jobQueueManager);
        $this->api = $this->getMock('RestService');
        $this->api->user = $GLOBALS['current_user'];

        $this->userMock = $this->getMock('User', array('getPreference', 'setPreference'));
        $this->userMock->is_admin = 1;
        $this->userMock->id = Uuid::uuid1();
        $this->userMock->new_with_id = false;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing saving admin configurations with invalid args.
     *
     * @covers       CalDavApi::configSave
     * @dataProvider configProvider
     * @param $args
     * @param $configuratorValue
     * @param $expectedValues
     */
    public function testConfigSaveWithInvalidArgs($args, $configuratorValue, $expectedValues)
    {
        $expectedResult = array(
            'intervals' => $GLOBALS['app_list_strings']['caldav_oldest_sync_date'],
            'call_directions' => $GLOBALS['app_list_strings']['call_direction_dom'],
            'modules' => array(
                'Calls' => $GLOBALS['app_list_strings']['moduleList']['Calls'],
                'Meetings' => $GLOBALS['app_list_strings']['moduleList']['Meetings'],
            ),
            'values' => $expectedValues,
        );

        $this->configurator->config = $configuratorValue;

        $result = $this->calDavApi->configSave($this->api, $args);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testConfigSaveWithInvalidArgs.
     *
     * @see CalDavApiTest::testConfigSaveWithInvalidArgs
     * @return array
     */
    public function configProvider()
    {
        $configuratorValue = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'default_caldav_module' => rand(1000, 9999),
            'default_caldav_interval' => rand(1000, 9999),
            'default_caldav_call_direction' => rand(1000, 9999),
        );
        $configuratorExpectedValues = array(
            'caldav_enable_sync' => $configuratorValue['caldav_enable_sync'],
            'caldav_module' => $configuratorValue['default_caldav_module'],
            'caldav_interval' => $configuratorValue['default_caldav_interval'],
            'caldav_call_direction' => $configuratorValue['default_caldav_call_direction'],
        );

        $invalidArgs = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'caldav_module' => rand(1000, 9999),
            'caldav_interval' => rand(1000, 9999),
            'caldav_call_direction' => rand(1000, 9999),
        );
        $invalidArgsExpectedValues = array(
            'caldav_enable_sync' => $invalidArgs['caldav_enable_sync'],
            'caldav_module' => $configuratorValue['default_caldav_module'],
            'caldav_interval' => $configuratorValue['default_caldav_interval'],
            'caldav_call_direction' => $configuratorValue['default_caldav_call_direction'],
        );

        return array(
            'emptyArgs' => array(
                'args' => array(),
                'configuratorValue' => $configuratorValue,
                'expectedValues' => $configuratorExpectedValues,
            ),
            'invalidArgs' => array(
                'args' => $invalidArgs,
                'configuratorValue' => $configuratorValue,
                'expectedValues' => $invalidArgsExpectedValues,
            ),
        );
    }

    /**
     * Testing case with correct arguments for saving admin config.
     *
     * @covers CalDavApi::configSave
     */
    public function testConfigSaveWithValidArgs()
    {
        $intervals = range(0, 100);
        shuffle($intervals);
        $intervals = array_slice($intervals, 0, 10);
        $GLOBALS['app_list_strings']['caldav_oldest_sync_date'] = $intervals;
        $directions = range(0, 100);
        shuffle($directions);
        $directions = array_slice($directions, 0, 10);
        $GLOBALS['app_list_strings']['call_direction_dom'] = $directions;

        $this->configurator->config = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'default_caldav_module' => rand(1000, 9999),
            'default_caldav_interval' => rand(1000, 9999),
            'default_caldav_call_direction' => rand(1000, 9999),
        );
        $args = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'caldav_module' => $this->supportedModules[array_rand($this->supportedModules)],
            'caldav_interval' => array_rand($intervals),
            'caldav_call_direction' => array_rand($directions),
        );
        $argsExpectedValues = array(
            'caldav_enable_sync' => $args['caldav_enable_sync'],
            'caldav_module' => $args['caldav_module'],
            'caldav_interval' => $args['caldav_interval'],
            'caldav_call_direction' => $args['caldav_call_direction'],
        );

        $result = $this->calDavApi->configSave($this->api, $args);

        $this->assertEquals($argsExpectedValues, $result['values']);
    }

    /**
     * Testing reExporting Calls and Meetings and clearing cache for updating menu.
     *
     * @param boolean $oldEnable
     * @param boolean $argsEnable
     * @param boolean $reExported
     * @param boolean $clearCache
     * @covers       CalDavApi::configSave
     * @dataProvider enableProvider
     */
    public function testConfigSaveWithChangedEnable($oldEnable, $argsEnable, $reExported, $clearCache)
    {
        $this->configurator->config = array(
            'caldav_enable_sync' => $oldEnable,
            'default_caldav_module' => rand(1000, 9999),
            'default_caldav_interval' => rand(1000, 9999),
            'default_caldav_call_direction' => rand(1000, 9999),
        );
        $args = array(
            'caldav_enable_sync' => $argsEnable,
        );
        if ($clearCache) {
            $this->calDavApi->expects($this->once())->method('clearMetaDataAPICache');
        } else {
            $this->calDavApi->expects($this->never())->method('clearMetaDataAPICache');
        }
        if ($reExported) {
            $this->jobQueueManager->expects($this->once())->method('CalDavRebuild');
        } else {
            $this->jobQueueManager->expects($this->never())->method('CalDavRebuild');
        }

        $this->calDavApi->configSave($this->api, $args);
    }

    /**
     * Data provider for testConfigSaveWithChangedEnable.
     *
     * @see CalDavApiTest::testConfigSaveWithChangedEnable
     * @return array
     */
    public function enableProvider()
    {
        return array(
            'saveDisabled' => array(
                'oldEnable' => false,
                'argsEnable' => false,
                'reExported' => false,
                'clearCache' => false,
            ),
            'saveEnabled' => array(
                'oldEnable' => true,
                'argsEnable' => true,
                'reExported' => false,
                'clearCache' => false,
            ),
            'saveDisable' => array(
                'oldEnable' => true,
                'argsEnable' => false,
                'reExported' => false,
                'clearCache' => true,
            ),
            'saveEnable' => array(
                'oldEnable' => false,
                'argsEnable' => true,
                'reExported' => true,
                'clearCache' => true,
            ),
        );
    }

    /**
     * Testing getting admin data.
     *
     * @covers CalDavApi::configGet
     */
    public function testConfigGet()
    {
        $intervals = range(0, 100);
        shuffle($intervals);
        $intervals = array_slice($intervals, 0, 10);
        $GLOBALS['app_list_strings']['caldav_oldest_sync_date'] = $intervals;
        $directions = range(0, 100);
        shuffle($directions);
        $directions = array_slice($directions, 0, 10);
        $GLOBALS['app_list_strings']['call_direction_dom'] = $directions;
        $modules = array();
        foreach ($this->supportedModules as $module) {
            $modules[$module] = $GLOBALS['app_list_strings']['moduleList'][$module];
        }

        $this->configurator->config = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'default_caldav_module' => rand(1000, 9999),
            'default_caldav_interval' => rand(1000, 9999),
            'default_caldav_call_direction' => rand(1000, 9999),
        );
        $argsExpectedValues = array(
            'caldav_enable_sync' => $this->configurator->config['caldav_enable_sync'],
            'caldav_module' => $this->configurator->config['default_caldav_module'],
            'caldav_interval' => $this->configurator->config['default_caldav_interval'],
            'caldav_call_direction' => $this->configurator->config['default_caldav_call_direction'],
        );

        $result = $this->calDavApi->configGet($this->api, array());

        $this->assertEquals($argsExpectedValues, $result['values']);
        $this->assertEquals($directions, $result['call_directions']);
        $this->assertEquals($intervals, $result['intervals']);
        $this->assertEquals($modules, $result['modules']);
    }

    /**
     * Testing not authorized as admin configGet.
     *
     * @covers CalDavApi::configGet
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testNotAuthorizedConfigGet()
    {
        $this->api->user = SugarTestUserUtilities::createAnonymousUser(false, 0);

        $this->calDavApi->configGet($this->api, array());
    }

    /**
     * Testing not authorized as admin configSave.
     *
     * @covers CalDavApi::configSave
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testNotAuthorizedConfigSave()
    {
        $this->api->user = SugarTestUserUtilities::createAnonymousUser(false, 0);

        $this->calDavApi->configSave($this->api, array());
    }

    /**
     * Testing getting user data.
     *
     * @covers CalDavApi::userConfigGet
     */
    public function testUserConfigGet()
    {
        $GLOBALS['current_user'] = $this->userMock;

        $intervals = range(0, 100);
        shuffle($intervals);
        $intervals = array_slice($intervals, 0, 10);
        $GLOBALS['app_list_strings']['caldav_oldest_sync_date'] = $intervals;
        $directions = range(0, 100);
        shuffle($directions);
        $directions = array_slice($directions, 0, 10);
        $GLOBALS['app_list_strings']['call_direction_dom'] = $directions;
        $modules = array();
        foreach ($this->supportedModules as $module) {
            $modules[$module] = $GLOBALS['app_list_strings']['moduleList'][$module];
        }
        $expectedValues = array(
            'caldav_module' => 'Meetings',
            'caldav_interval' => rand(1000, 9999),
            'caldav_call_direction' => rand(1000, 9999),
        );
        $map = array(
            array('caldav_module', 'global', $expectedValues['caldav_module']),
            array('caldav_interval', 'global', $expectedValues['caldav_interval']),
            array('caldav_call_direction', 'global', $expectedValues['caldav_call_direction']),
        );
        $this->userMock->method('getPreference')->will($this->returnValueMap($map));

        $result = $this->calDavApi->userConfigGet($this->api, array());

        $this->assertEquals($expectedValues, $result['values']);
        $this->assertEquals($directions, $result['call_directions']);
        $this->assertEquals($intervals, $result['intervals']);
        $this->assertEquals($modules, $result['modules']);
    }

    /**
     * Testing saving user data.
     *
     * @covers CalDavApi::userConfigSave
     */
    public function testUserConfigSave()
    {
        $GLOBALS['current_user'] = $this->userMock;

        $intervals = range(0, 100);
        shuffle($intervals);
        $intervals = array_slice($intervals, 0, 10);
        $GLOBALS['app_list_strings']['caldav_oldest_sync_date'] = $intervals;
        $directions = range(0, 100);
        shuffle($directions);
        $directions = array_slice($directions, 0, 10);
        $GLOBALS['app_list_strings']['call_direction_dom'] = $directions;
        $modules = array();
        foreach ($this->supportedModules as $module) {
            $modules[$module] = $GLOBALS['app_list_strings']['moduleList'][$module];
        }
        $this->configurator->config = array(
            'caldav_enable_sync' => rand(1000, 9999),
            'default_caldav_module' => rand(1000, 9999),
            'default_caldav_interval' => rand(1000, 9999),
            'default_caldav_call_direction' => rand(1000, 9999),
        );

        $args = array(
            'caldav_module' => array_rand($modules),
            'caldav_interval' => array_rand($intervals),
            'caldav_call_direction' => array_rand($directions),
        );
        $map = array(
            array('caldav_module', 'global', $args['caldav_module']),
            array('caldav_interval', 'global', $args['caldav_interval']),
            array('caldav_call_direction', 'global', $args['caldav_call_direction']),
        );
        $this->userMock->method('getPreference')->will($this->returnValueMap($map));
        $this->userMock->expects($this->exactly(3))
            ->method('setPreference')
            ->withConsecutive(
                array($this->equalTo('caldav_module'), $this->equalTo($args['caldav_module'])),
                array($this->equalTo('caldav_interval'), $this->equalTo($args['caldav_interval'])),
                array($this->equalTo('caldav_call_direction'), $this->equalTo($args['caldav_call_direction']))
            );

        $result = $this->calDavApi->userConfigSave($this->api, $args);

        $this->assertEquals($args, $result['values']);
        $this->assertEquals($directions, $result['call_directions']);
        $this->assertEquals($intervals, $result['intervals']);
        $this->assertEquals($modules, $result['modules']);
    }
}
