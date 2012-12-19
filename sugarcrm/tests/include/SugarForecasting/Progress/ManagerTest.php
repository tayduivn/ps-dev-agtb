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
require_once('include/SugarForecasting/Progress/Manager.php');
class SugarForecasting_Progress_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array args to be passed onto methods
     */
    protected static $args = array();

    /**
     * @var array array of users used throughout class
     */
    protected static $users = array();

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('manager', 'Forecasts'));
        SugarTestHelper::setup('current_user');
        
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$users['top_manager'] = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => array('amount' => 30000)
        ));

        self::$users['manager'] = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => array('amount' => 50000),
            'user' =>
            array('manager', 'reports_to' => self::$users['top_manager']['user']->id)
        ));

        global $current_user;

        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['manager']['user']->id;
        $current_user->setPreference('currency', self::$currency->id);

        $current_user = self::$users['manager']['user'];

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'user' =>
            array('manager', 'reports_to' => self::$users['manager']['user']->id),
            'quota' => array('amount' => 27000)
        );
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);

    }

    /**
     * reset after each test back to manager id, some tests may have changed to use top manager
     */
    public function setup() {
        self::$args['user_id'] = self::$users['manager']['user']->id;

        global $current_user;
        $current_user = self::$users['manager']['user'];
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        parent::tearDown();
    }

    /**
     * destroy some parts after each test
     */
    public function tearDown() {
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

    /**
     * check process method to make sure what is returned to the endpoint is correct
     *
     * @group forecasts
     * @group forecastsprogress
     */
    public function testProcess()
    {
        $obj = new SugarForecasting_Progress_Manager(self::$args);
        $data = $obj->process();

        //find expected quota object for the created quotas
        foreach(SugarTestQuotaUtilities::getCreatedQuotaIds() as $quotaID) {
            $quota = BeanFactory::getBean('Quotas', $quotaID);
            if($quota->timeperiod_id == self::$args['timeperiod_id'] && $quota->user_id == self::$args['user_id'] && $quota->quota_type == "Rollup"){
                break;
            }
        }

        $expectedPipelineCount = 0;
        $expectedClosedAmount = 0;
        $expectedPipelineRevenue = 0;
        $opp_ids = SugarTestOpportunityUtilities::getCreatedOpportunityIds();
        $timeperiod = BeanFactory::getBean('TimePeriods', self::$args['timeperiod_id']);
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        $excluded_sales_stages_won = $settings['sales_stage_won'];
        $excluded_sales_stages_lost = $settings['sales_stage_lost'];
        $managerId = self::$args['user_id'];
        $repId = self::$users['reportee']['user']->id;
        foreach($opp_ids as $id)
        {
            $opp = BeanFactory::getBean('Opportunities', $id);
            //check user is manager or rep
            if($opp->assigned_user_id != $repId && $opp->assigned_user_id != $managerId) {
                continue;
            }
            //bypass if opp is deleted
            if($opp->deleted == 1) {
                continue;
            }
            //check opp is out of timeperiod bounds
            if($timeperiod->start_date_timestamp >= $opp->date_closed_timestamp && $timeperiod->end_date_timestamp <= $opp->date_closed_timestamp) {
                continue;
            }
            //check exclusion patterns
            $exclude = false;
            //check that opp is within admin bounds
            foreach ($excluded_sales_stages_won as $exclusion)
            {
                if($opp->sales_stage == $exclusion)
                {
                    $expectedClosedAmount += ($opp->amount * $opp->base_rate);
                    $exclude = true;
                    break;
                }
            }
            foreach ($excluded_sales_stages_lost as $exclusion)
            {
                if($opp->sales_stage == $exclusion)
                {
                    $exclude = true;
                    break;
                }
            }
            //sales stage for opp is an excluded stage
            if($exclude)
            {
                continue;
            }

            //all conditions passed, add it in
            $expectedPipelineCount += 1;
            $expectedPipelineRevenue += round($opp->amount * $opp->base_rate,2);
        }

        //test parts of the process return
        $this->assertEquals($quota->amount, round(floatval($data['quota_amount']),2), "Quota not matching expected amount.  Expected: ".$quota->amount." Actual: ".$data['quota_amount'],.00001);
        $this->assertEquals($expectedPipelineCount, $data['opportunities'], "Pipeline Count not matching expected amount.  Expected: ".$expectedPipelineCount." Actual: ".$data['opportunities']);
        $this->assertEquals($expectedClosedAmount, round(floatval($data['closed_amount']),2), "Closed amount not matching expected amount.  Expected: ".$expectedClosedAmount." Actual: ".$data['closed_amount'],.00001);
        $this->assertEquals($expectedPipelineRevenue, round(floatval($data['pipeline_revenue']),2), "Pipeline Revenue not matching expected amount.  Expected: ".$expectedPipelineRevenue." Actual: ".$data['pipeline_revenue'],.00001);
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        // keys are as follows
        // 1 -> quota amount
        // 2 -> quota currency id

        return array(
            array(false, false, 0, 0, -99),
            array(true, false, 15000, 0, -99),
            array(false, true, 0, 30000, -99),
            array(true, true, 15000, 30000, -99),
        );
    }

    /**
     * check top level manager quota to make sure it returns the expected sum of values for manager that doesn't report to anyone
     *
     * @dataProvider dataProviderDatasets
     * @group forecasts
     * @group forecastsprogress
     */
    public function testGetTopLevelManagerQuota($createManagerWorksheet, $createRepWorksheet, $managerQuotaAmount, $repQuotaAmount, $quotaCurrencyId)
    {
        global $current_user;
        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['top_manager']['user']->id;

        if($createManagerWorksheet) {
            //create worksheet data based on dataprovider
            $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $managerWorksheet->user_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->related_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $managerWorksheet->quota = $managerQuotaAmount;
            $managerWorksheet->currency_id = $quotaCurrencyId;
            $managerWorksheet->forecast_type = 'Rollup';
            $managerWorksheet->related_forecast_type = 'Direct';
            $managerWorksheet->version = 0;
            $managerWorksheet->save();
        }
        if($createRepWorksheet) {
            //create worksheet data based on dataprovider
            $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $repWorksheet->user_id = self::$users['top_manager']['user']->id;
            $repWorksheet->related_id = self::$users['manager']['user']->id;
            $repWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $repWorksheet->quota = $repQuotaAmount;
            $repWorksheet->currency_id = $quotaCurrencyId;
            $repWorksheet->forecast_type = 'Rollup';
            $repWorksheet->related_forecast_type = 'Rollup';
            $repWorksheet->version = 0;
            $repWorksheet->save();
        }
        $obj = new SugarForecasting_Progress_Manager(self::$args);
        $quotaAmount = $obj->getQuotaTotalFromData();

        $expectedQuotaAmount = 0;
        //determine how much quota should be
        foreach(SugarTestQuotaUtilities::getCreatedQuotaIds() as $quotaID) {
            $quota = BeanFactory::getBean('Quotas', $quotaID);
            if($quota->timeperiod_id == self::$args['timeperiod_id'])
            {
                if($quota->quota_type == "Direct" && $quota->user_id == self::$args['user_id'])
                {
                    //personal quota for the top level manager
                    //use worksheet quota number as it will cause an override in the function
                    $expectedQuotaAmount += ($createManagerWorksheet ? $managerQuotaAmount : $quota->amount);
                }
                if($quota->quota_type == "Rollup" && $quota->user_id == self::$users['manager']['user']->id)
                {
                    $expectedQuotaAmount += ($createRepWorksheet ? $repQuotaAmount : $quota->amount);
                }

            }
        }
        //compare expected and actual
        $this->assertEquals($expectedQuotaAmount, $quotaAmount, "Quota not matching expected amount.  Expected: ".$expectedQuotaAmount." Actual: ".$quotaAmount);

    }


}