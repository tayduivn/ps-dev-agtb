<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecasts
 */
class ForecastsWorksheetManagerApiTest extends RestTestBase
{
    private $reportee;

    public function setUp()
    {
        parent::setUp();

        $this->reportee = SugarTestUserUtilities::createAnonymousUser();
        $this->reportee->reports_to_id = $GLOBALS['current_user']->id;
        $this->reportee->save();
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        $userIds = SugarTestUserUtilities::getCreatedUserIds();
        $GLOBALS['db'] -> query('DELETE FROM forecasts WHERE user_id IN (\'' . implode("', '", $userIds) . '\')');
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /***
     * @group forecastapi
     */
    public function testForecastsWorksheetManagerApi()
    {
        global $current_user;

        //get current timeperiod
        $timeperiod_id = TimePeriod::getCurrentId();

        //setup opps
        $managerOpp = SugarTestOpportunityUtilities::createOpportunity();
        $managerOpp->assigned_user_id = $current_user->id;
        $managerOpp->timeperiod_id = $timeperiod_id;
        $managerOpp->amount = 1800;
        $managerOpp->save();

        $repOpp = SugarTestOpportunityUtilities::createOpportunity();
        $repOpp->assigned_user_id = $this->reportee->id;
        $repOpp->timeperiod_id = $timeperiod_id;
        $repOpp->amount = 1300;
        $repOpp->save();

        //setup quotas
        $managerQuota = SugarTestQuotaUtilities::createQuota(2000);
        $managerQuota->user_id = $current_user->id;
        $managerQuota->quota_type = "Direct";
        $managerQuota->timeperiod_id = $timeperiod_id;
        $managerQuota->save();

        $repQuota = SugarTestQuotaUtilities::createQuota(1500);
        $repQuota->user_id = $this->reportee->id;
        $repQuota->quota_type = "Direct";
        $repQuota->timeperiod_id = $timeperiod_id;
        $repQuota->save();

        //setup forecasts
        $managerForecast = new Forecast();
        $managerForecast->user_id = $current_user->id;
        $managerForecast->best_case = 1500;
        $managerForecast->likely_case = 1200;
        $managerForecast->worst_case = 900;
        $managerForecast->timeperiod_id = $timeperiod_id;
        $managerForecast->forecast_type = "Direct";
        $managerForecast->save();

        $repForecast = new Forecast();
        $repForecast->user_id = $this->reportee->id;
        $repForecast->best_case = 1100;
        $repForecast->likely_case = 900;
        $repForecast->worst_case = 700;
        $repForecast->timeperiod_id = $timeperiod_id;
        $repForecast->forecast_type = "Direct";
        $repForecast->save();

        //setup worksheets
        $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $managerWorksheet->user_id = $current_user->id;
        $managerWorksheet->related_id = $current_user->id;
        $managerWorksheet->forecast_type = "Direct";
        $managerWorksheet->timeperiod_id = $timeperiod_id;
        $managerWorksheet->best_case = 1550;
        $managerWorksheet->likely_case = 1250;
        $managerWorksheet->worst_case = 950;
        $managerWorksheet->forecast = 1;
        $managerWorksheet->save();

        $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $repWorksheet->user_id = $current_user->id;
        $repWorksheet->related_id = $this->reportee->id;
        $repWorksheet->forecast_type = "Rollup";
        $repWorksheet->timeperiod_id = $timeperiod_id;
        $repWorksheet->best_case = 1150;
        $repWorksheet->likely_case = 950;
        $repWorksheet->worst_case = 750;
        $repWorksheet->forecast = 1;
        $repWorksheet->save();

        $managerData = array("amount" => $managerOpp->amount,
                             "quota" => $managerQuota->amount,
                             "quota_id" => $managerQuota->id,
                             "best_case" => $managerForecast->best_case,
                             "likely_case" => $managerForecast->likely_case,
                             "worst_case" => $managerForecast->worst_case,
                             "best_adjusted" => $managerWorksheet->best_case,
                             "likely_adjusted" => $managerWorksheet->likely_case,
                             "worst_adjusted" => $managerWorksheet->worst_case,
                             "forecast" => intval($managerWorksheet->forecast),
                             "forecast_id" => $managerForecast->id,
                             "worksheet_id" => $managerWorksheet->id,
                             "show_opps" => false,
                             "name" => $current_user->first_name . ' ' . $current_user->last_name,
                             "user_id" => $current_user->id,
                        );

        $repData = array("amount" => $repOpp->amount,
                         "quota" => $repQuota->amount,
                         "quota_id" => $repQuota->id,
                         "best_case" => $repForecast->best_case,
                         "likely_case" => $repForecast->likely_case,
                         "worst_case" => $repForecast->worst_case,
                         "best_adjusted" => $repWorksheet->best_case,
                         "likely_adjusted" => $repWorksheet->likely_case,
                         "worst_adjusted" => $repWorksheet->worst_case,
                         "forecast" => intval($repWorksheet->forecast),
                         "forecast_id" => $repForecast->id,
                         "worksheet_id" => $repWorksheet->id,
                         "show_opps" => true,
                         "name" => 'Opportunities (' . $this->reportee->first_name . ' ' . $this->reportee->last_name . ')',
                         "user_id" => $this->reportee->id,
                   );

        //case #1: current user is manager
        $restReply = $this->_restCall("ForecastManagerWorksheets/");

        //$this->assertEquals($managerData, $restReply['reply'][0], "there's no manager data in the rest reply" );
        //$this->assertEquals($repData, $restReply['reply'][1], "there's no sales rep data in the rest reply" );

        //case #2: user in filter is not manager - rest reply should be empty
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . $this->reportee->id);

        $this->assertEmpty($restReply['reply'], "rest reply is not empty");

        //case #3: current user is not manager - rest reply should be empty
        $this->reportee->reports_to_id = '';
        $this->reportee->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets/");

        $this->assertEmpty($restReply['reply'], "rest reply is not empty");
    }

}