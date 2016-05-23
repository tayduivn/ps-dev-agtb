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
require_once('modules/Configurator/Configurator.php');

/**
 * Class CaldavApiTest
 * @covers CalDavApi
 */
class CalDavApiTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Default admin settings
     *
     * @var array
     */
    protected $defaultValues;

    /**
     * Default module list
     *
     * @var array
     */
    protected $defaultModules = array(
        'Calls' => 'Calls',
        'Meetings' => 'Meetings',
        'Task' => 'Task',
    );

    /**
     * Value list
     *
     * @var array
     */
    protected $valuesList = array(
        'module',
        'interval',
        'call_direction'
    );

    /**
     * Value list
     *
     * @var array
     */
    protected $contentValuesList = array(
        'module'=>'getSupportedCalDavModules',
        'interval'=>'getOldestSyncDates',
        'call_direction'=>'getCallDirections',
    );

    public function setUp()
    {
        global $sugar_config,$app_list_strings;
        $this->language = $sugar_config['default_language'];
        $app_list_strings = return_app_list_strings_language($this->language, false);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

        $cfg = new Configurator();

        $this->defaultValues = array();
        foreach ($this->valuesList as $valueName) {
            $this->defaultValues['caldav_' . $valueName] = $cfg->config['default_caldav_' . $valueName];
        }

        $apiClass = new CalDavApi();
        $defaultModules = $apiClass->getSupportedCalDavModules();

        foreach ($defaultModules as $val) {
            if (!in_array($val, $this->defaultModules)) {
                $this->defaultModules[] = $val;
            }
        }

        parent::setUp();
    }

    public function tearDown()
    {
        $cfg = new Configurator();
        foreach ($this->defaultValues as $key => $val) {
            $cfg->config['default_'.$key] = $val;
        }
        $cfg->handleOverride();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * test the get admin config
     * @group caldav
     * @covers CalDavApi::configGet
     */
    public function testReadAdminConfig()
    {
        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "caldav",
        );

        $apiClass = $this->getMockBuilder('CalDavApi')
           ->disableOriginalConstructor()
            ->setMethods(array(
                'getSupportedCalDavModules',
            ))
            ->getMock();

        $apiClass->method('getSupportedCalDavModules')->willReturn($this->defaultModules);

        $result = $apiClass->configGet($api, $args);

        $values = $result['values'];

        $this->assertNotEmpty($values);

        foreach ($this->valuesList as $valueName) {
            $this->assertArrayHasKey($valueName . 's', $result);
            $this->assertNotEmpty($result[$valueName . 's']);

            $this->assertArrayHasKey('caldav_' . $valueName, $values);
            $this->assertArrayHasKey($values['caldav_' . $valueName], $result[$valueName . 's']);
        }
    }

    /**
     * test the update admin config
     * @group caldav
     * @covers CalDavApi::configSave
     */
    public function testUpdateAdminConfig()
    {
        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "caldav",
        );

        $apiClass = $this->getMockBuilder('CalDavApi')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSupportedCalDavModules',
            ))
            ->getMock();

        $apiClass->method('getSupportedCalDavModules')->willReturn($this->defaultModules);

        $values = array();
        foreach ($this->valuesList as $valueName) {
            $values[$valueName] = $apiClass->{$this->contentValuesList[$valueName]}();

            $this->assertNotEmpty($values[$valueName]);
            $this->assertArrayHasKey("caldav_" . $valueName, $this->defaultValues);

            $this->assertArrayHasKey($this->defaultValues['caldav_' . $valueName], $values[$valueName]);

            $value=$this->defaultValues['caldav_' . $valueName];
            if (count($values[$valueName])>1) {
                $key = array_search($this->defaultValues['caldav_' . $valueName], $values[$valueName]);
                unset($values[$valueName][$key]);
                $value=array_rand($values[$valueName]);
            }

            $args["caldav_" . $valueName] = $value;
        }

        $result = $apiClass->configSave($api, $args);

        $cfg = new Configurator();

        foreach ($this->valuesList as $valueName) {
            $this->assertEquals($args["caldav_" . $valueName], $cfg->config['default_caldav_' . $valueName]);
        }
    }

    /**
     * test the get user config
     * @group caldav
     * @covers CalDavApi::userConfigGet
     */
    public function testReadUserConfig()
    {
        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "caldav",
        );

        $apiClass = $this->getMockBuilder('CalDavApi')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSupportedCalDavModules',
            ))
            ->getMock();

        $apiClass->method('getSupportedCalDavModules')->willReturn($this->defaultModules);

        $result = $apiClass->userConfigGet($api, $args);

        $this->assertArrayHasKey("values", $result);

        $values = $result['values'];

        $this->assertNotEmpty($values);

        foreach ($this->valuesList as $valueName) {
            $this->assertArrayHasKey($valueName . 's', $result);
            $this->assertNotEmpty($result[$valueName . 's']);

            $this->assertArrayHasKey('caldav_' . $valueName, $values);
            $this->assertArrayHasKey($values['caldav_' . $valueName], $result[$valueName . 's']);
        }
    }

    /**
     * test the update user config
     * @group caldav
     * @covers CalDavApi::userConfigSave
     */
    public function testUpdateUserConfig()
    {
        global $current_user;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "caldav",
        );

        $apiClass = $this->getMockBuilder('CalDavApi')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSupportedCalDavModules',
            ))
            ->getMock();

        $apiClass->method('getSupportedCalDavModules')->willReturn($this->defaultModules);

        $values = array();
        foreach ($this->valuesList as $valueName) {
            $values[$valueName] = $apiClass->{$this->contentValuesList[$valueName]}();

            $this->assertNotEmpty($values[$valueName]);
            $this->assertArrayHasKey($current_user->getPreference('caldav_' . $valueName), $values[$valueName]);

            $value=$this->defaultValues['caldav_' . $valueName];
            if (count($values[$valueName]) > 1) {
                $key = array_search($this->defaultValues['caldav_' . $valueName], $values[$valueName]);
                unset($values[$valueName][$key]);
                $value=array_rand($values[$valueName]);
            }
            $args["caldav_" . $valueName] = $value;
        }

        $result = $apiClass->userConfigSave($api, $args);

        foreach ($this->valuesList as $valueName) {
            $this->assertEquals($args["caldav_" . $valueName], $current_user->getPreference('caldav_' . $valueName));
        }
    }

    /**
     * Testing is correctly generated list of Supported CalDav Modules.
     *
     * @group caldav
     * @covers CalDavApi::getSupportedCalDavModules
     */
    public function testGetSupportedCalDavModules()
    {
        $apiClass = new CalDavApi();
        foreach ($GLOBALS['app_list_strings']['moduleList'] as $module => $moduleTitle) {
            $GLOBALS['app_list_strings']['moduleList'][$module] = $moduleTitle . rand(1000, 9999);
        }
        $modules = $apiClass->getSupportedCalDavModules();

        $this->assertInternalType('array', $modules);
        foreach (array_keys($modules) as $module) {
            $this->assertEquals($GLOBALS['app_list_strings']['moduleList'][$module], $modules[$module]);
        }
    }
}
