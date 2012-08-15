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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecastapi
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

    public function setUp()
    {
        //create manager and a sales rep to report to the manager
        self::$manager = SugarTestUserUtilities::createAnonymousUser();
        self::$manager->save();

        self::$user = SugarTestUserUtilities::createAnonymousUser();
        self::$user->reports_to_id = self::$manager->id;
        self::$user->save();

        self::$timeperiod = new TimePeriod();
        self::$timeperiod->start_date = "2012-01-01";
        self::$timeperiod->end_date = "2012-03-31";
        self::$timeperiod->name = "Test";
        self::$timeperiod->save();
        $GLOBALS['current_user'] = self::$user;
        //give the rep a Quota
        self::$quota = SugarTestQuotaUtilities::createQuota(50000);
        self::$quota->user_id = self::$user->id;
        self::$quota->timeperiod_id = self::$timeperiod->id;
        self::$quota->save();

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
        $opp->timeperiod_id = self::$timeperiod->id;
        $opp->save();
        $opportunities[] = $opp;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '80';
        $opp->best_case = '10000';
        $opp->likely_case = '9000';
        $opp->worst_case = '7000';
        $opp->sales_stage = 'Negotiation/Review';
        $opp->timeperiod_id = self::$timeperiod->id;
        $opp->save();
        $opportunities[] = $opp;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = self::$user->id;
        $opp->probability = '80';
        $opp->amount = '30000';
        $opp->best_case = '30000';
        $opp->likely_case = '27000';
        $opp->worst_case = '23000';
        $opp->sales_stage = 'Closed Won';
        $opp->timeperiod_id = self::$timeperiod->id;
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

        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        parent::tearDown();
    }

    /**
     * @group forecastapi
     */
    public function testProgress() {
        $url = 'Forecasts/progress?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id;

        $restResponse = $this->_restCall($url);

        $restReply = $restResponse['reply'];

        //check quotas section
        $this->assertEquals(self::$quota->amount, $restReply['quota']['amount'], "Quota amount was not correct.  Expected: ");


        $likely_to_close_total = 24000;
        $likely_to_close_percent = 1.8;
        $best_to_close_total = 30000;
        $best_to_close_percent = 2.0;
        $likely_to_quota_total = 4000;
        $likely_to_quota_percent = 1.08;
        $best_to_quota_total = 10000;
        $best_to_quota_percent = 1.20;

        $revenue = 30000;
        $pipeline = 1.3;

        //check best/likely numbers
        // to quota
        $this->assertEquals($likely_to_quota_total, $restReply['quota']['likely_case']['amount'], "Likely to quota amount didn't match calculated amount.");
        $this->assertEquals($likely_to_quota_percent, $restReply['quota']['likely_case']['percent'], "Likely to quota percent didn't match calculated amount.");

        $this->assertEquals($best_to_quota_total, $restReply['quota']['best_case']['amount'], "Best to quota amount didn't match calculated amount.");
        $this->assertEquals($best_to_quota_percent, $restReply['quota']['best_case']['percent'], "Best to quota percent didn't match calculated amount");

        // to close
        $this->assertEquals($likely_to_close_total, $restReply['closed']['likely_case']['amount'], "Likely to close amount didn't match calculated amount.");
        $this->assertEquals($likely_to_close_percent, $restReply['closed']['likely_case']['percent'], "Likely to close percent didn't match calculated amount.");

        $this->assertEquals($best_to_close_total, $restReply['closed']['best_case']['amount'], "Best to close amount didn't match calculated amount.");
        $this->assertEquals($best_to_close_percent, $restReply['closed']['best_case']['percent'], "Best to close percent didn't match calculated amount.");

        $this->assertEquals($revenue, $restReply['revenue'], "Revenue didn't match calculated amount. expected: ".$revenue." received: ".$restReply['revenue']);
        $this->assertEquals($pipeline, $restReply['pipeline'], "Revenue didn't match calculated amount. expected: ".$pipeline." received: ".$restReply['pipeline']);
    }

    /**
     * @group forecastapi
     */
    public function testProgressNewUser() {
        $newUser = SugarTestUserUtilities::createAnonymousUser();
        $newUser->reports_to_id = self::$manager->id;
        $newUser->save();
        $url = 'Forecasts/progress?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . $newUser->id;

        $restResponse = $this->_restCall($url);

        $restReply = $restResponse['reply'];

        //check best/likely numbers
        // to quota
        $this->assertEquals(0, $restReply['quota']['amount'], "Quota amount was not correct.  Expected: ");
        $this->assertEquals(0, $restReply['quota']['likely_case']['amount'], "Likely to quota amount didn't match calculated amount.");
        $this->assertEquals(0, $restReply['quota']['likely_case']['percent'], "Likely to quota percent didn't match calculated amount.");

        $this->assertEquals(0, $restReply['quota']['best_case']['amount'], "Best to quota amount didn't match calculated amount.");
        $this->assertEquals(0, $restReply['quota']['best_case']['percent'], "Best to quota percent didn't match calculated amount");

        // to close
        $this->assertEquals(0, $restReply['closed']['likely_case']['amount'], "Likely to close amount didn't match calculated amount.");
        $this->assertEquals(0, $restReply['closed']['likely_case']['percent'], "Likely to close percent didn't match calculated amount.");

        $this->assertEquals(0, $restReply['closed']['best_case']['amount'], "Best to close amount didn't match calculated amount.");
        $this->assertEquals(0, $restReply['closed']['best_case']['percent'], "Best to close percent didn't match calculated amount.");

        $this->assertEquals(0, $restReply['revenue'], "Revenue didn't match calculated amount. expected: 0 received: ".$restReply['revenue']);
        $this->assertEquals(0, $restReply['pipeline'], "Revenue didn't match calculated amount. expected: 0 received: ".$restReply['pipeline']);
    }
}