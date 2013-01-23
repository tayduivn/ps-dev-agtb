<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'include/api/RestService.php';
require_once 'modules/Forecasts/clients/base/api/ForecastsConfigApi.php';

class ForecastsConfigModuleApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp(){
        global $beanFiles, $beanList;
        require('include/modules.php');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $GLOBALS['current_user']->is_admin = 1;
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'testSetting'");
        $db->commit();
    }

    /**
     * test the create api
     */
    public function testCreateConfig() {
        // Get the real data that is in the system, not the partial data we have saved

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "platform" => "base",
            "testSetting" => "testValue",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertTrue(array_key_exists("testSetting", $result));
        $this->assertEquals($result['testSetting'], "testValue");

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(array_key_exists("testSetting", $results));
        $this->assertEquals($results['testSetting'], "testValue");
    }

    /**
     * test the get config
     */
    public function testReadConfig() {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', 'testValue', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
            "platform" => "base",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->config($api, $args);
        $this->assertTrue(array_key_exists("testSetting", $result));
        $this->assertEquals($result['testSetting'], "testValue");
    }

    /**
     * test the update config
     */
    public function testUpdateConfig() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
            "platform" => "base",
            "testSetting" => strrev($testSetting),
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertTrue(array_key_exists("testSetting", $result));
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(array_key_exists("testSetting", $results));
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }

    /**
     * test the create api using bad credentials, should receive a failure
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateBadCredentialsConfig() {
        $GLOBALS['current_user']->is_admin = 0;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "platform" => "base",
            "testSetting" => "testValue",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertFalse(array_key_exists("testSetting", $results));
    }



    /**
     * test the save config calls TimePeriodSettingsChanged
     */
    public function testSaveConfigTimePeriodSettingsChangedCalled() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
            "platform" => "base",
            "testSetting" => strrev($testSetting),
        );

        $apiClass = new SpyingForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertTrue(array_key_exists("testSetting", $result));
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $this->assertTrue($apiClass->getTimePeriodSettingsChangedCalled());

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(array_key_exists("testSetting", $results));
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }


}

class SpyingForecastsConfigApi extends ForecastsConfigApi {

    private $timePeriodSettingsChangedCalled = false;
    /*
     * stubbed method to
     */
    public function timePeriodSettingsChanged($priorSettings, $currentSettings) {
        $this->timePeriodSettingsChangedCalled = true;
        return false;
    }
    public function getTimePeriodSettingsChangedCalled() {
        return $this->timePeriodSettingsChangedCalled;
    }

}
