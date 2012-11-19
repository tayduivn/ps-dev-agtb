<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarForecasting/Filter/TimePeriodFilter.php');

class SugarForecasting_Filter_TimePeriodFilterTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $currentSettings;

    /**
     * Setup global variables
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $admin = BeanFactory::getBean('Administration');
        self::$currentSettings = $admin->getConfigForModule('Forecasts', 'base');
    }

    public function setUp() {
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods set deleted = 1');
    }

    /**
     * Call SugarTestHelper to teardown initialization in setUpBeforeClass
     */
    public static function tearDownAfterClass()
    {
        self::updateForecastSettings(self::$currentSettings);
        SugarTestHelper::tearDown();
    }

    public function tearDown() {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM timeperiods WHERE deleted = 0");
        $db->query("UPDATE timeperiods SET deleted = 0");
    }

    /**
     *
     */
    public function testTimePeriodFilterWithAnnualTimePeriods() {
        $forecastConfigSettings = array (
            array('name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
            array('name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
            array('name' => 'timeperiod_start_month', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
            array('name' => 'timeperiod_start_day', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
            array('name' => 'timeperiod_shown_forward', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
            array('name' => 'timeperiod_shown_backward', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts')
        );
        $this->updateForecastSettings($forecastConfigSettings);
        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);
        $timePeriod->rebuildForecastingTimePeriods(array(), $forecastConfigSettings);

        // base file and class name
        $file = 'include/SugarForecasting/Filter/TimePeriodFilter.php';
        $klass = 'SugarForecasting_TimePeriodFilter';

        // check for a custom file exists
        $include_file = get_custom_file_if_exists($file);

        // if a custom file exists then we need to rename the class name to be Custom_
        if($include_file != $file) {
            $klass = "Custom_" . $klass;
        }

        // include the class in since we don't have a auto loader
        require_once($include_file);
        // create the lass

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj->process();
    }

    private function updateForecastSettings($settings) {
        $admin = BeanFactory::getBean('Administration');
        foreach($settings as $setting) {
            $admin->saveSetting('Forecasts', $setting['id'], $setting['value'], 'base');
        }
    }

}