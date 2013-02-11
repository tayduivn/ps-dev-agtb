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
class ForecastsCommittedApiTest extends RestTestBase
{
    /** @var array
     */
    private static $reportee;

    /**
     * @var array
     */

    protected static $manager;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$manager = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = self::$manager;
  
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager->id;
        self::$reportee->save();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastsCommitted()
    {
        $response = $this->_restCall("Forecasts/committed");
        $this->assertEmpty($response["reply"], "Rest reply is not empty. No default manager data should have been returned.");
    }
    
     /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastsCommittedSubmit()
    {
        $this->markTestSkipped('This is all done in the ForecastWorksheetApiTest Class');
    	$manager = SugarTestForecastUtilities::createForecastUser();
        $reportee = SugarTestForecastUtilities::createForecastUser(array("user" => array("reports_to" => $manager["user"]->id)));
        $timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();
        
        $tempUser = $GLOBALS["current_user"] = $this->_user;
    	
    	// set the current user to salesrep
        $this->_user = $reportee["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";
        
        $postData = array(
        	"amount" => 100,
        	"base_rate" => 1,
        	"best_case" => 100,
        	"currency_id" => -99,
        	"forecast_type" => "Direct",
        	"likely_case" => 100,
        	"worst_case" => 10,
        	"opp_count" => 3,
        	"timeperiod_id" => $timeperiod->id,
        );        
        
        /*
         * Make the rest call so that the worksheet update SQL is fired. This is to test DB 
         * compatibility. There is nothing to assert, but if the SQL fails, the testing suite should
         * catastrophically die.
         */
        $response = $this->_restCall("Forecasts/committed", json_encode($postData), "POST");
    
        $this->assertNotEmpty($response["reply"], "The rest reply is empty.  Please check sugarcrm.log for database errors.");
        
        //Now we need to change one of the worksheet values and save it with the worksheet api as a "draft"
        $productId = $reportee["opp_worksheets"][0]->related_id;
        $oldBestCase = $reportee["opp_worksheets"][0]->best_case;
        $returnBest = "";
        
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $reportee["user"]->id . "&timeperiod_id=" . $timeperiod->id);
        
    	//find one of the rows we updated in the forecast save step.
    	$worksheetIndex = 0;
    	foreach($response["reply"] as $sheet)
    	{
    		if($sheet["product_id"] == $productId)
    		{
    			break;
    		}
    		$worksheetIndex++;
    	}        

		//save a draft version
        $postData = array(
            "best_case" => $response["reply"][$worksheetIndex]["best_case"] + 100,
            "likely_case" => $response["reply"][$worksheetIndex]["likely_case"],
            "worst_case" => $response["reply"][$worksheetIndex]["worst_case"],
            "probability" => $response["reply"][$worksheetIndex]["probability"],
            "commit_stage" => $response["reply"][$worksheetIndex]["commit_stage"],
            "id" => $response["reply"][$worksheetIndex]["id"],
            "worksheet_id" => $response["reply"][$worksheetIndex]["worksheet_id"],
            "product_id" => $response["reply"][$worksheetIndex]["product_id"],
            "timeperiod_id" => $timeperiod->id,            
            "assigned_user_id" => $response["reply"][$worksheetIndex]["assigned_user_id"],
            "draft" => 1
        );
        
        $response = $this->_restCall("ForecastWorksheets/" . $postData["id"], json_encode($postData), "PUT");
        
        //switch user to manager and get the worksheet for the rep. Verify committed value is returned instead of draft (live) value
        $this->_user = $manager["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";
        
        // now get the data back to see if it was saved to all the proper tables.
        $response = $this->_restCall("ForecastWorksheets?user_id=" . $reportee["user"]->id . "&timeperiod_id=" . $timeperiod->id);

        //loop through response and pick out the rows that correspond with ops[0]->id
        foreach ($response["reply"] as $record) {
            if ($record["product_id"] == $productId) {
                $returnBest = $record["best_case"];
            }
        }

        //check to see if the draft data comes back
        $this->assertEquals($oldBestCase, $returnBest, "Committed Data was not returned.");
        
        // set the current user back.
        $this->_user = $tempUser;
        $GLOBALS["current_user"] = $tempUser;
        $this->authToken = "";
    }    
}