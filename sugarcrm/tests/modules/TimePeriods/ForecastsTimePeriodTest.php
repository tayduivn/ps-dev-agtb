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


class ForecastsTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    //These are the default forecast configuration settings we will use to test
    protected $forecastConfigSettings = array (
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_month', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_day', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_forward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_backward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts')
    );

    /**
     * Setup global variables
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    /**
     * Call SugarTestHelper to teardown initialization in setUpBeforeClass
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods set deleted = 1');

        $admin = BeanFactory::getBean('Administration');

        foreach($this->forecastConfigSettings as $config)
        {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }

        //Run rebuildForecastingTimePeriods which takes care of creating the TimePeriods based on the configuration data
        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);

        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $timePeriod->rebuildForecastingTimePeriods(array(), $currentForecastSettings);

        //add all of the newly created timePeriods to the test utils
        $result = $db->query('SELECT id, start_date, end_date, time_period_type FROM timeperiods WHERE deleted = 0');
        $createdTimePeriods = array();

        while($row = $db->fetchByAssoc($result))
        {
            $createdTimePeriods[] = TimePeriod::getBean($row['id']);
        }

        SugarTestTimePeriodUtilities::setCreatedTimePeriods($createdTimePeriods);
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        //Remove all created timeperiods used in the test run
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();

        //Remove any job_queue entries
        $db->query("DELETE FROM job_queue where name = ".$db->quoted("TimePeriodAutomationJob"));

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("DELETE FROM timeperiods WHERE deleted = 0");

        //Restore all previously timeperiod entries marked as deleted
        $db->query("UPDATE timeperiods set deleted = 0 WHERE deleted = 1");
    }

    /**
     * testTimePeriodDeleteTimePeriodsWithSamePreviousSettings
     *
     * This test will check
     * 1) That the count of the the timeperiods in the database will be the same before and after the deleteTimePeriods call
     * 2) That the count of the deleted timeperiods will remain the same before and after the deleteTimePeriods calls
     *
     */
    public function testTimePeriodDeleteTimePeriodsWithSamePreviousSettings()
    {
        $admin = BeanFactory::newBean('Administration');
        $prior_forecasts_settings = $admin->getConfigForModule('Forecasts', 'base');

        $timePeriod = BeanFactory::newBean('TimePeriods');
        $this->assertTrue($timePeriod->isSettingIdentical($prior_forecasts_settings, $prior_forecasts_settings));
    }


    /**
     * getShownDifferenceProvider
     *
     * This is the data provider function for getShownDifferenceProvider
     */
    public function getShownDifferenceProvider()
    {
        return array(
           array(1, 2, 'timeperiod_shown_forward', 1),
           array(2, 2, 'timeperiod_shown_forward', 0),
           array(2, 1, 'timeperiod_shown_forward', -1),
           array(1, 2, 'timeperiod_shown_backward', 1),
           array(2, 2, 'timeperiod_shown_backward', 0),
           array(2, 1, 'timeperiod_shown_backward', -1)
        );
    }


    /**
     * This function tests the getShownDifference method in TimePeriod
     *
     * @dataProvider getShownDifferenceProvider
     */
    public function testGetShownDifference($previous, $current, $key, $expected)
    {
        $timePeriod = BeanFactory::getBean('TimePeriods');

        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $priorForecastSettings[$key] = $previous;

        $newConfigSettings = $priorForecastSettings;
        $newConfigSettings[$key] = $current;

        $this->assertEquals($expected, $timePeriod->getShownDifference($priorForecastSettings, $newConfigSettings, $key), sprintf("Failed asserting that %s difference was not %d", $key, $expected));
    }

    /**
     * testIsTargetDateDifferentFromPrevious
     *
     * This test will check the accuracy of the timedate->isTargetDateDifferentFromPrevious method
     *
     */
    public function testIsTargetDateDifferentFromPrevious()
    {
        $timedate = TimeDate::getInstance();
        $timeperiod = BeanFactory::getBean('TimePeriods');

        //First let's check what happens when we pass the same start month and day
        $targetStartDate = $timedate->getNow();
        $targetStartDate->setDate($targetStartDate->format('Y'), 1, 1);

        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is not different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the start_month is different
        $priorForecastSettings['timeperiod_start_month'] = 2;
        $this->assertTrue($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the start_day is different
        $priorForecastSettings['timeperiod_start_month'] = 1;
        $priorForecastSettings['timeperiod_start_day'] = 2;
        $this->assertTrue($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the targetStartDate is one year back
        $targetStartDate->modify('-1 year');
        $priorForecastSettings['timeperiod_start_month'] = 1;
        $priorForecastSettings['timeperiod_start_day'] = 1;
        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the targetStartDate is one year back
        $targetStartDate->modify('+2 year');
        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if there were no previous settings
        $this->assertTrue($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, array()), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));
    }


    /**
     * testIsTargetIntervalDifferent
     *
     */
    public function testIsTargetIntervalDifferent()
    {
        $timeperiod = BeanFactory::getBean('TimePeriods');
        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentForecastSettings = $priorForecastSettings;

        //Check if they're the same
        $this->assertFalse($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));

        //Check if prior settings are empty
        $this->assertTrue($timeperiod->isTargetIntervalDifferent(array(), $currentForecastSettings));

        //Check if timeperiod_interval chagnes
        $currentForecastSettings['timeperiod_interval'] = TimePeriod::QUARTER_TYPE;
        $this->assertTrue($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));

        //Check if timeperiod_leaf_interval chagnes
        $currentForecastSettings['timeperiod_interval'] = TimePeriod::QUARTER_TYPE;
        $currentForecastSettings['timeperiod_leaf_interval'] = 'Month';
        $this->assertTrue($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));
    }


    /**
     * getByTypeDataProvider
     *
     * This is the data provider function for the testGetByType function
     */
    public function getByTypeDataProvider()
    {
        return array(
            array(TimePeriod::ANNUAL_TYPE),
            array(TimePeriod::QUARTER_TYPE)
        );
    }

    /**
     * testGetByType
     *
     * This is a test to check that the TimePeriod::getByType function returns the appropriate TimePeriod bean instance
     * @dataProvider getByTypeDataProvider
     */
    public function testGetByType($type)
    {
        $bean = TimePeriod::getByType($type);
        $this->assertEquals($type, $bean->time_period_type);
    }


    /**
     * testGetLatest
     * 
     */
    public function testGetLatest()
    {
        $timePeriod = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);
        //We don't have any existing data so this should be null
        $this->assertNull($timePeriod);

        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2020-01-01', '2020-03-31');
        $tp1->time_period_type = TimePeriod::ANNUAL_TYPE;
        $tp1->save();

        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2021-01-01', '2021-03-31');
        $tp2->time_period_type = TimePeriod::ANNUAL_TYPE;
        $tp2->save();

        $timePeriod = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($tp2->id, $timePeriod->id);
    }


    /**
     * testGetEarliest
     *
     */
    public function testGetEarliest()
    {
        $timePeriod = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        //We don't have any existing data so this should be null
        $this->assertNull($timePeriod);

        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2020-01-01', '2020-03-31');
        $tp1->time_period_type = TimePeriod::ANNUAL_TYPE;
        $tp1->save();

        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2021-01-01', '2021-03-31');
        $tp2->time_period_type = TimePeriod::ANNUAL_TYPE;
        $tp2->save();

        $timePeriod = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($tp1->id, $timePeriod->id);
    }


    /**
     * testOnlyShownBackwardDifferenceChanged
     *
     */
    public function testOnlyShownBackwardDifferenceChanged()
    {
        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentForecastSettings = $priorForecastSettings;
        $currentForecastSettings['timeperiod_shown_backward'] = 4;

        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);

        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);

        $timedate = TimeDate::getInstance();
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('-2 year')->format('Y'), 1, 1);

        $earliest = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed creating 2 new backward timeperiods');

        $earliest = TimePeriod::getEarliest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed creating 8 leaf timeperiods');

        //Now let's go up to 6 from 4 and see if we create 2 more
        $priorForecastSettings['timeperiod_shown_backward'] = 4;
        $currentForecastSettings['timeperiod_shown_backward'] = 6;
        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('-4 year')->format('Y'), 1, 1);

        $earliest = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed creating 2 more new backward timeperiods');

        $earliest = TimePeriod::getEarliest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed creating 16 leaf timeperiods');

        //Now let's decrement and assert that it does not affect things
        $priorForecastSettings['timeperiod_shown_backward'] = 6;
        $currentForecastSettings['timeperiod_shown_backward'] = 2;
        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('-4 year')->format('Y'), 1, 1);

        $earliest = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed not creating backward timeperiods');

        $earliest = TimePeriod::getEarliest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $earliest->start_date, 'Failed not creating leaf timeperiods');
    }

    /**
     * testOnlyShownForwardDifferenceChanged
     *
     */
    public function testOnlyShownForwardDifferenceChanged()
    {
        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentForecastSettings = $priorForecastSettings;
        $currentForecastSettings['timeperiod_shown_forward'] = 4;

        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);

        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);

        $timedate = TimeDate::getInstance();
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('2 year')->format('Y'), 1, 1);

        $latest = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed creating 2 new foward timeperiods');

        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('2 year')->format('Y'), 10, 1);
        $latest = TimePeriod::getLatest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed creating 8 leaf timeperiods');

        //Now let's go up to 6 from 4 and see if we create 2 more
        $priorForecastSettings['timeperiod_shown_forward'] = 4;
        $currentForecastSettings['timeperiod_shown_forward'] = 6;
        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('4 year')->format('Y'), 1, 1);

        $latest = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed creating 2 more new backward timeperiods');

        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('4 year')->format('Y'), 10, 1);
        $latest = TimePeriod::getLatest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed creating 8 leaf timeperiods');

        //Now let's decrement and assert that it does not affect things
        $priorForecastSettings['timeperiod_shown_forward'] = 6;
        $currentForecastSettings['timeperiod_shown_forward'] = 2;
        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);
        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('4 year')->format('Y'), 1, 1);

        $latest = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed not creating forward timeperiods');

        $expectedDate = $timedate->getNow()->setDate($timedate->getNow()->modify('4 year')->format('Y'), 10, 1);
        $latest = TimePeriod::getLatest(TimePeriod::QUARTER_TYPE);
        $this->assertEquals($expectedDate->asDbDate(), $latest->start_date, 'Failed creating 8 leaf timeperiods');
    }

    /**
     * testTimePeriodDeleteTimePeriodsWithDifferentPreviousSettings
     *
     * This test simulates incrementing the shown_forward and show_backward configuration parameters from 2 to 4
     *
     * This test will check
     * 1) That we have not deleted the previous timeperiods
     * 2) That we create new timeperiods since the show_forward and show_backward values have increased from 2 to 4
     *
     */
    public function testTimePeriodDeleteTimePeriodsWithDifferentPreviousSettings()
    {

    }


    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testNumberOfLeafPeriods() {
        $db = DBManagerFactory::getInstance();
        $result = $db->query("select count(id) as count from timeperiods where is_leaf = 1 and deleted = 0");
        $count = $db->fetchByAssoc($result);
        $this->assertEquals(20, $count['count']);
    }
    */


    /**
     * This is a test to ensure that the current time period was created based on the configuration settings we are testing a few things here
     * 1) That the TimePeriod::getCurrentId() call will return a valid id
     * 2) That the TimePeriod::getCurrentId() will return an id of which is an instance that is a leaf period
     * 3) That the current TimePeriod instance has the correct start date and end dates based on the default settings
     *
     */
    /*
    public function testCreatedCurrentTimePeriod()
    {
        $timedate = TimeDate::getInstance();
        $year = $timedate->getNow()->format('Y');
        $month = $timedate->getNow()->format('m');

        $currentId = TimePeriod::getCurrentId();
        $this->assertNotEmpty($currentId, 'Unable to get id from TimePeriod::getCurrentId() call');

        $currentTimePeriod = TimePeriod::getBean($currentId);
        $this->assertNotEmpty($currentTimePeriod, 'Unable to get TimePeriod instance for id ' . $currentId);

        $this->assertEquals(TimePeriod::QUARTER_TYPE, $currentTimePeriod->time_period_type);

        $startMonthDate = "{$year}-01-01";
        $endMonthDate = "{$year}-03-31";

        switch($month) {
            case 4:
            case 5:
            case 6:
                $startMonthDate = "{$year}-04-01";
                $endMonthDate = "{$year}-06-31";
                break;

            case 7:
            case 8:
            case 9:
                $startMonthDate = "{$year}-07-01";
                $endMonthDate = "{$year}-09-31";
                break;

            case 10:
            case 11:
            case 12:
                $startMonthDate = "{$year}-10-01";
                $endMonthDate = "{$year}-12-31";
                break;
        }

        $this->assertEquals($startMonthDate, $currentTimePeriod->start_date, "Start date for current time period is not " . $startMonthDate);
        $this->assertEquals($endMonthDate, $currentTimePeriod->end_date, "End date for current time period is not " . $endMonthDate);
    }
    */



    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testNumberOfPrimaryPeriods() {
        $db = DBManagerFactory::getInstance();
        $result = $db->query("select count(id) as count from timeperiods where is_leaf = 0 and deleted = 0");
        $count = $db->fetchByAssoc($result);
        $this->assertEquals(9, $count['count']);
    }
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testNumberOfLeafPeriods() {
        $db = DBManagerFactory::getInstance();
        $result = $db->query("select count(id) as count from timeperiods where is_leaf = 1 and deleted = 0");
        $count = $db->fetchByAssoc($result);
        $this->assertEquals(36, $count['count']);
    }
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
    */


    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testDateBoundsOfPreviousLeafPeriods() {
        $this->markTestSkipped('This test may not be setting up the currentTimePeriod correctly');
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testDateBoundsOfNextLeafPeriods() {
        $this->markTestSkipped('This test may not be setting up the currentTimePeriod correctly');
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
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
        $id = $db->getOne($query);
        $lastTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(), $id);

        SugarTestTimePeriodUtilities::addTimePeriod($lastTimePeriod);
        $leaves = $lastTimePeriod->getLeaves();
        for($i=0; $i < sizeof($leaves); $i++) {
            SugarTestTimePeriodUtilities::addTimePeriod($leaves[$i]);
        }
        $actualEndDate = SugarDateTime::createFromFormat($timedate->get_db_date_time_format(), $job->execute_time);
        //job was supposed to reschedule self for the next time
        $this->assertEquals($expectedEndDate->asDbDate(), $actualEndDate->asDbDate());
    }
    */

    /**
     * test that the forecasting
     * @group timeperiods
     */
    /*
    public function testCreatedTimePeriodsRunTimePeriodScheduledJob() {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();
        //get the current last time period
        $query = "select id, time_period_type from timeperiods where is_leaf = 0 and deleted = 0 order by end_date_timestamp desc";
        $id = $db->getOne($query);
        $lastTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(), $id);
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
    */
}