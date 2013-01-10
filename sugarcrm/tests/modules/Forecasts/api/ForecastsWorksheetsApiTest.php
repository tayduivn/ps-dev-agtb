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

require_once("tests/rest/RestTestBase.php");

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsWorksheetsApiTest extends RestTestBase
{
    /** @var array
     */
    protected static $reportee;

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

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var isSetup
     */
    private static $_isSetup;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        // get current settings
        self::$admin = BeanFactory::getBean('Administration');
        $adminConfig = self::$admin->getConfigForModule('Forecasts');
        self::$_isSetup = $adminConfig['is_setup'];
        self::$admin->saveSetting('Forecasts', 'is_setup', '1', 'base');
        //Reset all columns to be shown
        self::$admin->saveSetting('Forecasts', 'show_worksheet_likely', 1, 'base');
        self::$admin->saveSetting('Forecasts', 'show_worksheet_best', 1, 'base');
        self::$admin->saveSetting('Forecasts', 'show_worksheet_worst', 1, 'base');

        // setup the test users
        self::$manager = SugarTestForecastUtilities::createForecastUser();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(
            array("user" => array("reports_to" => self::$manager["user"]->id))
        );

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = array(
            "amount" => self::$manager["opportunities_total"],
            "quota" => self::$manager["quota"]->amount,
            "quota_id" => self::$manager["quota"]->id,
            "best_case" => self::$manager["forecast"]->best_case,
            "likely_case" => self::$manager["forecast"]->likely_case,
            "worst_case" => self::$manager["forecast"]->worst_case,
            "best_adjusted" => self::$manager["worksheet"]->best_case,
            "likely_adjusted" => self::$manager["worksheet"]->likely_case,
            "worst_adjusted" => self::$manager["worksheet"]->worst_case,
            "commit_stage" => self::$manager["worksheet"]->commit_stage,
            "forecast_id" => self::$manager["forecast"]->id,
            "worksheet_id" => self::$manager["worksheet"]->id,
            "show_opps" => true,
            "ops" => self::$manager["opportunities"],
            "op_worksheets" => self::$manager["opp_worksheets"],
            "id" => self::$manager["user"]->id,
            "name" => "Opportunities (" . self::$manager["user"]->first_name . " " . self::$manager["user"]->last_name . ")",
            "user_id" => self::$manager["user"]->id,
            "timeperiod_id" => self::$timeperiod->id
        );

        self::$repData = array(
            "amount" => self::$reportee["opportunities_total"],
            "quota" => self::$reportee["quota"]->amount,
            "quota_id" => self::$reportee["quota"]->id,
            "best_case" => self::$reportee["forecast"]->best_case,
            "likely_case" => self::$reportee["forecast"]->likely_case,
            "worst_case" => self::$reportee["forecast"]->worst_case,
            "best_adjusted" => self::$reportee["worksheet"]->best_case,
            "likely_adjusted" => self::$reportee["worksheet"]->likely_case,
            "worst_adjusted" => self::$reportee["worksheet"]->worst_case,
            "commit_stage" => self::$manager["worksheet"]->commit_stage,
            "forecast_id" => self::$reportee["forecast"]->id,
            "worksheet_id" => self::$reportee["worksheet"]->id,
            "show_opps" => true,
            "ops" => self::$reportee["opportunities"],
            "op_worksheets" => self::$reportee["opp_worksheets"],
            "id" => self::$reportee["user"]->id,
            "name" => self::$reportee["user"]->first_name . " " . self::$reportee["user"]->last_name,
            "user_id" => self::$reportee["user"]->id,
            "timeperiod_id" => self::$timeperiod->id
        );


        parent::setUpBeforeClass();
    }

    public function tearDown()
    {
        // override since we want to do this after the class is done
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        self::$admin->saveSetting('Forecasts', 'is_setup', self::$_isSetup, 'base');
        //Reset all columns to be hidden
        self::$admin->saveSetting('Forecasts', 'show_worksheet_best', 0, 'base');
        self::$admin->saveSetting('Forecasts', 'show_worksheet_worst', 0, 'base');
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastWorksheets()
    {
        $tempUser = $GLOBALS["current_user"] = $this->_user;

        // set the current user to sales-rep
        $this->_user = self::$reportee["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        $response = $this->_restCall(
            "ForecastWorksheets?user_id=" . self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id
        );
        $this->assertNotEmpty($response["reply"], "Rest reply is empty. Rep data should have been returned.");

        // set the current user back.
        $this->_user = $tempUser;
        $GLOBALS["current_user"] = $tempUser;
        $this->authToken = "";

        return $response['reply'];
    }

    /**
     * @group forecastapi
     * @group forecasts
     *
     * @depends testForecastWorksheets
     *
     */
    public function testForecastWorksheetSaveDraft($worksheets)
    {
        $oldUser = $GLOBALS["current_user"];

        // set the current user to salesrep
        $this->_user = self::$reportee["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        $best_case = $worksheets[0]["best_case"] + 100;
        $probability = $worksheets[0]["probability"] + 10;
        $id = $worksheets[0]["id"];

        $postData = array(
            "best_case" => $best_case,
            "likely_case" => $worksheets[0]["likely_case"],
            "probability" => $probability,
            "commit_stage" => $worksheets[0]["commit_stage"],
            "id" => $id,
            "worksheet_id" => $worksheets[0]["worksheet_id"],
            "product_id" => $worksheets[0]["product_id"],
            "timeperiod_id" => self::$timeperiod->id,
            "assigned_user_id" => $worksheets[0]["assigned_user_id"],
            "draft" => 1
        );

        $response = $this->_restCall("ForecastWorksheets/" . $id, json_encode($postData), "PUT");

        // just a test to see
        $resp_get = $this->_restCall(
            "ForecastWorksheets?user_id=" . self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id
        );

        //check to see if the data to the Worksheet table was saved
        $this->assertEquals($probability, $response['reply']['probability'], "Put Response: " . var_export($response['reply'], true) . "Get Response: " . var_export($resp_get['reply'][0], true));
        $this->assertEquals($best_case, $response['reply']['best_case'], "Put Response: " . var_export($response['reply'], true) . "Get Response: " . var_export($resp_get['reply'][0], true));

        // make sure the worksheet was not modified via the date_modified from the worksheet
        $this->assertEquals($worksheets[0]['w_date_modified'], $response['reply']['w_date_modified']);

        // set the current user to original user
        $this->_user = $oldUser;
        $GLOBALS["current_user"] = $oldUser;
        $this->authToken = "";

        return $worksheets[0];

    }

    /**
     * @depends testForecastWorksheetSaveDraft
     * @param array $worksheet
     * @return array
     */
    public function testForecastWorksheetManagerDoesNotSeeDraftDataForChangedOpportunity($worksheet)
    {
        $this->_user = self::$manager["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall(
            "ForecastWorksheets?user_id=" . self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id
        );

        //loop through response and pick out the rows that correspond with ops[0]->id
        $resp_opp = array();
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $worksheet['id']) {
                $resp_opp = $record;
                break;
            }
        }

        // assert that the values returned are the values that were in the original worksheet.
        $this->assertEquals(
            $worksheet['probability'],
            $resp_opp['probability'],
            "Worksheet Probability Not Original Value"
        );
        $this->assertEquals($worksheet['best_case'], $resp_opp['best_case'], "Worksheet Best Case Not Original Value");

        return $worksheet;
    }

    /**
     * @depends testForecastWorksheetManagerDoesNotSeeDraftDataForChangedOpportunity
     * @param array $worksheet
     */
    public function testForecastWorksheetRepCommit($worksheet)
    {
        $oldUser = $GLOBALS["current_user"];

        // set the current user to salesrep
        $this->_user = self::$reportee["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        $best_case = $worksheet["best_case"] + 100;
        $probability = $worksheet["probability"] + 10;
        $id = $worksheet["id"];

        $postData = array(
            "best_case" => $best_case,
            "likely_case" => $worksheet["likely_case"],
            "probability" => $probability,
            "commit_stage" => $worksheet["commit_stage"],
            "id" => $id,
            "worksheet_id" => $worksheet["worksheet_id"],
            "product_id" => $worksheet["product_id"],
            "timeperiod_id" => self::$timeperiod->id,
            "assigned_user_id" => $worksheet["assigned_user_id"],
            "draft" => 0
        );

        $response = $this->_restCall("ForecastWorksheets/" . $id, json_encode($postData), "PUT");

        //check to see if the data to the Worksheet table was saved
        $this->assertEquals($probability, $response['reply']['probability'], "Worksheet probability was not saved.");
        $this->assertEquals($best_case, $response['reply']['best_case'], "Worksheet best_case was not saved.");

        // make sure the worksheet was not modified via the date_modified from the worksheet
        $this->assertNotEquals($worksheet['w_date_modified'], $response['reply']['w_date_modified']);

        // set the current user to original user
        $this->_user = $oldUser;
        $GLOBALS["current_user"] = $oldUser;
        $this->authToken = "";

        // return what the user got back so we can make sure it's the same that the manager gets back
        return $response['reply'];
    }

    /**
     * @depends testForecastWorksheetRepCommit
     * @param $worksheet
     */
    public function testForecastWorksheetManagerSeesCommittedData($worksheet)
    {
        $this->_user = self::$manager["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall(
            "ForecastWorksheets?user_id=" . self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id
        );

        //loop through response and pick out the rows that correspond with ops[0]->id
        $resp_opp = array();
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $worksheet['id']) {
                $resp_opp = $record;
                break;
            }
        }

        // assert that the values returned are the values that were in the original worksheet.
        $this->assertEquals(
            $worksheet['probability'],
            $resp_opp['probability'],
            "Worksheet Probability Not Committed Value"
        );
        $this->assertEquals($worksheet['best_case'], $resp_opp['best_case'], "Worksheet Best Case Not Committed Value");
    }


    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testNoResultsForManagerOnDraftSaveOfNewUser()
    {
        $oldUser = $GLOBALS["current_user"];

        $newUser = SugarTestForecastUtilities::createForecastUser(
            array("user" => array("reports_to" => self::$manager["user"]->id))
        );

        //remove any created worksheets for this user so we can test the edge case
        $worksheetIds = array();
        foreach ($newUser["opp_worksheets"] as $worksheet) {
            $worksheetIds[] = $worksheet->id;
        }
        SugarTestWorksheetUtilities::removeSpecificCreatedWorksheets($worksheetIds);

        // set the current user to Manager
        $this->_user = self::$manager["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";

        // now get the data back to see if it we get no rows
        $response = $this->_restCall(
            "ForecastWorksheets?user_id=" . $newUser["user"]->id . "&timeperiod_id=" . self::$timeperiod->id
        );

        $this->assertEmpty($response["reply"], "Data was returned, this edge case should return no data");

        // set the current user to original user
        $this->_user = $oldUser;
        $GLOBALS["current_user"] = $oldUser;
        $this->authToken = "";
    }
}