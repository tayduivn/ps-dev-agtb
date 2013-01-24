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

class ForecastTest extends Sugar_PHPUnit_Framework_TestCase
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


    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        self::$user = SugarTestUserUtilities::createAnonymousUser(false);
        self::$user->reports_to_id = $GLOBALS['current_user']->id;
        self::$user->save();

        self::$forecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, self::$user);
    }

    public static function tearDownAfterClass()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM forecast_manager_worksheet WHERE user_id = '".self::$user->id."'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();

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
     * @group forecasts
     */
    public function testCommitManagerForecastReturnsFalseWhenUserNotAManager()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $return = $worksheet->commitManagerForecast(self::$user, self::$timeperiod->id);

        $this->assertFalse($return);
    }
}
