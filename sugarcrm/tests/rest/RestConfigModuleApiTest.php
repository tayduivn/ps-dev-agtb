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

require_once('tests/rest/RestTestBase.php');

class RestConfigModuleApiTest extends RestTestBase {
    protected $configs = array(
        //BEGIN SUGARCRM flav=pro ONLY
        array('name' => 'AdministrationTest', 'value' => 'Base', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => 'Portal', 'platform' => 'portal', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => '["Portal"]', 'platform' => 'json', 'category' => 'Forecasts'),
        //END SUGARCRM flav=pro ONLY
    );
    public function setUp()
    {
        parent::setUp();

        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        foreach($this->configs as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    public static function setUpBeforeClass()
    {
        sugar_cache_clear('admin_settings_cache');
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest' or name = 'AdministrationSaveTest'");
        $db->commit();
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRetrieveConfigSettingsByValidModuleNoSettings()
    {

        $restReply = $this->_restCall('Opportunities/config?platform=base');
        // now returns an empty array not an error
        $this->assertEmpty($restReply['reply']);
    }

    /**
     * @group rest
     */
    public function testRetrieveConfigSettingsByInvalidModule()
    {
        $restReply = $this->_restCall('OneDoesNotSimplyWalkIntoASugarModule/config?platform=base');
        $this->assertEquals('404', $restReply['info']['http_code']);
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @group rest
     */
    public function testRetrieveSettingsByValidModuleWithPlatformReturnsSettings()
    {
        $restReply = $this->_restCall('Forecasts/config?platform=base');
        $this->assertEquals('200', $restReply['info']['http_code']);
        $this->assertTrue($restReply['reply'] > 0);
    }

    /**
     * @group rest
     */
    public function testRetrieveSettingsByValidModuleWithPlatformOverRidesBasePlatform()
    {
        $restReply = $this->_restCall('Forecasts/config?platform=portal');
        $this->assertEquals('200', $restReply['info']['http_code']);
        $this->assertEquals('Portal', $restReply['reply']['AdministrationTest']);
    }

    /**
     * @group rest
     */
    public function testJsonValueIsArray()
    {
        $restReply = $this->_restCall('Forecasts/config?platform=json');
        $this->assertEquals('200', $restReply['info']['http_code']);
        $this->assertEquals(array("Portal"), $restReply['reply']['AdministrationTest']);
    }

    /**
     * @group rest
     */
    public function testSaveForecastsConfigValueUnauthorizedUser()
    {
        $GLOBALS['current_user']->is_admin = false;
        $GLOBALS['current_user']->save();
        $restReply = $this->_restCall('Forecasts/config?platform=base',json_encode(array('AdministrationSaveTest' => 'My voice is my passport, verify me')),'POST');
        $this->assertEquals('403', $restReply['info']['http_code']);
        $this->assertEquals("Current User not authorized to change Forecasts configuration settings", $restReply['reply']['error_message']);
    }

    /**
     * @group rest
     */
    public function testSaveForecastsConfigValue()
    {
        $GLOBALS['current_user']->is_admin = true;
        $GLOBALS['current_user']->save();
        $restReply = $this->_restCall('Forecasts/config?platform=base',json_encode(array('AdministrationSaveTest' => 'My voice is my passport, verify me')),'POST');
        $this->assertEquals('200', $restReply['info']['http_code']);
        $this->assertEquals('My voice is my passport, verify me', $restReply['reply']['AdministrationSaveTest']);
    }
    //END SUGARCRM flav=pro ONLY

}
