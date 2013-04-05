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
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts', 'base');
        $settingsToRestore = array('timeperod_interval', 'timeperiod_leaf_interval', 'timeperiod_start_date', 'timeperiod_shown_forward', 'timeperiod_shown_backward');
        foreach($settingsToRestore as $id) {
            if(isset($settings[$id])) {
                self::$currentSettings[$id] = $settings[$id];
            }
        }
    }

    public function setUp() {
        parent::setUp();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE timeperiods set deleted = 1");
    }

    /**
     * Call SugarTestHelper to teardown initialization in setUpBeforeClass
     */
    public static function tearDownAfterClass()
    {
        self::updateForecastSettings(self::$currentSettings);        
    }

    public function tearDown() {
        SugarTestHelper::tearDown();
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM timeperiods WHERE deleted = 0");
        $db->query("UPDATE timeperiods SET deleted = 0");
        parent::tearDown();
    }

    public function timePeriodFilterWithTimePeriodsProvider() {
        $timedate = TimeDate::getInstance();
        $now = $timedate->getNow(false);
        $year = $now->format('Y');
        return array(
            array(TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 1, 1, 12),
            array(TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 2, 2, 20),
            array(TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 2, 1)->asDbDate(), 1, 1, 12),
            array(TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 1, 1, 9),
            array(TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 2, 2, 15),
            array(TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 2, 1)->asDbDate(), 2, 2, 15),
        );
    }

    /**
     * This is a test to check that the SugarForecasting_Filter_TimePeriodFilter class returns the appropriate timeperiods based on the settings
     * for the timeperiod type and the shown forward/backward settings.
     *
     * @group forecasts
     * @group timeperiods
     * @dataProvider timePeriodFilterWithTimePeriodsProvider
     */
    public function testTimePeriodFilterWithTimePeriods($parentType, $leafType, $startDate, $shownForward, $shownBackward, $expectedLeaves) {

        $forecastConfigSettings = array (
            'timeperiod_interval' => $parentType,
            'timeperiod_leaf_interval' => $leafType,
            'timeperiod_start_date' => $startDate,
            'timeperiod_shown_forward' => $shownForward,
            'timeperiod_shown_backward' => $shownBackward
        );

        $this->updateForecastSettings($forecastConfigSettings);

        $admin = BeanFactory::getBean('Administration');
        $settings =  $admin->getConfigForModule('Forecasts', 'base');

        $timePeriod = TimePeriod::getByType($parentType);
        $timePeriod->rebuildForecastingTimePeriods(array(), $settings);

        $obj = new SugarForecasting_Filter_TimePeriodFilter(array());
        $this->assertEquals($expectedLeaves, count($obj->process()));

        //Now assert that the leaf_cycle is 1 according to the specified start month
        $timedate = TimeDate::getInstance();
        $timePeriodToCheck = TimePeriod::getEarliest($leafType);

        while($timePeriodToCheck != null) {
            if($timedate->fromDbDate($timePeriodToCheck->start_date)->format('n') == $timedate->fromDbDate($startDate)->format('n')) {
                $this->assertEquals(1, $timePeriodToCheck->leaf_cycle);
            }
            $timePeriodToCheck = $timePeriodToCheck->getNextTimePeriod();
        }

    }

    private function updateForecastSettings($settings) {
        $admin = BeanFactory::getBean('Administration');
        foreach($settings as $id=>$value) {
            $admin->saveSetting('Forecasts', $id, $value, 'base');
        }
    }

}