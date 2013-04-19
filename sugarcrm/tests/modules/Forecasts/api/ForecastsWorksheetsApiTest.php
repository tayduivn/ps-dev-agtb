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


require_once('modules/Forecasts/clients/base/api/ForecastsWorksheetApi.php');
require_once("modules/Forecasts/clients/base/api/ForecastWorksheetsFilterApi.php");
require_once('include/api/RestService.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsWorksheetsApiTest extends Sugar_PHPUnit_Framework_TestCase
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
     * @var ForecastWorksheetsFilterApi
     */
    protected $filterApi;

    /**
     * @var ForecastsWorksheetApi
     */
    protected $putApi;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp('current_user');
        // get current settings

        SugarTestForecastUtilities::setUpForecastConfig(array(
                'show_worksheet_worst' => 1
            ));

        // setup the test users
        self::$manager = SugarTestForecastUtilities::createForecastUser();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(array(
            "user" => array(
                "reports_to" => self::$manager["user"]->id
            ),
            "opportunities" => array(
                "total" => 5,
                "include_in_forecast" => 5
            )
        ));

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
    }

    public function setUp()
    {
        $this->filterApi = new ForecastWorksheetsFilterApi();
        $this->putApi = new ForecastsWorksheetApi();
    }

    public function tearDown()
    {
        $this->filterApi = null;
        $GLOBALS["current_user"] = null;
        // override since we want to do this after the class is done
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::tearDownForecastConfig();
        parent::tearDown();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastWorksheets()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $GLOBALS["current_user"] = self::$reportee["user"];

        $response = $this->filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee['user']),
            array('user_id' => self::$repData['id'], 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets')
        );

        $this->assertNotEmpty($response["records"], "Rest reply is empty. Rep data should have been returned.");

        return $response['records'][0];
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastWorksheetsChart()
    {

        $GLOBALS["current_user"] = self::$reportee["user"];

        $response = $this->filterApi->forecastWorksheetsChartGet(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee['user']),
            array('user_id' => self::$repData['id'], 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets')
        );

        $this->assertNotEmpty($response["values"], "Rest reply is empty. Rep chart data should have been returned.");

        return $response['values'];
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @depends testForecastWorksheets
     *
     * @param array $worksheet  The worksheet we want to work with throughout the test
     * @return array
     */
    public function testForecastWorksheetSaveDraft($worksheet)
    {
        $this->markTestIncomplete('SFA team - check data from previous step: it consistently fails with a difference of 100');
        $GLOBALS["current_user"] = self::$reportee["user"];

        $best_case = $worksheet["best_case"] + 100;
        $probability = $worksheet["probability"] + 10;

        $postData = $worksheet;

        unset($postData['date_modified']);
        $postData['probability'] = $probability;
        $postData['best_case'] = $best_case;
        $postData['module'] = 'ForecastWorksheets';
        $postData['record'] = $worksheet['id'];

        $response = $this->putApi->forecastWorksheetSave(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee["user"]),
            $postData
        );

        //check to see if the data to the Worksheet table was saved
        $this->assertEquals($probability, $response['probability']);
        $this->assertEquals($best_case, $response['best_case']);

        // make sure we still have the draft record
        $this->assertEquals(1, $response['draft']);

        return $response;

    }

    /**
     * @depends testForecastWorksheetSaveDraft
     * @group forecastapi
     * @group forecasts
     * @param array $worksheet
     * @return array
     */
    public function testForecastWorksheetManagerDoesNotSeeDraftData($worksheet)
    {
        $GLOBALS["current_user"] = self::$manager["user"];

        $response = $this->filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('user_id' => self::$repData['id'], 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets')
        );

        $this->assertEmpty($response['records']);

        return $worksheet;
    }

    /**
     * @depends testForecastWorksheetManagerDoesNotSeeDraftData
     * @group forecastapi
     * @group forecasts
     * @param array $worksheet
     * @return array
     */
    public function testForecastWorksheetRepCommit($worksheet)
    {
        /* @var $worksheetBean ForecastWorksheet */
        $GLOBALS['current_user'] = self::$reportee['user'];
        $worksheetBean = BeanFactory::getBean('ForecastWorksheets');
        $commit = $worksheetBean->commitWorksheet(self::$reportee['user']->id, self::$timeperiod->id);

        $this->assertTrue($commit);

        return $worksheet;
    }

    /**
     * @depends testForecastWorksheetRepCommit
     * @group forecastapi
     * @group forecasts
     * @param $worksheet
     */
    public function testForecastWorksheetManagerSeesCommittedData($worksheet)
    {
        $GLOBALS["current_user"] = self::$manager["user"];

        $response = $this->filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('user_id' => self::$repData['id'], 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets')
        );

        $this->assertNotEmpty($response['records']);

        //loop through response and pick out the rows that correspond with ops[0]->id
        $resp_opp = array();
        foreach ($response["records"] as $record) {
            if ( $record["parent_id"] == $worksheet['parent_id'] && $record["parent_type"] == $worksheet['parent_type'] ) {
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
        // set the current user to Manager
        $GLOBALS["current_user"] = self::$manager["user"];

        $newUser = SugarTestForecastUtilities::createForecastUser(
            array("user" => array("reports_to" => self::$manager["user"]->id))
        );

        //remove any created worksheets for this user so we can test the edge case
        $worksheetIds = array();
        foreach ($newUser["opp_worksheets"] as $worksheet) {
            $worksheetIds[] = $worksheet->id;
        }
        SugarTestWorksheetUtilities::removeSpecificCreatedWorksheets($worksheetIds);

        $response = $this->filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('user_id' => $newUser["user"]->id, 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets')
        );

        $this->assertEmpty($response['records'], "Data was returned, this edge case should return no data");
    }
}
