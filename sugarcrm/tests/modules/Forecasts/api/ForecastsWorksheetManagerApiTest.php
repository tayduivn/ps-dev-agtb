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
     * @var array
     */
    private static $reportee;

    /**
     * @var array
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

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        self::$manager = SugarTestForecastUtilities::createForecastUser();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$manager['user']->id)));

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = array("amount" => self::$manager['opportunities_total'],
            "quota" => self::$manager['quota']->amount,
            "quota_id" => self::$manager['quota']->id,
            "best_case" => self::$manager['forecast']->best_case,
            "likely_case" => self::$manager['forecast']->likely_case,
            "worst_case" => self::$manager['forecast']->worst_case,
            "best_adjusted" => self::$manager['worksheet']->best_case,
            "likely_adjusted" => self::$manager['worksheet']->likely_case,
            "worst_adjusted" => self::$manager['worksheet']->worst_case,
            "forecast" => intval(self::$manager['worksheet']->forecast),
            "forecast_id" => self::$manager['forecast']->id,
            "worksheet_id" => self::$manager['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager['user']->id,
            "name" => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            "user_id" => self::$manager['user']->id,

        );

        self::$repData = array("amount" => self::$reportee['opportunities_total'],
            "quota" => self::$reportee['quota']->amount,
            "quota_id" => self::$reportee['quota']->id,
            "best_case" => self::$reportee['forecast']->best_case,
            "likely_case" => self::$reportee['forecast']->likely_case,
            "worst_case" => self::$reportee['forecast']->worst_case,
            "best_adjusted" => self::$reportee['worksheet']->best_case,
            "likely_adjusted" => self::$reportee['worksheet']->likely_case,
            "worst_adjusted" => self::$reportee['worksheet']->worst_case,
            "forecast" => intval(self::$reportee['worksheet']->forecast),
            "forecast_id" => self::$reportee['forecast']->id,
            "worksheet_id" => self::$reportee['worksheet']->id,
            "show_opps" => true,
            "id" => self::$reportee['user']->id,
            "name" => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            "user_id" => self::$reportee['user']->id,

        );

    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();

        parent::tearDown();
    }


    public function testPassedInUserIsManager()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);

        $data = array(self::$managerData, self::$repData);
        $this->assertEquals($data, $restReply['reply']);
    }

    public function testPassedInUserIsNotManagerReturnsEmpty()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$reportee['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
        $this->assertEmpty($restReply['reply'], "rest reply is not empty");
    }

    public function testCurrentUserIsNotManagerReturnsEmpty()
    {
        // save the current user
        global $current_user;
        $_old_current_user = $current_user;

        // set the current user to the reportee
        $current_user = self::$reportee['user'];

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

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);

        $this->assertEquals($localRepData, $restReply['reply'][1], "Best/Likely (Adjusted) numbers by default should be the same as best/likely numbers");

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
        $tmp->reports_to_id = self::$manager['user']->id;
        $tmp->deleted = 1;
        $tmp->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);

        // we should only have one row returned
        $this->assertEquals(2, count($restReply['reply']), "deleted user's data should not be listed in worksheet table");
    }

    /**
     * @bug 55172
     */
    public function testAmountIsZeroWhenReporteeHasNoCommittedForecast()
    {
        $rep_forecast = BeanFactory::getBean('Forecasts', self::$repData['forecast_id']);
        $rep_forecast->deleted = 1;
        $rep_forecast->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);

        $this->assertSame(0, $restReply['reply'][1]['amount']);

        $rep_forecast->deleted = 0;
        $rep_forecast->save();
    }

    /**
     * @bug 55181
     */
    public function testManagerAndReporteeWithNoDataReturnsAllZeros()
    {
        global $current_user;

        $tmp1 = SugarTestUserUtilities::createAnonymousUser();

        $_current_user = $current_user;

        $current_user = $tmp1;

        $tmp2 = SugarTestUserUtilities::createAnonymousUser();
        $tmp2->reports_to_id = $tmp1->id;
        $tmp2->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . $tmp1->id . '&timeperiod_id=' . self::$timeperiod->id);

        $expected = array(
            0 =>
            array(
                'amount' => 0,
                'quota' => 0,
                'quota_id' => '',
                'best_case' => 0,
                'likely_case' => 0,
                'worst_case' => 0,
                'best_adjusted' => 0,
                'likely_adjusted' => 0,
                'worst_adjusted' => 0,
                'forecast' => 0,
                'forecast_id' => '',
                'worksheet_id' => '',
                'show_opps' => true,
                'id' => $tmp1->id,
                'name' => 'Opportunities (' . $tmp1->first_name . ' ' . $tmp1->last_name . ')',
                'user_id' => $tmp1->id,
            ),
            1 =>
            array(
                'amount' => 0,
                'quota' => 0,
                'quota_id' => '',
                'best_case' => 0,
                'likely_case' => 0,
                'worst_case' => 0,
                'best_adjusted' => 0,
                'likely_adjusted' => 0,
                'worst_adjusted' => 0,
                'forecast' => 0,
                'forecast_id' => '',
                'worksheet_id' => '',
                'show_opps' => true,
                'id' => $tmp2->id,
                'name' => $tmp2->first_name . ' ' . $tmp2->last_name,
                'user_id' => $tmp2->id,
            ),
        );

        $this->assertEquals($expected, $restReply['reply']);

        $current_user = $_current_user;
    }

    public function testManagerReporteeManagerReturnesProperValues()
    {
        // create extra reps
        $rep1 = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$reportee['user']->id)));
        $rep2 = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$reportee['user']->id)));

        // create a rollup forecast for the new manager
        $tmpForecast = SugarTestForecastUtilities::createManagerRollupForecast(self::$reportee, $rep1, $rep2);

        // create a worksheet for the new managers user
        $tmpWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        $tmpWorksheet->related_id = self::$reportee['user']->id;
        $tmpWorksheet->user_id = self::$reportee['user']->reports_to_id;
        $tmpWorksheet->forecast_type = "Rollup";
        $tmpWorksheet->related_forecast_type = "Direct";
        $tmpWorksheet->timeperiod_id = self::$timeperiod->id;
        $tmpWorksheet->best_case = $tmpForecast->best_case+100;
        $tmpWorksheet->likely_case = $tmpForecast->likely_case+100;
        $tmpWorksheet->worst_case = $tmpForecast->worst_case-100;
        $tmpWorksheet->forecast = 1;
        $tmpWorksheet->save();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);

        $expected = array(
            "amount" => self::$reportee['opportunities_total'] + $rep1['opportunities_total'] + $rep2['opportunities_total'],
            "best_adjusted" => $tmpWorksheet->best_case,
            "best_case" => $tmpForecast->best_case,
            "forecast" => intval($tmpWorksheet->forecast),
            "forecast_id" => $tmpForecast->id,
            "id" => self::$reportee["user"]->id,
            "likely_adjusted" => $tmpWorksheet->likely_case,
            "likely_case" => $tmpForecast->likely_case,
            "name" => self::$reportee["user"]->first_name . " " . self::$reportee["user"]->last_name,
            "quota" => self::$reportee['quota']->amount,
            "quota_id" => self::$reportee['quota']->id,
            "show_opps" => false,
            "user_id" => self::$reportee["user"]->id,
            "worksheet_id" => $tmpWorksheet->id,
            "worst_adjusted" => $tmpWorksheet->worst_case,
            "worst_case" => $tmpForecast->worst_case,
            "timeperiod_id" => self::$timeperiod->id
        );

        $this->assertEquals($expected, $restReply['reply'][1]);

    }


    /**
     * This test is to see that the data returned for the name field is set correctly when locale name format changes
     *
     * @group testGetLocaleFormattedName
     */
    public function testGetLocaleFormattedName()
    {
        global $locale, $current_language;
        $defaultPreference = $this->_user->getPreference('default_locale_name_format');
        $this->_user->setPreference('default_locale_name_format', 'l, f', 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
        $current_module_strings = return_module_language($current_language, 'Forecasts');
        $expectedName = string_format($current_module_strings['LBL_MY_OPPORTUNITIES'],
                                      array($locale->getLocaleFormattedName(self::$manager['user']->first_name, self::$manager['user']->last_name))
                        );
        $this->assertEquals($expectedName, $restReply['reply'][0]['name']);
        $this->_user->setPreference('default_locale_name_format', $defaultPreference, 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
    }
}

