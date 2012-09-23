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
class ForecastsWorksheetsApiTest extends RestTestBase
{
    /** @var array
     */
    private $reportee;

    /**
     * @var array
     */
    protected $manager;
    /**
     * @var TimePeriod
     */
    protected $timeperiod;

    /**
     * @var array
     */
    protected $managerData;

    /**
     * @var array
     */
    protected $repData;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        $this->manager = SugarTestForecastUtilities::createForecastUser();

        $this->reportee = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => $this->manager['user']->id)));

        $this->timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        $this->managerData = array(
            "amount" => $this->manager['opportunities_total'],
            "quota" => $this->manager['quota']->amount,
            "quota_id" => $this->manager['quota']->id,
            "best_case" => $this->manager['forecast']->best_case,
            "likely_case" => $this->manager['forecast']->likely_case,
            "worst_case" => $this->manager['forecast']->worst_case,
            "best_adjusted" => $this->manager['worksheet']->best_case,
            "likely_adjusted" => $this->manager['worksheet']->likely_case,
            "worst_adjusted" => $this->manager['worksheet']->worst_case,
            "commit_stage" => $this->manager['worksheet']->commit_stage,
            "forecast_id" => $this->manager['forecast']->id,
            "worksheet_id" => $this->manager['worksheet']->id,
            "show_opps" => true,
            "ops" => $this->manager['opportunities'],
            "op_worksheets" => $this->manager['opp_worksheets'],
            "id" => $this->manager['user']->id,
            "name" => 'Opportunities (' . $this->manager['user']->first_name . ' ' . $this->manager['user']->last_name . ')',
            "user_id" => $this->manager['user']->id,
            "timeperiod_id" => $this->timeperiod->id
        );

        $this->repData = array(
            "amount" => $this->reportee['opportunities_total'],
            "quota" => $this->reportee['quota']->amount,
            "quota_id" => $this->reportee['quota']->id,
            "best_case" => $this->reportee['forecast']->best_case,
            "likely_case" => $this->reportee['forecast']->likely_case,
            "worst_case" => $this->reportee['forecast']->worst_case,
            "best_adjusted" => $this->reportee['worksheet']->best_case,
            "likely_adjusted" => $this->reportee['worksheet']->likely_case,
            "worst_adjusted" => $this->reportee['worksheet']->worst_case,
            "commit_stage" => $this->manager['worksheet']->commit_stage,
            "forecast_id" => $this->reportee['forecast']->id,
            "worksheet_id" => $this->reportee['worksheet']->id,
            "show_opps" => true,
            "ops" => $this->reportee['opportunities'],
            "op_worksheets" => $this->reportee['opp_worksheets'],
            "id" => $this->reportee['user']->id,
            "name" => $this->reportee['user']->first_name . ' ' . $this->reportee['user']->last_name,
            "user_id" => $this->reportee['user']->id,
            "timeperiod_id" => $this->timeperiod->id
        );

    }

    public function tearDown()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
    }

    public static function tearDownAfterClass()
    {
        //SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        parent::tearDown();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastWorksheets()
    {
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id);
        $this->assertNotEmpty($response["reply"], "Rest reply is empty. Rep data should have been returned.");
    }


    /**
     * @group forecastapi
     * @group forecasts
     *
     */
    public function testForecastWorksheetSave()
    {

        $this->repData["op_worksheets"][0]->best_case = $this->repData["op_worksheets"][0]->best_case + 100;
        $this->repData["ops"][0]->probability = $this->repData["ops"][0]->probability + 10;

        $returnBest = '';
        $returnProb = '';
        $returnCommitStage = '';

        $postData = array(
            "amount" => $this->repData["ops"][0]->amount,
            "best_case" => $this->repData["op_worksheets"][0]->best_case,
            "likely_case" => $this->repData["op_worksheets"][0]->likely_case,
            "probability" => $this->repData["ops"][0]->probability,
            "commit_stage" => $this->repData["ops"][0]->commit_stage,
            "id" => $this->repData["ops"][0]->id,
            "worksheet_id" => $this->repData["op_worksheets"][0]->id,
            "timeperiod_id" => $this->timeperiod->id,
            "current_user" => $this->repData["id"],
            "assigned_user_id" => $this->repData["id"],
        );

        $response = $this->_restCall("ForecastWorksheets/" . $this->repData["ops"][0]->id, json_encode($postData), "PUT");

        $db = DBManagerFactory::getInstance();
        $db->commit();

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id . "&id=" . $this->repData["ops"][0]->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record)
        {
            if ($record["id"] == $this->repData["ops"][0]->id)
            {
                $returnBest = $record["best_case"];
                $returnProb = $record["probability"];
                $returnCommitStage = $record["commit_stage"];
            }
        }

        //check to see if the data to the Opportunity table was saved
        $this->assertEquals($this->repData["ops"][0]->probability, $returnProb, "Opportunity data was not saved.");

        //check to see if the best_case in the Worksheet table was saved
        $this->assertEquals($this->repData["op_worksheets"][0]->best_case, $returnBest, "Worksheet best_case was not saved.");

        //check to see if the commit_stage in worksheet table was saved
        $this->assertEquals($this->repData["op_worksheets"][0]->commit_stage, $returnCommitStage, "Worksheet commit_stage was not saved.");
    }


    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testWorksheetVersionSave()
    {

        $this->repData["op_worksheets"][0]->best_case = $this->repData["op_worksheets"][0]->best_case + 100;
        $this->repData["ops"][0]->probability = $this->repData["ops"][0]->probability + 10;
        $returnBest = '';
        $returnProb = '';

        $postData = array("amount" => $this->repData["ops"][0]->amount,
            "best_case" => $this->repData["op_worksheets"][0]->best_case,
            "likely_case" => $this->repData["op_worksheets"][0]->likely_case,
            "commit_stage" => $this->repData["commit_stage"],
            "probability" => $this->repData["ops"][0]->probability,
            "id" => $this->repData["ops"][0]->id,
            "worksheet_id" => $this->repData["op_worksheets"][0]->id,
            "timeperiod_id" => $this->timeperiod->id,
            "current_user" => $this->repData["id"],
            "assigned_user_id" => $this->repData["id"],
            "draft" => 1
        );

        // set the current user to salesrep
        $this->_user = $this->reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";

        $response = $this->_restCall("ForecastWorksheets/" . $this->repData["ops"][0]->id, json_encode($postData), "PUT");

        $db = DBManagerFactory::getInstance();
        $db->commit();

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $this->repData["ops"][0]->id) {
                $returnBest = $record["best_case"];
                $returnProb = $record["probability"];
                $returnVersion = $record["version"];
            }
        }

        //check to see if the draft data comes back
        $this->assertEquals("0", $returnVersion, "Draft Data was not returned.");

        //Now, save as a regular version so things will be reset.
        $postData["draft"] = 0;
        $response = $this->_restCall("ForecastWorksheets/" . $this->repData["ops"][0]->id, json_encode($postData), "PUT");

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $this->repData["ops"][0]->id) {
                $returnBest = $record["best_case"];
                $returnProb = $record["probability"];
                $returnVersion = $record["version"];
            }
        }

        //check to see if the live data comes back
        $this->assertEquals("1", $returnVersion, "Live Data was not returned.");
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testWorksheetDraftVisibility()
    {

        $this->repData["op_worksheets"][0]->best_case = $this->repData["op_worksheets"][0]->best_case + 100;
        $this->repData["ops"][0]->probability = $this->repData["ops"][0]->probability + 10;
        $returnBest = '';
        $returnProb = '';

        $postData = array("amount" => $this->repData["ops"][0]->amount,
            "best_case" => $this->repData["op_worksheets"][0]->best_case,
            "likely_case" => $this->repData["op_worksheets"][0]->likely_case,
            "commit_stage" => $this->repData["commit_stage"],
            "probability" => $this->repData["ops"][0]->probability,
            "id" => $this->repData["ops"][0]->id,
            "worksheet_id" => $this->repData["op_worksheets"][0]->id,
            "timeperiod_id" => $this->timeperiod->id,
            "current_user" => $this->repData["id"],
            "assigned_user_id" => $this->repData["id"],
            "draft" => 1
        );

        // set the current user to salesrep
        $this->_user = $this->reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";

        $response = $this->_restCall("ForecastWorksheets/" . $this->repData["ops"][0]->id, json_encode($postData), "PUT");

        $db = DBManagerFactory::getInstance();
        $db->commit();

        // set the current user to Manager
        $this->_user = $this->manager['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";

        // now get the data back to see if it we get the live version, not the draft version
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $this->repData["ops"][0]->id) {
                $returnBest = $record["best_case"];
                $returnProb = $record["probability"];
                $returnVersion = $record["version"];
            }
        }

        //check to see if the live data comes back
        $this->assertEquals("1", $returnVersion, "Live Data was not returned for Manager.");

        // set the current user to salesrep
        $this->_user = $this->reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";

        //Now, save as a regular version so things will be reset.
        $postData["draft"] = 0;
        $response = $this->_restCall("ForecastWorksheets/" . $this->repData["ops"][0]->id, json_encode($postData), "PUT");

        $db->commit();

        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $this->repData["id"] . "&timeperiod_id=" . $this->timeperiod->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record) {
            if ($record["id"] == $this->repData["ops"][0]->id) {
                $returnBest = $record["best_case"];
                $returnProb = $record["probability"];
                $returnVersion = $record["version"];
            }
        }

        //check to see if the live data comes back
        $this->assertEquals("1", $returnVersion, "Live Data was not returned.");
    }
}