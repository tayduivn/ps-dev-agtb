<?php
//FILE SUGARCRM flav=pro ONLY
//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once 'modules/SchedulersJobs/SchedulersJob.php';

class SugarJobCreateNextTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $preTestIds = array();

    //These are the default forecast configuration settings we will use to test
    protected $forecastConfigSettings = array (
        'timeperiod_type' => 'chronological',
        'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
        'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
        'timeperiod_start_date' => '2012-01-01',
        'timeperiod_shown_forward' => '2',
        'timeperiod_shown_backward' => '2'
    );

    public static function setUpBeforeClass()
    {
        
        global $current_user;
        $current_user->is_admin = 1;
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $this->postSetUp();
    }

    protected function postSetUp($timePeriodType=TimePeriod::ANNUAL_TYPE)
    {
        SugarTestForecastUtilities::setUpForecastConfig($this->forecastConfigSettings);

        error_log('-----------------------');
        error_log(var_export($this->forecastConfigSettings, true));

        //Run rebuildForecastingTimePeriods which takes care of creating the TimePeriods based on the configuration data
        $timePeriod = TimePeriod::getByType($timePeriodType);
        $admin = BeanFactory::getBean('Administration');
        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');

        error_log(var_export($currentForecastSettings, true));

        $timePeriod->rebuildForecastingTimePeriods(array(), $currentForecastSettings);
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();

        $db = DBManagerFactory::getInstance();

        //Remove any job_queue entries
        $db->query("DELETE FROM job_queue where name = ".$db->quoted("SugarJobCreateNextTimePeriod"));

        $db->query("UPDATE timeperiods set deleted = 1");

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds))  . "')");

        $db->query("DELETE FROM timeperiods WHERE deleted = 1");
        parent::tearDown();
    }

    /**
     * @group timeperiods
     * @group forecasts
     * @outputBuffering disabled
     */
    public function testSugarJobCreateNextTimePeriodJobForAnnualParent()
    {
        global $current_user;
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('Forecasts', 'base');

        $timeperiodInterval = $config['timeperiod_interval'];
        $timeperiodLeafInterval = $config['timeperiod_leaf_interval'];

        $parentTimePeriod = TimePeriod::getLatest($timeperiodInterval);
        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $currentTimePeriod = TimePeriod::getCurrentTimePeriod($timeperiodLeafInterval);

        $timedate = TimeDate::getInstance();

        //We run the rebuild command if the latest TimePeriod is less than the specified configuration interval from the current TimePeriod
        $correctStartDate = $timedate->fromDbDate($currentTimePeriod->start_date);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        $shownForward = $config['timeperiod_shown_forward'];

        //Move the current start date forward by the leaf period amounts
        for($x=0; $x < $shownForward; $x++) {
            $correctStartDate->modify($parentTimePeriod->next_date_modifier);
        }

        $this->assertGreaterThanOrEqual($correctStartDate, $latestStartDate);

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        //After the job runs, the $correctStartDate should be set and this should no longer be greater than $latestStartDate
        $this->assertFalse($correctStartDate > $latestStartDate);

        //Now if we run the queue again, retrieving the latest TimePeriod a second time should return the newly created leaf timeperiod
        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $latestTimePeriod2 = TimePeriod::getLatest($timeperiodLeafInterval);
        $this->assertEquals($latestTimePeriod->id, $latestTimePeriod2->id);
    }


    /**
     * @group timeperiods
     * @group forecasts
     */
    public function testSugarJobCreateNextTimePeriodJobForQuarterParent()
    {
        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $this->forecastConfigSettings = array (
            'timeperiod_type' => 'chronological',
            'timeperiod_interval' => TimePeriod::QUARTER_TYPE,
            'timeperiod_leaf_interval' => TimePeriod::MONTH_TYPE,
            'timeperiod_start_month' => '1',
            'timeperiod_start_day' => '1',
            'timeperiod_shown_forward' => '8',
            'timeperiod_shown_backward' => '8'
        );

        $this->postSetUp(TimePeriod::QUARTER_TYPE);
        global $current_user;
        $timeperiodLeafInterval = TimePeriod::MONTH_TYPE;

        $parentTimePeriod = TimePeriod::getLatest(TimePeriod::QUARTER_TYPE);
        $latestTimePeriod = TimePeriod::getLatest(TimePeriod::MONTH_TYPE);
        $currentTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::MONTH_TYPE);

        $timedate = TimeDate::getInstance();

        //We run the rebuild command if the latest TimePeriod is less than the specified configuration interval from the current TimePeriod
        $correctStartDate = $timedate->fromDbDate($currentTimePeriod->start_date);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);



        $shownForward = 8;
        //Move the current start date forward by the leaf period amounts
        for($x=0; $x < $shownForward; $x++) {
            $correctStartDate->modify($parentTimePeriod->next_date_modifier);
        }

        $this->assertGreaterThanOrEqual($correctStartDate, $latestStartDate);

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        //After the job runs, the $correctStartDate should be set and this should no longer be greater than $latestStartDate
        $this->assertFalse($correctStartDate > $latestStartDate);

        //Now if we run the queue again, retrieving the latest TimePeriod a second time should return the newly created leaf timeperiod
        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $latestTimePeriod2 = TimePeriod::getLatest($timeperiodLeafInterval);
        $this->assertEquals($latestTimePeriod->id, $latestTimePeriod2->id);
    }


}
