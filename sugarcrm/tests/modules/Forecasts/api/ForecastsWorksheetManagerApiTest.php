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
    /**
     * @var User
     */
    private static $reportee;

    /**
     * @var User
     */
    protected static $manager;
    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var array
     */
    protected static $repData;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language('en_us');

        self::$manager = SugarTestUserUtilities::createAnonymousUser();
        
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager->id;
        self::$reportee->save();
        
        self::$timeperiod = new TimePeriod();
        self::$timeperiod->start_date = "2012-01-01";
        self::$timeperiod->end_date = "2012-03-31";
        self::$timeperiod->name = "Test";
        self::$timeperiod->save();
        
        $timeperiod_id = self::$timeperiod->id;
                
        //setup opps
        $managerOpp = SugarTestOpportunityUtilities::createOpportunity();
        $managerOpp->assigned_user_id = self::$manager->id;
        $managerOpp->timeperiod_id = $timeperiod_id;
        $managerOpp->amount = 1800;
        $managerOpp->probability = 80;
        $managerOpp->forecast = 1;
        $managerOpp->team_set_id = 1;
        $managerOpp->team_id = 1;
        $managerOpp->save();

        $repOpp = SugarTestOpportunityUtilities::createOpportunity();
        $repOpp->assigned_user_id = self::$reportee->id;
        $repOpp->timeperiod_id = $timeperiod_id;
        $repOpp->amount = 1300;
        $repOpp->probability = 80;
        $repOpp->forecast = 1;
        $repOpp->team_set_id = 1;
        $repOpp->team_id = 1;
        $repOpp->save();

        //setup quotas
        $managerQuota = SugarTestQuotaUtilities::createQuota(2000);
        $managerQuota->user_id = self::$manager->id;
        $managerQuota->quota_type = "Direct";
        $managerQuota->timeperiod_id = $timeperiod_id;
        $managerQuota->save();

        $repQuota = SugarTestQuotaUtilities::createQuota(1500);
        $repQuota->user_id = self::$reportee->id;
        $repQuota->quota_type = "Rollup";
        $repQuota->timeperiod_id = $timeperiod_id;
        $repQuota->save();

        //setup forecasts
        $managerForecast = new Forecast();
        $managerForecast->user_id = self::$manager->id;
        $managerForecast->best_case = 1500;
        $managerForecast->likely_case = 1200;
        $managerForecast->worst_case = 900;
        $managerForecast->timeperiod_id = $timeperiod_id;
        $managerForecast->forecast_type = "Direct";
        $managerForecast->save();

        $repForecast = new Forecast();
        $repForecast->user_id = self::$reportee->id;
        $repForecast->best_case = 1100;
        $repForecast->likely_case = 900;
        $repForecast->worst_case = 700;
        $repForecast->timeperiod_id = $timeperiod_id;
        $repForecast->forecast_type = "Direct";
        $repForecast->save();

        //setup worksheets
        $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $managerWorksheet->user_id = self::$manager->id;
        $managerWorksheet->related_id = self::$manager->id;
        $managerWorksheet->forecast_type = "Direct";
        $managerWorksheet->timeperiod_id = $timeperiod_id;
        $managerWorksheet->best_case = 1550;
        $managerWorksheet->likely_case = 1250;
        $managerWorksheet->worst_case = 950;
        $managerWorksheet->forecast = 1;
        $managerWorksheet->save();

        $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $repWorksheet->user_id = self::$manager->id;
        $repWorksheet->related_id = self::$reportee->id;
        $repWorksheet->forecast_type = "Rollup";
        $repWorksheet->timeperiod_id = $timeperiod_id;
        $repWorksheet->best_case = 1150;
        $repWorksheet->likely_case = 950;
        $repWorksheet->worst_case = 750;
        $repWorksheet->forecast = 1;
        $repWorksheet->save();

        self::$managerData = array("amount" => $managerOpp->amount,
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
                             "show_opps" => true,
                             "id" => $managerForecast->id,
                             "name" => 'Opportunities (' . self::$manager->first_name . ' ' . self::$manager->last_name . ')',
                             "user_id" => self::$manager->id,

                        );

        self::$repData = array("amount" => $repOpp->amount,
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
                         "id" => $repForecast->id,
                         "name" => self::$reportee->first_name . ' ' . self::$reportee->last_name,
                         "user_id" => self::$reportee->id,
                   );
        
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        $userIds = SugarTestUserUtilities::getCreatedUserIds();
        $GLOBALS['db'] -> query('DELETE FROM forecasts WHERE user_id IN (\'' . implode("', '", $userIds) . '\')');
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDown();
    }

    public function tearDown()
    {}

    public function testPassedInUserIsManager()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager->id . '&timeperiod_id=' . self::$timeperiod->id);

        $data = array(self::$managerData, self::$repData);
        $this->assertEquals($data, $restReply['reply']);
    }

    public function testPassedInUserIsNotManagerReturnsEmpty()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$reportee->id . '&timeperiod_id=' . self::$timeperiod->id);
        $this->assertEmpty($restReply['reply'], "rest reply is not empty");
    }

    public function testCurrentUserIsNotManagerReturnsEmpty()
    {
        // save the current user
        global $current_user;
        $_old_current_user = $current_user;

        // set the current user to the reportee
        $current_user = self::$reportee;

        // run the test
        $restReply = $this->_restCall("ForecastManagerWorksheets?timeperiod_id=" . self::$timeperiod->id);
        $this->assertEmpty($restReply['reply'], "rest reply is not empty");

        // reset current user;
        $current_user = $_old_current_user;
    }

    /**
     * @bug 54619
     */
    public function testAdjustedNumbersShouldBeSameAsNonAdjustedColumns()
    {
        $rep_worksheet = BeanFactory::getBean('Worksheet', self::$repData['worksheet_id']);
        $rep_worksheet->deleted = 1;
        $rep_worksheet->save();
        
        $localRepData = self::$repData;

        $localRepData['best_adjusted'] = $localRepData['best_case'];
        $localRepData['likely_adjusted'] = $localRepData['likely_case'];
        $localRepData['worst_adjusted'] = $localRepData['worst_case'];
        $localRepData['forecast'] = 0;
        $localRepData['worksheet_id'] = '';

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager->id . '&timeperiod_id=' . self::$timeperiod->id);

        $this->assertEquals($localRepData, $restReply['reply'][1], "Best/Likely (Adjusted) numbers by default should be the same as best/likely numbers" );
        
        $rep_worksheet->deleted = 0;
        $rep_worksheet->save();
    }

    /**
     * @bug 54655
     */
    public function testBlankLineInWorksheetAfterDeletingASalesRep()
    {
        // temp reportee
        $tmp = SugarTestUserUtilities::createAnonymousUser();
        $tmp->reports_to_id = self::$manager->id;
        $tmp->deleted = 1;
        $tmp->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager->id . '&timeperiod_id=' . self::$timeperiod->id);

        // we should only have one row returned
        $this->assertEquals(2, count($restReply['reply']), "deleted user's data should not be listed in worksheet table" );
    }

    /**
     * @bug 55172
     */
    public function testAmountIsZeroWhenReporteeHasNoCommittedForecast()
    {
        $rep_forecast = BeanFactory::getBean('Forecasts', self::$repData['forecast_id']);
        $rep_forecast->deleted = 1;
        $rep_forecast->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager->id . '&timeperiod_id=' . self::$timeperiod->id);

        $this->assertSame(0, $restReply['reply'][1]['amount']);

        $rep_forecast->deleted = 0;
        $rep_forecast->save();
    }
}

