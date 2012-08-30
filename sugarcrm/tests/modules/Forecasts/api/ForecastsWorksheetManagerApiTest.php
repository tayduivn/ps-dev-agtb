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
    private static $reportee2;

    /**
     * @var array
     */
    protected static $manager;
    
    /**
     * @var array
     */
    protected static $manager2;
    
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
    protected static $managerData2;

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
        self::$manager2 = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$manager['user']->id)));

        self::$reportee = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$manager['user']->id)));
        self::$reportee2 = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$manager2['user']->id)));

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
        
        self::$managerData2 = array("amount" => self::$manager2['opportunities_total'],
            "quota" => self::$manager2['quota']->amount,
            "quota_id" => self::$manager2['quota']->id,
            "best_case" => self::$manager2['forecast']->best_case,
            "likely_case" => self::$manager2['forecast']->likely_case,
            "worst_case" => self::$manager2['forecast']->worst_case,
            "best_adjusted" => self::$manager2['worksheet']->best_case,
            "likely_adjusted" => self::$manager2['worksheet']->likely_case,
            "worst_adjusted" => self::$manager2['worksheet']->worst_case,
            "forecast" => intval(self::$manager2['worksheet']->forecast),
            "forecast_id" => self::$manager2['forecast']->id,
            "worksheet_id" => self::$manager2['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager2['user']->id,
            "name" => 'Opportunities (' . self::$manager2['user']->first_name . ' ' . self::$manager2['user']->last_name . ')',
            "user_id" => self::$manager2['user']->id,

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
	
	public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager['user'];
        $this->_oldUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->_user;
        $GLOBALS['log']->fatal("Setup");
    }
    
    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();

        parent::tearDown();
    }

    //Override tearDown so we don't lose the current user
    public function tearDown()
    {
		$GLOBALS['current_user'] = $this->_oldUser;
    }


    /**
     * This test asserts that we get back data.
     * 
     */
    public function testPassedInUserIsManager()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
        $this->assertNotEmpty($restReply['reply'], "Reply empty, user not a manager"); 
    }

    public function testPassedInUserIsNotManagerReturnsEmpty()
    {
        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$reportee['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
        $this->assertEmpty($restReply['reply'], "rest reply is not empty");
    }

    public function testCurrentUserIsNotManagerReturnsEmpty()
    {
        // save the current user
        $_old_current_user = $GLOBALS['current_user'];
        
        // set the current user to the reportee
        $this->_user = self::$reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
		
        // run the test
        $restReply = $this->_restCall("ForecastManagerWorksheets?timeperiod_id=" . self::$timeperiod->id);
                
        $this->assertEmpty($restReply['reply'], "rest reply is not empty");

        // reset current user;
        $GLOBALS['current_user'] = $_old_current_user;
        $this->_user = $_old_current_user;
    }

    /**
     * @bug 54619
     * @group 54619
     */
    public function testAdjustedNumbersShouldBeSameAsNonAdjustedColumns()
    {
        $rep_worksheet = BeanFactory::getBean('Worksheet', self::$repData['worksheet_id']);
        $rep_worksheet->deleted = 1;
        $rep_worksheet->save();
        $GLOBALS['db']->commit();

        $localRepData = self::$repData;

        $localRepData['best_adjusted'] = SugarTestForecastUtilities::formatTestNumber($localRepData['best_case']);
        $localRepData['likely_adjusted'] = SugarTestForecastUtilities::formatTestNumber($localRepData['likely_case']);
        $localRepData['worst_adjusted'] = SugarTestForecastUtilities::formatTestNumber($localRepData['worst_case']);
        $localRepData['forecast'] = SugarTestForecastUtilities::formatTestNumber(0);
        $localRepData['worksheet_id'] = '';

        $restReply = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
		foreach($restReply['reply'] as $record)
		{
			if($record["user_id"] == $localRepData["user_id"])
			{	
		        $this->assertEquals($localRepData['best_adjusted'], $record['best_adjusted'], "best_adjusted numbers should be the same");
		        $this->assertEquals($localRepData['likely_adjusted'], $record['likely_adjusted'], "likely_adjusted numbers should be the same");
		        $this->assertEquals($localRepData['worst_adjusted'], $record['worst_adjusted'], "worst_adjusted numbers should be the same");
		        $this->assertEquals($localRepData['forecast'], $record['forecast'], "forecast numbers should be the same");
		        break;
			}
		}
		
        $rep_worksheet->deleted = 0;
        $rep_worksheet->save();
        $GLOBALS['db']->commit();
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
        $this->assertEquals(3, count($restReply['reply']), "deleted user's data should not be listed in worksheet table");
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

        $this->assertEquals($expected[0]['amount'], $restReply['reply'][0]['amount']);
        $this->assertEquals($expected[0]['quota'], $restReply['reply'][0]['quota']);
        $this->assertEquals($expected[0]['best_case'], $restReply['reply'][0]['best_case']);
        $this->assertEquals($expected[0]['likely_case'], $restReply['reply'][0]['likely_case']);
        $this->assertEquals($expected[0]['worst_case'], $restReply['reply'][0]['worst_case']);
        $this->assertEquals($expected[0]['best_adjusted'], $restReply['reply'][0]['best_adjusted']);
        $this->assertEquals($expected[0]['likely_adjusted'], $restReply['reply'][0]['likely_adjusted']);
        $this->assertEquals($expected[0]['worst_adjusted'], $restReply['reply'][0]['worst_adjusted']);

        $this->assertEquals($expected[1]['amount'], $restReply['reply'][1]['amount']);
        $this->assertEquals($expected[1]['quota'], $restReply['reply'][1]['quota']);
        $this->assertEquals($expected[1]['best_case'], $restReply['reply'][1]['best_case']);
        $this->assertEquals($expected[1]['likely_case'], $restReply['reply'][1]['likely_case']);
        $this->assertEquals($expected[1]['worst_case'], $restReply['reply'][1]['worst_case']);
        $this->assertEquals($expected[1]['best_adjusted'], $restReply['reply'][1]['best_adjusted']);
        $this->assertEquals($expected[1]['likely_adjusted'], $restReply['reply'][1]['likely_adjusted']);
        $this->assertEquals($expected[1]['worst_adjusted'], $restReply['reply'][1]['worst_adjusted']);

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
            "best_adjusted" => SugarTestForecastUtilities::formatTestNumber($tmpWorksheet->best_case),
            "best_case" => SugarTestForecastUtilities::formatTestNumber($tmpForecast->best_case),
            "forecast" => intval($tmpWorksheet->forecast),
            "forecast_id" => $tmpForecast->id,
            "id" => self::$reportee["user"]->id,
            "likely_adjusted" => SugarTestForecastUtilities::formatTestNumber($tmpWorksheet->likely_case),
            "likely_case" => SugarTestForecastUtilities::formatTestNumber($tmpForecast->likely_case),
            "name" => self::$reportee["user"]->first_name . " " . self::$reportee["user"]->last_name,
            "quota" => SugarTestForecastUtilities::formatTestNumber(self::$reportee['quota']->amount),
            "quota_id" => self::$reportee['quota']->id,
            "show_opps" => false,
            "user_id" => self::$reportee["user"]->id,
            "worksheet_id" => $tmpWorksheet->id,
            "worst_adjusted" => SugarTestForecastUtilities::formatTestNumber($tmpWorksheet->worst_case),
            "worst_case" => SugarTestForecastUtilities::formatTestNumber($tmpForecast->worst_case),
            "timeperiod_id" => self::$timeperiod->id
        );

        $this->assertEquals($expected["amount"], $restReply['reply'][1]["amount"]);
        $this->assertEquals($expected["best_adjusted"], $restReply['reply'][1]["best_adjusted"]);
        $this->assertEquals($expected["best_case"], $restReply['reply'][1]["best_case"]);
        $this->assertEquals($expected["forecast"], $restReply['reply'][1]["forecast"]);
        $this->assertEquals($expected["forecast_id"], $restReply['reply'][1]["forecast_id"]);
        $this->assertEquals($expected["id"], $restReply['reply'][1]["id"]);
        $this->assertEquals($expected["likely_adjusted"], $restReply['reply'][1]["likely_adjusted"]);
        $this->assertEquals($expected["likely_case"], $restReply['reply'][1]["likely_case"]);
        $this->assertEquals($expected["name"], $restReply['reply'][1]["name"]);
        $this->assertEquals($expected["quota"], $restReply['reply'][1]["quota"]);
        $this->assertEquals($expected["quota_id"], $restReply['reply'][1]["quota_id"]);
        $this->assertEquals($expected["show_opps"], $restReply['reply'][1]["show_opps"]);
        $this->assertEquals($expected["user_id"], $restReply['reply'][1]["user_id"]);
        $this->assertEquals($expected["worksheet_id"], $restReply['reply'][1]["worksheet_id"]);
        $this->assertEquals($expected["worst_adjusted"], $restReply['reply'][1]["worst_adjusted"]);
        $this->assertEquals($expected["worst_case"], $restReply['reply'][1]["worst_case"]);
        $this->assertEquals($expected["timeperiod_id"], $restReply['reply'][1]["timeperiod_id"]);
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
    
    /**
     * @group forecastapi
     */
     public function testWorksheetVersionSave()
     {
     	$postData = array("amount" => self::$managerData["amount"],
                             "quota" => self::$managerData["quota"],
                             "quota_id" => self::$managerData["quota_id"],
                             "best_case" => self::$managerData["best_case"],
                             "likely_case" => self::$managerData["likely_case"],
                             "worst_case" => self::$managerData["worst_case"],
                             "best_adjusted" => self::$managerData["best_adjusted"],
                             "likely_adjusted" => self::$managerData["likely_adjusted"],
                             "worst_adjusted" => self::$managerData["worst_adjusted"],
                             "forecast" => self::$managerData["forecast"],
                             "forecast_id" => self::$managerData["forecast_id"],
                             "id" => self::$managerData["id"],
                             "worksheet_id" => self::$managerData["worksheet_id"],
                             "show_opps" => self::$managerData["show_opps"],
                             "name" => self::$managerData["name"],
                             "user_id" => self::$managerData["user_id"],
                             "current_user" => self::$managerData["user_id"],
                             "timeperiod_id" => self::$timeperiod->id,
                             "draft" => 1
                        );
 
        //save draft version
		$response = $this->_restCall("ForecastManagerWorksheets/" . self::$managerData["user_id"], json_encode($postData), "PUT");
		
		//see if draft version comes back
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
		
		$this->assertEquals("0", $response["reply"][0]["version"], "Draft version was not returned.");
		
		//Now, save as a regular version so things will be reset.
		$postData["draft"] = 0;		 
		$response = $this->_restCall("ForecastManagerWorksheets/" . self::$managerData["user_id"], json_encode($postData), "PUT");
		
		//now, see if the regular version comes back.
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$manager['user']->id . '&timeperiod_id=' . self::$timeperiod->id);
		$this->assertEquals("1", $response["reply"][0]["version"], "Comitted version was not returned.");
	 
     }
     
     /**
     * @group forecastapi
     */
     public function testWorksheetDraftVisibility()
     {       
        
        
     	$GLOBALS['log']->fatal("Original: " . self::$managerData2["best_adjusted"]);
     	self::$managerData2["best_adjusted"] = self::$managerData2["best_adjusted"] + 100;
     	
     	$postData = array("amount" => self::$managerData2["amount"],
                             "quota" => self::$managerData2["quota"],
                             "quota_id" => self::$managerData2["quota_id"],
                             "best_case" => self::$managerData2["best_case"],
                             "likely_case" => self::$managerData2["likely_case"],
                             "worst_case" => self::$managerData2["worst_case"],
                             "best_adjusted" => self::$managerData2["best_adjusted"],
                             "likely_adjusted" => self::$managerData2["likely_adjusted"],
                             "worst_adjusted" => self::$managerData2["worst_adjusted"],
                             "forecast" => self::$managerData2["forecast"],
                             "forecast_id" => self::$managerData2["forecast_id"],
                             "id" => self::$managerData2["id"],
                             "worksheet_id" => self::$managerData2["worksheet_id"],
                             "show_opps" => self::$managerData2["show_opps"],
                             "name" => self::$managerData2["name"],
                             "user_id" => self::$managerData2["user_id"],
                             "current_user" => self::$managerData2["user_id"],
                             "timeperiod_id" => self::$timeperiod->id,
                             "draft" => 1
                        );
        // set the current user to manager2
        $this->_user = self::$manager2['user'];
        $GLOBALS['current_user'] = $this->_user;
        
		//save draft version for manager2
		$response = $this->_restCall("ForecastManagerWorksheets/" . self::$managerData2["user_id"], json_encode($postData), "PUT");
		
		// reset current user to manager1
        $this->_user = self::$manager['user'];
        $GLOBALS['current_user'] = $this->_user;
        
		$GLOBALS['log']->fatal($GLOBALS['current_user']->id);
		$GLOBALS['log']->fatal("Manager2: " . self::$manager2['user']->id);
		$GLOBALS['log']->fatal("Manager:  " . self::$manager['user']->id);
	
		//Check the table as a manager1 to see if the draft version is hidden 
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$managerData2["user_id"] . '&timeperiod_id=' . self::$timeperiod->id);
		
		$GLOBALS['log']->fatal($postData);
		$GLOBALS['log']->fatal($response["reply"]);
		$this->assertEquals(self::$managerData2["best_adjusted"] - 100, $response["reply"][0]["best_adjusted"], "Draft version was returned");
     }
}

