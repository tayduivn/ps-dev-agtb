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
class ForecastsChartManagerApiTest extends RestTestBase
{

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var Forecast
     */
    protected static $managerWorksheet;

    /**
     * @var Forecast
     */
    protected static $repWorksheet;

    /**
     * @var TimePeriod;
     */
    protected static $timeperiod;

    /**
     * Set-up the variables needed for this to run.
     *
     * @static
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$user = SugarTestUserUtilities::createAnonymousUser();

        $rep = SugarTestUserUtilities::createAnonymousUser();
        $rep->reports_to_id = self::$user->id;
        $rep->save();

        self::$timeperiod = new TimePeriod();
        self::$timeperiod->start_date = "2012-01-01";
        self::$timeperiod->end_date = "2012-03-31";
        self::$timeperiod->name = "Test";
        self::$timeperiod->save();

        $managerOpp = SugarTestOpportunityUtilities::createOpportunity();
        $managerOpp->assigned_user_id = self::$user->id;
        $managerOpp->timeperiod_id = self::$timeperiod->id;
        $managerOpp->amount = 1800;
        $managerOpp->likely_case = 1700;
        $managerOpp->best_case = 1900;
        $managerOpp->probability = '85';
        $managerOpp->date_closed = '2012-01-30';
        $managerOpp->team_id = '1';
        $managerOpp->team_set_id = '1';
        $managerOpp->save();

        $repOpp = SugarTestOpportunityUtilities::createOpportunity();
        $repOpp->assigned_user_id = $rep->id;
        $repOpp->timeperiod_id = self::$timeperiod->id;
        $repOpp->amount = 1300;
        $repOpp->likely_case = 1200;
        $repOpp->best_case = 1400;
        $repOpp->probability = '85';
        $repOpp->date_closed = '2012-01-30';
        $repOpp->team_id = '1';
        $repOpp->team_set_id = '1';
        $repOpp->save();

        //setup quotas
        $managerQuota = SugarTestQuotaUtilities::createQuota(2000);
        $managerQuota->user_id = self::$user->id;
        $managerQuota->quota_type = "Direct";
        $managerQuota->timeperiod_id = self::$timeperiod->id;
        $managerQuota->team_set_id = 1;
        $managerQuota->save();

        $repQuota = SugarTestQuotaUtilities::createQuota(1500);
        $repQuota->user_id = $rep->id;
        $repQuota->quota_type = "Direct";
        $repQuota->timeperiod_id = self::$timeperiod->id;
        $repQuota->team_set_id = 1;
        $repQuota->save();

        //setup forecasts
        $managerForecast = new Forecast();
        $managerForecast->user_id = self::$user->id;
        $managerForecast->best_case = 1500;
        $managerForecast->likely_case = 1200;
        $managerForecast->worst_case = 900;
        $managerForecast->timeperiod_id = self::$timeperiod->id;
        $managerForecast->forecast_type = "Direct";
        $managerForecast->team_set_id = 1;
        $managerForecast->save();

        $repForecast = new Forecast();
        $repForecast->user_id = $rep->id;
        $repForecast->best_case = 1100;
        $repForecast->likely_case = 900;
        $repForecast->worst_case = 700;
        $repForecast->timeperiod_id = self::$timeperiod->id;
        $repForecast->forecast_type = "Direct";
        $repForecast->team_set_id = 1;
        $repForecast->save();

        //setup worksheets
        self::$managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        self::$managerWorksheet->user_id = self::$user->id;
        self::$managerWorksheet->related_id = self::$user->id;
        self::$managerWorksheet->forecast_type = "Direct";
        self::$managerWorksheet->timeperiod_id = self::$timeperiod->id;
        self::$managerWorksheet->best_case = 1550;
        self::$managerWorksheet->likely_case = 1250;
        self::$managerWorksheet->worst_case = 950;
        self::$managerWorksheet->team_set_id = 1;
        self::$managerWorksheet->save();

        self::$repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        self::$repWorksheet->user_id = self::$user->id;
        self::$repWorksheet->related_id = $rep->id;
        self::$repWorksheet->forecast_type = "Rollup";
        self::$repWorksheet->timeperiod_id = self::$timeperiod->id;
        self::$repWorksheet->best_case = 1150;
        self::$repWorksheet->likely_case = 950;
        self::$repWorksheet->worst_case = 750;
        self::$repWorksheet->team_set_id = 1;
        self::$repWorksheet->save();

        parent::setUpBeforeClass();
    }

    /**
     * Clean up the class
     *
     * @static
     */
    public static function tearDownAfterClass()
    {
        $userIds = SugarTestUserUtilities::getCreatedUserIds();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        $GLOBALS['db']->query('DELETE FROM timeperiods WHERE id ="' . self::$timeperiod->id . '";');
        $GLOBALS['db']->query('DELETE FROM forecasts WHERE user_id IN (\'' . implode("', '", $userIds) . '\')');

        parent::tearDownAfterClass();
        // this strange as we only want to call this when the class expires;
        parent::tearDown();
    }

    /**
     * Ignore the teardown so we don't remove users that might be needed.
     */
    public function tearDown()
    {
    }

    /**
     * Utility Method to run the same command for each test.
     *
     * @param string $dataset           What data set we want to test for
     * @return mixed
     */
    protected function runRestCommand($dataset = 'likely')
    {
        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&group_by=sales_stage&dataset=' . $dataset . '&display_manager=true';
        $restReply = $this->_restCall($url);

        return $restReply['reply'];
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testChartDataShouldContainTwoUsers()
    {
        $data = $this->runRestCommand();
        $this->assertEquals(2, count($data['values']));
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testPropertyValueNameContainsAdjusted()
    {
        $data = $this->runRestCommand();
        $this->assertContains('(Adjusted)', $data['properties'][0]['value_name']);
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testGoalParetoLabelContainsAdjusted()
    {
        $data = $this->runRestCommand();
        $this->assertContains('(Adjusted)', $data['properties'][0]['goal_marker_label'][1]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testManagerValueIsLikelyAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand();
        $this->assertEquals(self::$managerWorksheet->likely_case, $data['values'][0]['values'][0]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testReporteeValueIsLikelyAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand();
        $this->assertEquals(self::$repWorksheet->likely_case, $data['values'][1]['values'][0]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testManagerValueIsBestAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand('best');
        $this->assertEquals(self::$managerWorksheet->best_case, $data['values'][0]['values'][0]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testReporteeValueIsBestAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand('best');
        $this->assertEquals(self::$repWorksheet->best_case, $data['values'][1]['values'][0]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testManagerValueIsWorstAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand('worst');
        $this->assertEquals(self::$managerWorksheet->worst_case, $data['values'][0]['values'][0]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testReporteeValueIsWorstAdjustedValueFromWorksheet()
    {
        $data = $this->runRestCommand('worst');
        $this->assertEquals(self::$repWorksheet->worst_case, $data['values'][1]['values'][0]);
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testThirdReporteeValueZeroWithoutForecastRecord()
    {

        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language('en_us');
        $rep = SugarTestUserUtilities::createAnonymousUser();
        $rep->reports_to_id = self::$user->id;
        $rep->save();

        $repOpp = SugarTestOpportunityUtilities::createOpportunity();
        $repOpp->assigned_user_id = self::$user->id;
        $repOpp->timeperiod_id = self::$timeperiod->id;
        $repOpp->amount = 1800;
        $repOpp->likely_case = 1700;
        $repOpp->best_case = 1900;
        $repOpp->probability = '85';
        $repOpp->date_closed = '2012-01-30';
        $repOpp->team_id = '1';
        $repOpp->team_set_id = '1';
        $repOpp->save();

        //setup quotas
        $repQuota = SugarTestQuotaUtilities::createQuota(2000);
        $repQuota->user_id = self::$user->id;
        $repQuota->quota_type = "Direct";
        $repQuota->timeperiod_id = self::$timeperiod->id;
        $repQuota->team_set_id = 1;
        $repQuota->save();

        $data = $this->runRestCommand();
        $this->assertEquals(0, $data['values'][2]['values'][0]);
    }
}