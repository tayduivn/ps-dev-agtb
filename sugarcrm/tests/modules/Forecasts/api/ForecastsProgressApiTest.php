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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsProgressApiTest extends RestTestBase
{

    protected static $user;

    /**
     * @var TimePeriod;
     */
    protected static $timeperiod;
    protected static $manager;
    protected static $quota;
    protected static $managerQuota;

    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setup('app_list_strings');
        $forecastConfig = array (
            'show_buckets' => 0,
            'committed_probability' => 70,
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost')
        );
        $admin = BeanFactory::getBean('Administration');
        foreach ($forecastConfig as $name => $value)
        {
            $admin->saveSetting('base', $name, json_encode($value));
        }
    }

    public function setUp()
    {
        parent::setUp();
        //create manager and a sales rep to report to the manager
        self::$manager = SugarTestUserUtilities::createAnonymousUser();
        self::$manager->is_admin = 1;
        self::$manager->save();

        self::$user = SugarTestUserUtilities::createAnonymousUser();
        self::$user->reports_to_id = self::$manager->id;
        self::$user->is_admin = 1;
        self::$user->save();

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        //give the rep a Quota
        self::$quota = SugarTestQuotaUtilities::createQuota(50000);
        self::$quota->user_id = self::$user->id;
        self::$quota->quota_type = "Direct";
        self::$quota->timeperiod_id = self::$timeperiod->id;
        self::$quota->save();
        //give the manager a Quota
        self::$managerQuota = SugarTestQuotaUtilities::createQuota(56000);
        self::$managerQuota->user_id = self::$manager->id;
        self::$managerQuota->quota_type = "Rollup";
        self::$managerQuota->timeperiod_id = self::$timeperiod->id;
        self::$managerQuota->save();


        $opportunities = array();

        //create opportunities to be used in tests
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '80';
        $opp->amount = '20000';
        $opp->best_case = '20000';
        $opp->likely_case = '18000';
        $opp->worst_case = '15000';
        $opp->sales_stage = 'Negotiation/Review';
        $opp->date_closed = self::$timeperiod->start_date;
        $opp->save();
        $opportunities[] = $opp;

        //create opportunities to be used in tests
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$manager->id;
        $opp->probability = '80';
        $opp->amount = '20000';
        $opp->best_case = '20000';
        $opp->likely_case = '18000';
        $opp->worst_case = '15000';
        $opp->sales_stage = 'Negotiation/Review';
        $opp->date_closed = self::$timeperiod->end_date;
        $opp->save();
        $opportunities[] = $opp;


        //create opportunities to be used in tests
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$manager->id;
        $opp->probability = '100';
        $opp->amount = '20000';
        $opp->best_case = '20000';
        $opp->worst_case = '15000';
        $opp->sales_stage = 'Closed Won';
        $opp->date_closed = self::$timeperiod->start_date;
        $opp->save();
        $opportunities[] = $opp;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '80';
        $opp->best_case = '10000';
        $opp->worst_case = '7000';
        $opp->sales_stage = 'Negotiation/Review';
        $opp->date_closed = self::$timeperiod->end_date;
        $opp->save();
        $opportunities[] = $opp;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '80';
        $opp->amount = '30000';
        $opp->best_case = '30000';
        $opp->worst_case = '23000';
        $opp->sales_stage = 'Closed Won';
        $opp->date_closed = self::$timeperiod->start_date;
        $opp->save();
        $opportunities[] = $opp;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '100';
        $opp->amount = '20000';
        $opp->best_case = '20000';
        $opp->worst_case = '15000';
        $opp->sales_stage = 'Closed Won';
        $opp->date_closed = self::$timeperiod->end_date;
        $opp->save();
        $opportunities[] = $opp;

        $forecast = new Forecast();
        $forecast->user_id = self::$user->id;
        $forecast->timeperiod_id = self::$timeperiod->id;
        $forecast->best_case = 60000;
        $forecast->likely_case = 54000;
        $forecast->forecast_type = 'Direct';
        $forecast->opp_count = 3;
        $forecast->opp_weigh_value = 60000 / 3;
        $forecast->save();

        //setup worksheets
        $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $managerWorksheet->user_id = self::$manager->id;
        $managerWorksheet->related_id = self::$manager->id;
        $managerWorksheet->related_forecast_type = "Direct";
        $managerWorksheet->forecast_type = "Direct";
        $managerWorksheet->timeperiod_id = self::$timeperiod->id;
        $managerWorksheet->best_case = 62000;
        $managerWorksheet->likely_case = 55000;
        $managerWorksheet->worst_case = 50000;
        $managerWorksheet->forecast = 1;
        $managerWorksheet->save();

        $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $repWorksheet->user_id = self::$manager->id;
        $repWorksheet->related_id = self::$user->id;
        $repWorksheet->related_forecast_type = "Direct";
        $managerWorksheet->forecast_type = "Direct";
        $repWorksheet->timeperiod_id = self::$timeperiod->id;;
        $repWorksheet->best_case = 82000;
        $repWorksheet->likely_case = 74000;
        $repWorksheet->worst_case = 65000;
        $repWorksheet->forecast = 1;
        $repWorksheet->save();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testProgress() {
        $url = 'Forecasts/progressRep?user_id=' . self::$user->id . '&timeperiod_id=' . self::$timeperiod->id;

        $restResponse = $this->_restCall($url);

        $restReply = $restResponse['reply'];

        //check quotas section
        $this->assertEquals(self::$quota->amount, $restReply['quota_amount'], "Quota amount was not correct.  Expected: ");
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testManagerProgress() {
        $url = 'Forecasts/progressManager?user_id=' . self::$manager->id . '&timeperiod_id=' . self::$timeperiod->id;
        $restResponse = $this->_restCall($url);
        $restReply = $restResponse['reply'];
        $this->assertEquals(70000, $restReply['closed_amount'], "Closed amount didn't match calculated amount.");
        $this->assertEquals(3, $restReply['opportunities'], "opportunity count did not match");
        $this->assertEquals(50000, $restReply['pipeline_revenue'], "pipeline revenue did not match expected amounts");
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testProgressNewUser() {
        $newUser = SugarTestUserUtilities::createAnonymousUser();
        $newUser->reports_to_id = self::$manager->id;
        $newUser->save();
        $url = 'Forecasts/progressRep?user_id=' . $newUser->id . '&timeperiod_id=' . self::$timeperiod->id;

        $restResponse = $this->_restCall($url);

        $restReply = $restResponse['reply'];

        //check best/likely numbers
        // to quota
        $this->assertEquals(0, $restReply['quota_amount'], "Quota amount was not correct.  Expected: ");
    }
}