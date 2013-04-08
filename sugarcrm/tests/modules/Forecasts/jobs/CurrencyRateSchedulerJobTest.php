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
require_once 'include/SugarCurrency/CurrencyRateUpdateAbstract.php';

class CurrencyRateSchedulerJobTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $currency;
    private $opportunity;
    private $opportunityClosed;
    private $quota;
    private $forecast;
    //private $forecastSchedule;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        global $current_user;
        $this->currency = SugarTestCurrencyUtilities::createCurrency('UpdateBaseRateSchedulerJob', 'UBRSJ', 'UBRSJ', 1.234);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->currency_id = $this->currency->id;
        $this->opportunity->save();

        $this->opportunityClosed = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunityClosed->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $this->opportunityClosed->currency_id = $this->currency->id;
        $this->opportunityClosed->save();

        $this->quota = SugarTestQuotaUtilities::createQuota(500);
        $this->quota->currency_id = $this->currency->id;
        $this->quota->save();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        $this->forecast = SugarTestForecastUtilities::createForecast($timeperiod, $current_user);
        // currency is always base, set by forecast save()
        $this->forecast->save();

        /*$this->forecastSchedule = SugarTestForecastScheduleUtilities::createForecastSchedule($timeperiod, $current_user);
        $this->forecastSchedule->currency_id = $this->currency->id;
        $this->forecastSchedule->save();*/

    }

    public function tearDown()
    {
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        //SugarTestForecastScheduleUtilities::removeAllCreatedForecastSchedules();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @group forecasts
     */
    public function testCurrencyRateSchedulerJob()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $oppBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppUsDollarPreJob = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppBaseRateClosedPreJob = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $oppUsDollarClosedPreJob = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $quotaBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM quotas WHERE id = '%s'", $this->quota->id));
        $forecastBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM forecasts WHERE id = '%s'", $this->forecast->id));
        //$forecastScheduleBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM forecast_schedule WHERE id = '%s'", $this->forecastSchedule->id));

        // change the conversion rate
        $this->currency->conversion_rate = '2.345';
        $this->currency->save();

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'TestJobQueue',
            'class::SugarJobUpdateCurrencyRates',
            $this->currency->id,
            $current_user);

        //$this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $oppBaseRate = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppUsDollar = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppBaseRateClosed = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $oppUsDollarClosed = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $quotaBaseRate = $db->getOne(sprintf("SELECT base_rate FROM quotas WHERE id = '%s'", $this->quota->id));
        $forecastBaseRate = $db->getOne(sprintf("SELECT base_rate FROM forecasts WHERE id = '%s'", $this->forecast->id));
        //$forecastScheduleBaseRate = $db->getOne(sprintf("SELECT base_rate FROM forecast_schedule WHERE id = '%s'", $this->forecastSchedule->id));

        $this->assertNotEquals($oppBaseRatePreJob, $oppBaseRate, 'opportunities.base_rate was modified by CurrencyRateSchedulerJob');
        $this->assertNotEquals($oppUsDollarPreJob, $oppUsDollar, 'opportunities.amount_usdollar was modified by CurrencyRateSchedulerJob',2);
        $this->assertEquals($oppBaseRateClosedPreJob, $oppBaseRateClosed, 'opportunities.base_rate was not modified by CurrencyRateSchedulerJob');
        $this->assertEquals($oppUsDollarClosedPreJob, $oppUsDollarClosed, 'opportunities.amount_usdollar was not modified by CurrencyRateSchedulerJob for closed opportunity',2);
        $this->assertNotEquals($quotaBaseRatePreJob, $quotaBaseRate, 'quotas.base_rate was modified by CurrencyRateSchedulerJob');
        $this->assertEquals($forecastBaseRatePreJob, $forecastBaseRate, 'forecasts.base_rate was not modified by CurrencyRateSchedulerJob');
        //$this->assertNotEquals($forecastScheduleBaseRatePreJob, $forecastScheduleBaseRate, 'forecast_schedule.base_rate modified by CurrencyRateSchedulerJob');
    }

    /**
     * @group forecasts
     */
    public function testCurrencyRateDefinitions()
    {
        $test = new UpdateRateTest();
        $test->addRateColumnDefinition('foo','bar');
        $test->addRateColumnDefinition('foo','baz');
        $test->addRateColumnDefinition('foo','biz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(array('bar','baz','biz'),$rates);
        $test->removeRateColumnDefinition('foo','baz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(array('bar','biz'),$rates);

        $test->addUsDollarColumnDefinition('foo','bar','bar_usdollar');
        $test->addUsDollarColumnDefinition('foo','baz','baz_usdollar');
        $test->addUsDollarColumnDefinition('foo','biz','biz_usdollar');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals(array(
            'bar'=>'bar_usdollar',
            'baz'=>'baz_usdollar',
            'biz'=>'biz_usdollar'
        ),$rates);
        $test->removeUsDollarColumnDefinition('foo','baz');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals(array(
            'bar'=>'bar_usdollar',
            'biz'=>'biz_usdollar'
        ),$rates);
    }

}

class UpdateRateTest extends CurrencyRateUpdateAbstract {

}
