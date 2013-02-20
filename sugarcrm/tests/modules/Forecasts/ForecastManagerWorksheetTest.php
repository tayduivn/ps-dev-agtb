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


class ForecastManagerWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Forecast
     */
    protected static $forecast;

    /**
     * @var Timeperiod
     */
    protected static $timeperiod;

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var Quota
     */
    protected static $user_quota;

    /**
     * @var Quota
     */
    protected static $manager_quota;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        self::$user = SugarTestUserUtilities::createAnonymousUser(false);
        self::$user->reports_to_id = $GLOBALS['current_user']->id;
        self::$user->save();

        self::$user_quota = SugarTestQuotaUtilities::createQuota(600);
        self::$user_quota->user_id = self::$user->id;
        self::$user_quota->quota_type = 'Direct';
        self::$user_quota->timeperiod_id = self::$timeperiod->id;
        self::$user_quota->save();

        $rollup_quota_user = SugarTestQuotaUtilities::createQuota(600);
        $rollup_quota_user->user_id = self::$user->id;
        $rollup_quota_user->quota_type = 'Rollup';
        $rollup_quota_user->timeperiod_id = self::$timeperiod->id;
        $rollup_quota_user->save();

        self::$manager_quota = SugarTestQuotaUtilities::createQuota(1000);
        self::$manager_quota->user_id = $GLOBALS['current_user']->id;
        self::$manager_quota->quota_type = 'Direct';
        self::$manager_quota->timeperiod_id = self::$timeperiod->id;
        self::$manager_quota->save();

        $rollup_quota = SugarTestQuotaUtilities::createQuota(1000);
        $rollup_quota->user_id = $GLOBALS['current_user']->id;
        $rollup_quota->quota_type = 'Rollup';
        $rollup_quota->timeperiod_id = self::$timeperiod->id;
        $rollup_quota->save();

        self::$forecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, self::$user);
    }

    public static function tearDownAfterClass()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM forecast_manager_worksheets WHERE user_id = '" . self::$user->id . "'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();

        SugarTestHelper::tearDown();
    }

    /**
     * @group forecasts
     */
    public function testSaveManagerDraft()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $ret = $worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray());

        // make sure that true was returned
        $this->assertTrue($ret);

        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertNotNull($ret, 'User Draft Forecast Manager Worksheet Not Found');
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals($GLOBALS['current_user']->id, $worksheet->assigned_user_id);
        $this->assertEquals(1, $worksheet->draft);
    }

    /**
     * @depends testSaveManagerDraft
     * @dataProvider caseFieldsDataProvider
     * @group forecasts
     */
    public function testSaveManagerDraftDoesNotCreateCommittedVersion()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $ret = $worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray());

        // make sure that true was returned
        $this->assertTrue($ret);

        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        $this->assertNull($ret);
    }

    /**
     * @depends testSaveManagerDraft
     * @dataProvider caseFieldsDataProvider
     * @group forecasts
     */
    public function testAdjustedCaseValuesEqualStandardCaseValues($field, $adjusted_field)
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals($worksheet->$field, $worksheet->$adjusted_field, 0, 2);
    }

    public static function caseFieldsDataProvider()
    {
        return array(
            array('likely_case', 'likely_case_adjusted'),
            array('best_case', 'best_case_adjusted'),
            array('worst_case', 'worst_case_adjusted'),
        );
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     */
    public function testQuotaWasPulledFromQuotasTable()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals(self::$user_quota->amount, $worksheet->quota, '', 2);
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     */
    public function testCommitManagerHasCommittedUserRow()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->commitManagerForecast($GLOBALS['current_user'], self::$timeperiod->id);


        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        $this->assertNotNull($ret, 'User Committed Forecast Manager Worksheet Not Found');
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals($GLOBALS['current_user']->id, $worksheet->assigned_user_id);
        $this->assertEquals(0, $worksheet->draft);
    }

    /**
     * @depends testCommitManagerHasCommittedUserRow
     * @group forecasts
     */
    public function testUserCommitsUpdatesMangerDraftAndUpdatesCommittedVersion()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $forecast = self::$forecast->toArray();
        $forecast['best_case'] += 100;
        $ret = $worksheet->reporteeForecastRollUp(self::$user, $forecast);

        // make sure that true was returned
        $this->assertTrue($ret);

        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        // just make sure that the best case on the committed version still equals the original value
        $this->assertEquals($forecast['best_case'], $worksheet->best_case);
    }

    /**
     * @group forecasts
     */
    public function testCommitManagerForecastReturnsFalseWhenUserNotAManager()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $return = $worksheet->commitManagerForecast(self::$user, self::$timeperiod->id);

        $this->assertFalse($return);
    }

    /**
     * @group forecasts
     */
    public function testManagerQuotaReCalcWorks()
    {
        // from the data created when the class was started, the manager had a quota of 1000
        // and the user had a quota of 600, so, it should return 400 as that is the difference
        $worksheet = new MockForecastManagerWorksheet();
        $worksheet->timeperiod_id = self::$timeperiod->id;

        $new_mgr_quota = $worksheet->recalcUserQuota($GLOBALS['current_user']->id);

        $this->assertEquals(400, $new_mgr_quota, '', 2);
    }
}


class MockForecastManagerWorksheet extends ForecastManagerWorksheet
{
    public function recalcUserQuota($user_id)
    {
        return parent::recalcUserQuota($user_id);
    }
}