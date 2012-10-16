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
    protected $currentTimePeriod;
    
    protected static $foreastsConfigSettings = array(
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => 'Annual', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => 'Quarter', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_month', 'value' => '7', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_day', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiods_shown_forward', 'value' => '4', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiods_shown_backward', 'value' => '4', 'platform' => 'base', 'category' => 'Forecasts')
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        foreach(self::$foreastsConfigSettings as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
        TimePeriod::rebuildForecastingTimePeriods();

        //add all of the newly created timePeriods to the test utils
        $db = DBManagerFactory::getInstance();
        $results = $db->limitQuery('select time_period_type, id from timeperiods where is_leaf = 0 and deleted = 0 order by start_date_timestamp asc', 0,1);
        $row = $db->fetchByAssoc($results);
        $timeperiod = BeanFactory::getBean($row['time_period_type']."TimePeriods",$row['id']);

        do{
            SugarTestTimePeriodUtilities::addTimePeriod($timeperiod);
            $leaves = $timeperiod->getLeaves();
            for($i=0; $i < sizeof($leaves); $i++) {
                SugarTestTimePeriodUtilities::addTimePeriod($leaves[$i]);
            }
            $timeperiod = $timeperiod->getNextTimePeriod();
        } while(!is_null($timeperiod));
        error_log(print_r(SugarTestTimePeriodUtilities::getCreatedTimePeriodIds(), 1));
    }

    public function setUp()
    {
        $this->currentTimePeriod = new AnnualTimePeriod();
        $this->currentTimePeriod->retrieve(TimePeriod::getCurrentId());
    }

    public static function tearDownAfterClass()
    {
        $db = DBManagerFactory::getInstance();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        $db->query("DELETE FROM job_queue where name = ".$db->quoted("TimePeriodAutomationJob"));
        $db->query("UPDATE timeperiods set deleted = 0 WHERE deleted = 1");
        SugarTestHelper::tearDown();
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testNumberOfPrimaryPeriods() {
        $db = DBManagerFactory::getInstance();
        $result = $db->query("select count(id) as count from timeperiods where is_leaf = 0 and deleted = 0");
        $count = $db->fetchByAssoc($result);
        $this->assertEquals(9, $count['count']);
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testNumberOfLeafPeriods() {
        $db = DBManagerFactory::getInstance();
        $result = $db->query("select count(id) as count from timeperiods where is_leaf = 1 and deleted = 0");
        $count = $db->fetchByAssoc($result);
        $this->assertEquals(36, $count['count']);
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfCurrentTimePeriod() {
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

        $this->assertEquals($expectedStartDate->asDbDate(), $this->currentTimePeriod->start_date, "Start Dates do not match");
        $this->assertEquals($expectedEndDate->asDbDate(), $this->currentTimePeriod->end_date, "End Dates do not match");
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfCurrentLeafPeriods() {
        $timeDate = TimeDate::getInstance();
        $leaves = $this->currentTimePeriod->getLeaves();
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
            $this->currentTimePeriod = $this->currentTimePeriod->getPreviousTimePeriod();
            $expectedStartDate = $expectedStartDate->modify("-1 year");
            $expectedEndDate = $expectedEndDate->modify("-1 year");
            $this->assertEquals($expectedStartDate->asDbDate(), $this->currentTimePeriod->start_date, "Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $this->currentTimePeriod->end_date, "End Dates do not match");
        }
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfFutureTimePeriods() {
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
            $this->currentTimePeriod = $this->currentTimePeriod->getNextTimePeriod();
            $expectedStartDate = $expectedStartDate->modify("+1 year");
            $expectedEndDate = $expectedEndDate->modify("+1 year");
            $this->assertEquals($expectedStartDate->asDbDate(), $this->currentTimePeriod->start_date, "Start Dates do not match");
            $this->assertEquals($expectedEndDate->asDbDate(), $this->currentTimePeriod->end_date, "End Dates do not match");
        }
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testDateBoundsOfPreviousLeafPeriods() {
        $timeDate = TimeDate::getInstance();
        for($i = 1; $i <= 4; $i++) {
            $this->currentTimePeriod = $this->currentTimePeriod->getPreviousTimePeriod();
            $leaves = $this->currentTimePeriod->getLeaves();
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
        $timeDate = TimeDate::getInstance();
        for($i = 1; $i <= 4; $i++) {
            $this->currentTimePeriod = $this->currentTimePeriod->getNextTimePeriod();
            $leaves = $this->currentTimePeriod->getLeaves();
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

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testTimePeriodScheduledJob() {
        $timedate = TimeDate::getInstance();
        //grab scheduler job
        $job = $job = BeanFactory::newBean('SchedulersJobs');
        $job->retrieve_by_string_fields(array('name'=>'TimePeriodAutomationJob'));
        //get current time period, expect the next timeperiod to be built at the end of this one
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(),TimePeriod::getCurrentId());
        $expectedEndDate = $timedate->fromDbDate($currentTimePeriod->end_date);

        $actualEndDate = SugarDateTime::createFromFormat($timedate->get_db_date_time_format(), $job->execute_time);
        $this->assertEquals($expectedEndDate->asDbDate(), $actualEndDate->asDbDate());
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testRunTimePeriodScheduledJob() {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();

        //grab scheduler job
        $job = $job = BeanFactory::newBean('SchedulersJobs');
        $job->retrieve_by_string_fields(array('name'=>'TimePeriodAutomationJob'));
        //run the job
        $job->runJob();
        //get current time period, and advance one to check the dates
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(),TimePeriod::getCurrentId());
        $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();

        $expectedEndDate = $timedate->fromDbDate($currentTimePeriod->end_date);

        //add new timeperiods to the test util list so they can be deleted humanely in tear down
        //get the current last time period
        $query = "select id, time_period_type from timeperiods where is_leaf = 0 and deleted = 0 order by end_date_timestamp desc";
        $result = $db->limitQuery($query, 0, 1);
        $row = $db->fetchByAssoc($result);
        $lastTimePeriod = BeanFactory::getBean($row['time_period_type']."TimePeriods", $row['id']);

        SugarTestTimePeriodUtilities::addTimePeriod($lastTimePeriod);
        $leaves = $lastTimePeriod->getLeaves();
        for($i=0; $i < sizeof($leaves); $i++) {
            SugarTestTimePeriodUtilities::addTimePeriod($leaves[$i]);
        }
        $actualEndDate = SugarDateTime::createFromFormat($timedate->get_db_date_time_format(), $job->execute_time);
        //job was supposed to reschedule self for the next time
        $this->assertEquals($expectedEndDate->asDbDate(), $actualEndDate->asDbDate());
    }

    /**
     * test that the forecasting
     * @group timeperiods
     */
    public function testCreatedTimePeriodsRunTimePeriodScheduledJob() {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();
        //get the current last time period
        $query = "select id, time_period_type from timeperiods where is_leaf = 0 and deleted = 0 order by end_date_timestamp desc";
        $result = $db->limitQuery($query, 0, 1);
        $row = $db->fetchByAssoc($result);
        $lastTimePeriod = BeanFactory::getBean($row['time_period_type']."TimePeriods", $row['id']);
        $lastStartDate = $timedate->fromDbDate($lastTimePeriod->start_date);
        $lastEndDate = $timedate->fromDbDate($lastTimePeriod->end_date);
        $lastStartDate = $lastStartDate->modify("+1 year");
        $lastEndDate = $lastEndDate->modify("+1 year");
        //grab scheduler job
        $job = $job = BeanFactory::newBean('SchedulersJobs');
        $job->retrieve_by_string_fields(array('name'=>'TimePeriodAutomationJob'));
        //run the job
        $job->runJob();
        //get the new time period
        $lastTimePeriod = $lastTimePeriod->getNextTimePeriod();

        //add new timeperiods to the test util list so they can be deleted humanely in tear down
        SugarTestTimePeriodUtilities::addTimePeriod($lastTimePeriod);
        $leaves = $lastTimePeriod->getLeaves();
        for($i=0; $i < sizeof($leaves); $i++) {
            SugarTestTimePeriodUtilities::addTimePeriod($leaves[$i]);
        }

        $this->assertNotNull($lastTimePeriod, "scheduled job did not create the new timeperiod as expected");
        $this->assertEquals($timedate->asDbDate($lastStartDate), $lastTimePeriod->start_date);
        $this->assertEquals($timedate->asDbDate($lastEndDate), $lastTimePeriod->end_date);

        //check that it created leaves and the count is right,
        //dates aren't necessary to check, that is checked in other tests
        $this->assertTrue($lastTimePeriod->hasLeaves());
        $leaves = $lastTimePeriod->getLeaves();
        $this->assertEquals(4, count($leaves), "Incorrect Number Of Leaves Created from scheduler job");
    }
}