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

require_once 'include/api/RestService.php';
require_once 'modules/CalDav/clients/base/api/CalDavApi.php';
require_once('modules/Configurator/Configurator.php');

/**
 * Class CaldavApiTest
 * @coversDefaultClass \CalDavApi
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
    protected $defaultModules = array('Calls','Meetings','Task');

    public function setUp()
    {
        global $sugar_config,$app_list_strings;
        $this->language = $sugar_config['default_language'];
        $app_list_strings = return_app_list_strings_language($this->language, false);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;

        $cfg = new Configurator();

        $this->defaultValues = array(
            'caldav_module' => $cfg->config['default_caldav_module'],
            'caldav_interval' => $cfg->config['default_caldav_interval']
        );

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
     * @covers \CalDavApi::caldavConfigGet
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

        $result = $apiClass->caldavConfigGet($api, $args);


        $this->assertArrayHasKey("modules", $result);
        $this->assertNotEmpty($result['modules']);

        $this->assertArrayHasKey("intervals", $result);
        $this->assertNotEmpty($result['intervals']);

        $this->assertArrayHasKey("values", $result);

        $values = $result['values'];

        $this->assertNotEmpty($values);

        $this->assertArrayHasKey("caldav_module", $values);
        $this->assertArrayHasKey("caldav_interval", $values);

        $this->assertContains($values['caldav_module'], $result['modules']);
        $this->assertArrayHasKey($values['caldav_interval'], $result['intervals']);

    }

    /**
     * test the update admin config
     * @group caldav
     * @covers \CalDavApi::caldavConfigSave
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

        $modules = $apiClass->getSupportedCalDavModules();

        $this->assertNotEmpty($modules);
        $this->assertArrayHasKey("caldav_module", $this->defaultValues);
        $this->assertContains($this->defaultValues['caldav_module'], $modules);

        $module=$this->defaultValues['caldav_module'];
        if (count($modules)>1) {
            $key = array_search($this->defaultValues['caldav_module'], $modules);
            unset($modules[$key]);
            $module=$modules[array_rand($modules)];
        }

        $intervals = $apiClass->getOldestSyncDates();

        $this->assertNotEmpty($intervals);
        $this->assertArrayHasKey("caldav_interval", $this->defaultValues);
        $this->assertArrayHasKey($this->defaultValues['caldav_interval'], $intervals);

        $interval=$this->defaultValues['caldav_interval'];
        if (count($intervals)>1) {
            unset($intervals[$this->defaultValues['caldav_interval']]);
            $interval=array_rand($intervals);
        }

        $args["caldav_module"] = $module;
        $args["caldav_interval"] = $interval;

        $result = $apiClass->caldavConfigSave($api, $args);

        $cfg = new Configurator();

        $newValues = array(
            'module' => $cfg->config['default_caldav_module'],
            'interval' => $cfg->config['default_caldav_interval']
        );

        $this->assertEquals($module, $newValues['module']);
        $this->assertEquals($interval, $newValues['interval']);

    }

    /**
     * test the get admin config
     * @group caldav
     * @covers \CalDavApi::caldavUserConfigGet
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

        $result = $apiClass->caldavUserConfigGet($api, $args);

        $this->assertArrayHasKey("modules", $result);
        $this->assertNotEmpty($result['modules']);

        $this->assertArrayHasKey("intervals", $result);
        $this->assertNotEmpty($result['intervals']);

        $this->assertArrayHasKey("values", $result);

        $values = $result['values'];

        $this->assertNotEmpty($values);

        $this->assertArrayHasKey("caldav_module", $values);
        $this->assertArrayHasKey("caldav_interval", $values);

        $this->assertContains($values['caldav_module'], $result['modules']);
        $this->assertArrayHasKey($values['caldav_interval'], $result['intervals']);

    }

    /**
     * test the update admin config
     * @group caldav
     * @covers \CalDavApi::caldavUserConfigSave
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

        $modules = $apiClass->getSupportedCalDavModules();

        $this->assertNotEmpty($modules);
        $this->assertContains($current_user->getPreference('caldav_module'), $modules);

        $module=$current_user->getPreference('caldav_module');
        if (count($modules)>1) {
            $key = array_search($current_user->getPreference('caldav_module'), $modules);
            unset($modules[$key]);
            $module=$modules[array_rand($modules)];
        }

        $intervals = $apiClass->getOldestSyncDates();

        $this->assertNotEmpty($intervals);
        $this->assertArrayHasKey($current_user->getPreference('caldav_interval'), $intervals);

        $interval=$current_user->getPreference('caldav_interval');
        if (count($intervals)>1) {
            unset($intervals[$current_user->getPreference('caldav_interval')]);
            $interval=array_rand($intervals);
        }

        $args["caldav_module"] = $module;
        $args["caldav_interval"] = $interval;

        $result = $apiClass->caldavUserConfigSave($api, $args);

        $this->assertEquals($module, $current_user->getPreference('caldav_module'));
        $this->assertEquals($interval, $current_user->getPreference('caldav_interval'));

    }

    /**
     * test
     * @group caldav
     * @covers \CalDavApi::getSupportedCalDavModules
     */
    public function testGetSupportedCalDavModules()
    {
        $apiClass = new CalDavApi();
        $modules = $apiClass->getSupportedCalDavModules();

        $this->assertInternalType('array', $modules);
    }
}
