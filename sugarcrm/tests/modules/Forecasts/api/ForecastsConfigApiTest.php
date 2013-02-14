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
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('moduleList');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $GLOBALS['current_user']->is_admin = 1;
        parent::setUp();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'testSetting' and category = 'Forecasts'");
        $db->commit();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * test the create api
     *
     * @group forecasts
     */
    public function testCreateConfig() {
        // Get the real data that is in the system, not the partial data we have saved

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "testSetting" => "testValue",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], "testValue");

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey("testSetting", $results);
        $this->assertEquals($results['testSetting'], "testValue");
    }

    /**
     * test the get config
     * @group forecasts
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
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->config($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], "testValue");
    }

    /**
     * test the update config
     * @group forecasts
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
            "testSetting" => strrev($testSetting),
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey("testSetting", $results);
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }

    /**
     * test the create api using bad credentials, should receive a failure
     *
     * @group forecasts
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateBadCredentialsConfig() {
        $GLOBALS['current_user']->is_admin = 0;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "testSetting" => "testValue",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayNotHasKey("testSetting", $results);
    }

    /**
     * test the save config calls TimePeriodSettingsChanged
     * @group forecasts
     */
    public function testSaveConfigTimePeriodSettingsChangedCalled() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $priorSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentSettings = $admin->getConfigForModule('Forecasts', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
        );

        $args = array_merge($args, $priorSettings);

        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged'));

        if(empty($priorSettings['is_setup'])) {
            $priorSettings['timeperiod_shown_forward'] = 0;
            $priorSettings['timeperiod_shown_backward'] = 0;
        }

        $apiClass->expects($this->once())
                                ->method('timePeriodSettingsChanged')
                                ->with($priorSettings, $currentSettings);

        $apiClass->forecastsConfigSave($api, $args);
    }

    /**
   	 * @return array asserting data with the key data points changed to test each conditional
   	 */
   	public function getTimePeriodSettingsData()
   	{
   		return array(
               array(
                   array(
                  ),
                  false
               ),
               array(
                   array(
                      'timeperiod_shown_backward' => '3',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_shown_forward' => '3',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_start_date' => '2013-03-01',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_interval' => TimePeriod::QUARTER_TYPE,
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_leaf_interval' => TimePeriod::MONTH_TYPE,
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_type' => 'fiscal',
                  ),
                  true
               ),
   		);
   	}

    /**
     * check the conditionals and that they return expected values for the timePeriodSettingsChanged function
     *
     * @dataProvider getTimePeriodSettingsData
     * @param $changedSettings
     * @param $expectedResult
     * @group forecasts
     */
    public function testTimePeriodSettingsChagned($changedSettings, $expectedResult)
   	{
        $priorSettings = array(
                           'timeperiod_shown_backward' => '2',
                           'timeperiod_shown_forward' => '2',
                           'timeperiod_start_date' => '2013-01-01',
                           'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                           'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
                           'timeperiod_type' => 'chronological',
                       );

        $currentSettings = array_merge($priorSettings, $changedSettings);

        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->timePeriodSettingsChanged($priorSettings, $currentSettings);

        $this->assertEquals($expectedResult, $result, "TimePeriod Setting check failed for given parameters. Prior Settings: " . print_r($priorSettings,1) . " Current Settings: " . print_r($currentSettings, 1) . " result: " . print_r($result,1));
    }


    /**
     * test the save config calls TimePeriodSettingsChanged
     * @group forecasts
     */
    public function testSaveConfigTimePeriodSettingsChangedNotCalled() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $priorSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentSettings = $admin->getConfigForModule('Forecasts', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
        );

        $args = array_merge($args, $priorSettings);

        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged'));

        if(empty($priorSettings['is_setup'])) {
            $priorSettings['timeperiod_shown_forward'] = 0;
            $priorSettings['timeperiod_shown_backward'] = 0;
        }

        $apiClass->expects($this->never())
                                ->method('timePeriodSettingsChanged');

        $apiClass->configSave($api, $args);
    }

}