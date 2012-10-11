<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('modules/TimePeriods/TimePeriod.php');

class ForecastsTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $foreastsConfigSettings = array(
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => 'Annual', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => 'Quarter', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_month', 'value' => '7', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_day', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiods_shown_forward', 'value' => '4', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiods_shown_backward', 'value' => '4', 'platform' => 'base', 'category' => 'Forecasts'));

    protected static $db;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        self::$db = DBManagerFactory::getInstance();
        self::$db->query("DELETE FROM config where name = 'Forecasts'");
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        foreach(self::$foreastsConfigSettings as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
        TimePeriod::rebuildForecastingTimePeriods();

        parent::setUpBeforeClass();
    }

    public function tearDown() {
        SugarTestHelper::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        self::$db->query("DELETE FROM timeperiods where deleted = 0");
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testNumberOfPrimaryPeriods() {
        $result = self::$db->query("select count(id) as count from timeperiods where is_leaf = 0 and deleted = 0");
        $count = self::$db->fetchByAssoc($result);
        $this->assertEquals(9, $count['count']);
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testNumberOfLeafPeriods() {
        $result = self::$db->query("select count(id) as count from timeperiods where is_leaf = 1 and deleted = 0");
        $count = self::$db->fetchByAssoc($result);
        $this->assertEquals(36, $count['count']);
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfCurrentTimePeriod() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        $now = $timeDate->getNow();
        $expectedStartDate = $timeDate->getNow();
        $expectedEndDate = $timeDate->getNow();
        $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate = $expectedEndDate->modify("-1 day");
        $expectedEndDate = $expectedEndDate->modify("+1 year");
        if($now < $expectedStartDate) {
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
        }

        $this->assertEquals($expectedStartDate->asDbDate(), $currentTimePeriod->start_date, "Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $currentTimePeriod->end_date, "End Dates do not match");
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfCurrentLeafPeriods() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        $leaves = $currentTimePeriod->getLeaves();
        $now = $timeDate->getNow();
        $expectedStartDate = $timeDate->getNow();
        $expectedEndDate = $timeDate->getNow();
        $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate = $expectedEndDate->modify("-1 day");
        $expectedEndDate = $expectedEndDate->modify("+3 month");
        if($now < $expectedStartDate) {
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
        }

        $this->assertEquals($expectedStartDate->asDbDate(), $leaves[0]->start_date, "1st Quarter Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $leaves[0]->end_date, "1st Quarter End Dates do not match");

        $expectedStartDate = $expectedStartDate->modify("+3 month");
        //not every month will have 31 days, or even 30 for that matter
        $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
        $expectedEndDate = $expectedEndDate->modify("+3 month");
        //yes this works, and it is awesome.
        $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

        $this->assertEquals($expectedStartDate->asDbDate(), $leaves[1]->start_date, "2nd Quarter Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $leaves[1]->end_date, "2nd Quarter End Dates do not match");

        $expectedStartDate = $expectedStartDate->modify("+3 month");
        //not every month will have 31 days, or even 30 for that matter
        $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
        $expectedEndDate = $expectedEndDate->modify("+3 month");
        //yes this works, and it is awesome.
        $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

        $this->assertEquals($expectedStartDate->asDbDate(), $leaves[2]->start_date, "3rd Quarter Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $leaves[2]->end_date, "3rd Quarter End Dates do not match");

        $expectedStartDate = $expectedStartDate->modify("+3 month");
        //not every month will have 31 days, or even 30 for that matter
        $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
        $expectedEndDate = $expectedEndDate->modify("+3 month");
        //yes this works, and it is awesome.
        $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

        $this->assertEquals($expectedStartDate->asDbDate(), $leaves[3]->start_date, "4th Quarter Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $leaves[3]->end_date, "4th Quarter End Dates do not match");
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfPreviousTimePeriods() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        $now = $timeDate->getNow();
        $expectedStartDate = $timeDate->getNow();
        $expectedEndDate = $timeDate->getNow();
        $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate = $expectedEndDate->modify("-1 day");
        $expectedEndDate = $expectedEndDate->modify("+1 year");
        if($now < $expectedStartDate) {
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
        }

        for($i = 0; $i < 4; $i++) {
            $currentTimePeriod = $currentTimePeriod->getPreviousTimePeriod();
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
            $this->assertEquals($expectedStartDate->asDbDate(), $currentTimePeriod->start_date, "Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $currentTimePeriod->end_date, "End Dates do not match");
        }
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfFutureTimePeriods() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        $now = $timeDate->getNow();
        $expectedStartDate = $timeDate->getNow();
        $expectedEndDate = $timeDate->getNow();
        $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
        $expectedEndDate = $expectedEndDate->modify("-1 day");
        $expectedEndDate = $expectedEndDate->modify("+1 year");
        if($now < $expectedStartDate) {
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
        }

        for($i = 0; $i < 4; $i++) {
            $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
            $expectedStartDate = $expectedStartDate->modify("+1 year");
            $expectedEndDate = $expectedEndDate->modify("+1 year");
            $this->assertEquals($expectedStartDate->asDbDate(), $currentTimePeriod->start_date, "Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $currentTimePeriod->end_date, "End Dates do not match");
        }
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfPreviousLeafPeriods() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        for($i = 1; $i <= 4; $i++) {
            $currentTimePeriod = $currentTimePeriod->getPreviousTimePeriod();
            $leaves = $currentTimePeriod->getLeaves();
            $now = $timeDate->getNow();
            $expectedStartDate = $timeDate->getNow();
            $expectedEndDate = $timeDate->getNow();
            $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
            $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
            $expectedEndDate = $expectedEndDate->modify("-1 day");
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            if($now < $expectedStartDate) {
                $expectedStartDate = $expectedStartDate->modify("-1 year");
                $expectedEndDate = $expectedEndDate->modify("-1 year");
            }

            $expectedStartDate = $expectedStartDate->modify("-".$i." year");
            $expectedEndDate = $expectedEndDate->modify("-".$i." year");

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[0]->start_date, "1st Quarter of previous year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[0]->end_date, "1st Quarter of previous year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[1]->start_date, "2nd Quarter of previous year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[1]->end_date, "2nd Quarter of previous year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[2]->start_date, "3rd Quarter of previous year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[2]->end_date, "3rd Quarter of previous year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[3]->start_date, "4th Quarter of previous year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[3]->end_date, "4th Quarter of previous year: ".$i." End Dates do not match");
        }
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfNextLeafPeriods() {
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentType()."TimePeriods",TimePeriod::getCurrentId());
        $timeDate = TimeDate::getInstance();
        for($i = 1; $i <= 4; $i++) {
            $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
            $leaves = $currentTimePeriod->getLeaves();
            $now = $timeDate->getNow();
            $expectedStartDate = $timeDate->getNow();
            $expectedEndDate = $timeDate->getNow();
            $expectedStartDate->setDate(intval($now->format("Y")), 7, 1);
            $expectedEndDate->setDate(intval($now->format("Y")), 7, 1);
            $expectedEndDate = $expectedEndDate->modify("-1 day");
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            if($now < $expectedStartDate) {
                $expectedStartDate = $expectedStartDate->modify("-1 year");
                $expectedEndDate = $expectedEndDate->modify("-1 year");
            }

            $expectedStartDate = $expectedStartDate->modify("+".$i." year");
            $expectedEndDate = $expectedEndDate->modify("+".$i." year");

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[0]->start_date, "1st Quarter of future year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[0]->end_date, "1st Quarter of future year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[1]->start_date, "2nd Quarter of future year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[1]->end_date, "2nd Quarter of future year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[2]->start_date, "3rd Quarter of future year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[2]->end_date, "3rd Quarter of future year: ".$i." End Dates do not match");

            $expectedStartDate = $expectedStartDate->modify("+3 month");
            //not every month will have 31 days, or even 30 for that matter
            $expectedEndDate = $expectedEndDate->modify("first day of ".$expectedEndDate->format("M"));
            $expectedEndDate = $expectedEndDate->modify("+3 month");
            //yes this works, and it is awesome.
            $expectedEndDate = $expectedEndDate->modify("last day of ".$expectedEndDate->format("M"));

            $this->assertEquals($expectedStartDate->asDbDate(), $leaves[3]->start_date, "4th Quarter of future year: ".$i." Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $leaves[3]->end_date, "4th Quarter of future year: ".$i." End Dates do not match");
        }
    }

}