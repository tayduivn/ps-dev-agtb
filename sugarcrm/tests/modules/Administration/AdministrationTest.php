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

class AdministrationTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $configs = array(
        //BEGIN SUGARCRM flav=pro ONLY
        array('name' => 'AdministrationTest', 'value' => 'Base', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => 'Portal', 'platform' => 'portal', 'category' => 'Forecasts'),
        //END SUGARCRM flav=pro ONLY
    );

    public static function setUpBeforeClass()
    {
        sugar_cache_clear('admin_settings_cache');
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        foreach($this->configs as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        $db->commit();
    }

    public function testRetrieveSettingsByInvalidModuleReturnsEmptyArray()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('InvalidModule', 'base');

        $this->assertEmpty($results);
    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function testRetrieveSettingsByValidModuleWithPlatformReturnsOneRow()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(count($results) > 0);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformOverRidesBasePlatform()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'portal');

        $this->assertEquals('Portal', $results['AdministrationTest']);
    }

    public function testCacheExist()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheSameAsReturn()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertSame($results, sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheClearedAfterSave()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $admin->saveSetting("Forecasts", "AdministrationTest", "testCacheClearedAfterSave", "base");

        $this->assertEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }
    //END SUGARCRM flav=pro ONLY
}