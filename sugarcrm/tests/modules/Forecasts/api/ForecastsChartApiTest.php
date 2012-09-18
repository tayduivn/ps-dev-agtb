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
 * @group forecastapi
 * @group forecasts
 */
class ForecastsChartApiTest extends RestTestBase
{

    protected static $user;

    /**
     * @var TimePeriod;
     */
    protected static $timeperiod;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        self::$user = SugarTestUserUtilities::createAnonymousUser();

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod("2012-01-01", "2012-03-31");

        // create an opp
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();
        $opp1->assigned_user_id = self::$user->id;
        $opp1->probability = '85';
        $opp1->amount = 1200;
        $opp1->best_case = 1300;
        $opp1->worst_case = 1100;
        $opp1->team_id = '1';
        $opp1->team_set_id = '1';
        $opp1->timeperiod_id = self::$timeperiod->id;
        $opp1->date_closed = '2012-01-30';
        $opp1->save();

        $quota = SugarTestQuotaUtilities::createQuota(1500);
        $quota->user_id = self::$user->id;
        $quota->timeperiod_id = self::$timeperiod->id;
        $quota->created_by = 1;
        $quota->modified_user_id = 1;
        $quota->save();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        parent::tearDownAfterClass();
        // this strange as we only want to call this when the class expires;
        parent::tearDown();
    }

    public function tearDown()
    {
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testQuotaIsReturned()
    {
        // url
        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&group_by=sales_stage&dataset=likely';
        $return = $this->_restCall($url);

        $chart = $return['reply'];
        $this->assertEquals(1500, $chart['values'][0]['goalmarkervalue'][0]);
    }

    /**
     * @dataProvider providerDataSetValueReturned
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testDataSetValueReturned($actual, $dataset)
    {
        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&group_by=sales_stage&dataset=' . $dataset;
        $return = $this->_restCall($url);

        $chart = $return['reply'];
        $this->assertEquals($actual, $chart['values'][0]['goalmarkervalue'][1]);
    }

    /**
     * @return array
     */
    public function providerDataSetValueReturned()
    {
        return array(
            array(1300, 'best'),
            array(1200, 'likely'),
            array(1100, 'worst')
        );
    }
    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testGoalMarkerLabelSetCorrectly()
    {
        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&group_by=sales_stage&dataset=likely';
        $return = $this->_restCall($url);

        $chart = $return['reply'];
        $this->assertEquals("Likely Case", $chart['properties'][0]['goal_marker_label'][1]);
    }

    /**
     * @dataProvider providerGroupByReturnTheProperLabelName
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testGroupByReturnTheProperLabelName($actual, $group_by)
    {
        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&group_by=' . $group_by . '&dataset=likely';
        $return = $this->_restCall($url);

        $chart = $return['reply'];
        $this->assertEquals($actual, $chart['properties'][0]['label_name']);
    }

    /**
     * @return array
     */
    public function providerGroupByReturnTheProperLabelName()
    {
        global $current_language;

        $mod_strings = return_module_language($current_language, 'Opportunities');

        return array(
            array(get_label('LBL_SALES_STAGE', $mod_strings), 'sales_stage'),
            array(get_label('LBL_FORECAST', $mod_strings), 'forecast'),
            array(get_label('LBL_PROBABILITY', $mod_strings), 'probability')
        );
    }

    /**
     * @bug 54921
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testUsersWithNoDataChartContainsUsers()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user1->user_name = "user1";
        $user1->save();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user2->reports_to_id = $user1->id;
        $user2->user_name = "user2";
        $user2->save();

        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . $user1->id . '&group_by=sales_stage&dataset=likely&display_manager=true';
        $return = $this->_restCall($url);

        $chart = $return['reply'];
        $this->assertEquals(2, count($chart['values']));
    }

    /**
     * @bug 55246
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testNoGroupByReturnsGroupedByForecast()
    {
        global $current_language;

        $mod_strings = return_module_language($current_language, 'Forecasts');

        $url = 'Forecasts/chart?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$user->id . '&dataset=likely';
        $return = $this->_restCall($url);

        $chart = $return['reply'];

        $this->assertEquals($mod_strings['LBL_CHART_INCLUDED'], $chart['label'][0]);
        //$this->assertEquals("Likely", $chart['properties'][0]['goal_marker_label'][1]);
    }

}