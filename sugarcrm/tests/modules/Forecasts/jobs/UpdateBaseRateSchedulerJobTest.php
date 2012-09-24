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

require_once 'modules/SchedulersJobs/SchedulersJob.php';
require_once('modules/Currencies/jobs/BaseRateSchedulerJob.php');

class UpdateBaseRateSchedulerJobTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $jobs = array();
    private $jobRan = FALSE;
    private $currency;
    private $opportunity;
    private $forecast;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        global $current_user;
        $this->currency = SugarTestCurrencyUtilities::createCurrency('UpdateBaseRateSchedulerJob', 'UBRSJ', 'UBRSJ', 1.234);
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        $this->opportunity->currency_id = $this->currency->id;
        $this->opportunity->amount_usdollar = $this->opportunity->amount_usdollar * $this->currency->conversion_rate;
        $this->opportunity->base_rate = $this->currency->conversion_rate;
        $this->opportunity->save();

        $this->forecast = SugarTestForecastUtilities::createForecast($timeperiod, $current_user);
        $this->forecast->currency_id = $this->currency->id;
        $this->forecast->base_rate = $this->currency->conversion_rate;
        $this->forecast->save();

        $this->jobRan = FALSE;
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        if(!empty($this->jobs))
        {
            $jobs = implode("','", $this->jobs);
            $db->query(sprintf("DELETE FROM job_queue WHERE id IN ('%s')", $jobs));
        }

        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        //SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        //SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    protected function createJob($data)
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach($data as $key => $val)
        {
            $job->$key = $val;
        }
        $job->save();
        $this->jobs[] = $job->id;
        return $job;
    }

    /**
     * @outputBuffering disabled
     * @group forecasts
     */
    public function testBaseRateSchedulerJob()
    {
        global $current_user;
        //Set opportunities to be excluded
        $excludeModules = array('opportunities');

        //Change the conversion rate
        $this->currency->conversion_rate = 1.4;
        $this->currency->save();

        $job = $this->createJob(
            array("name" => "UpdateBaseRateSchedulerJob",
                  "status" => SchedulersJob::JOB_STATUS_RUNNING,
                  "target" => "class::MockBaseRateSchedulerJob",
                  "assigned_user_id" => $current_user->id,
                  "data" => json_encode(array('excludeModules'=>$excludeModules, 'currencyId'=>$this->currency->id))
            )
        );

        $job->runJob();
        $job->retrieve($job->id);

        $this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $db = DBManagerFactory::getInstance();
        $oppBaseRate = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $forecastBaseRate = $db->getOne(sprintf("SELECT base_rate FROM forecasts WHERE id = '%s'", $this->forecast->id));

        $this->assertEquals(1.234, $oppBaseRate, 'opportunities.base_rate was modified by BaseRateSchedulerJob');
        $this->assertEquals(1.4, $forecastBaseRate, 'forecasts.base_rate was not modified by BaseRateSchedulerJob');
    }

}

class MockBaseRateSchedulerJob extends BaseRateSchedulerJob
{
    public $job;

    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data;
        $this->job->succeedJob();
        parent::run($data);
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}