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
 * @group forecasts
 */
class ForecastsWorksheetsApiTest extends RestTestBase
{
	 /** @var array
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
    
	public static function setUpBeforeClass(){
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
            "ops" => self::$manager['opportunities'],
            "op_worksheets" => self::$manager['opp_worksheets'],
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
            "ops" => self::$reportee['opportunities'],
            "op_worksheets" => self::$reportee['opp_worksheets'],
            "id" => self::$reportee['user']->id,
            "name" => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            "user_id" => self::$reportee['user']->id,

        );

    }
    
    public static function tearDownAfterClass(){
    	/*SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        $GLOBALS['db']->query("DELETE FROM forecasts WHERE id IN ('" . self::$managerForecast->id . "','" . self::$repForecast->id . "')");
        $GLOBALS['db']->query("DELETE FROM timeperiods WHERE id ='" . self::$timeperiod->id . "';");*/
        
    	SugarTestForecastUtilities::cleanUpCreatedForecastUsers();   	 
    	parent::tearDown();
    }
    
    /*public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager;
        $GLOBALS['current_user'] = $this->_user;

    }*/
    
	public function tearDown(){}
	   
    /***
     * @group forecastapi
     */
    public function testForecastWorksheets()
    {
        $response = $this->_restCall("ForecastWorksheets?user_id=" . self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
        $this->assertNotEmpty($response["reply"], "Rest reply is empty. Rep data should have been returned.");        
    }
     
    /**
     * @group forecastapi
     */
   public function testForecastWorksheetSave(){
    	
    	self::$repData["op_worksheets"][0]->best_case = self::$repData["op_worksheets"][0]->best_case + 100;
    	self::$repData["ops"][0]->probability = self::$repData["ops"][0]->probability + 10;
        $returnBest = '';
        $returnProb = '';
        $returnCommitStage = '';
        
    	$postData = array("amount" => self::$repData["ops"][0]->amount,
                             "best_case" => self::$repData["op_worksheets"][0]->best_case,
                             "likely_case" => self::$repData["op_worksheets"][0]->likely_case,                            
                             "forecast" => self::$repData["forecast"],
                             "probability" => self::$repData["ops"][0]->probability,
                             "commit_stage" => self::$repData["ops"][0]->commit_stage,
                             "id" => self::$repData["ops"][0]->id,
                             "worksheet_id" => self::$repData["op_worksheets"][0]->id,
                             "timeperiod_id" => self::$timeperiod->id,
                             "current_user" => self::$repData["id"],
                             "assigned_user_id" => self::$repData["id"],
                        );
        
		$response = $this->_restCall("ForecastWorksheets/" . self::$repData["ops"][0]->id, json_encode($postData), "PUT");
			
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
				
		//loop through response and pick out the rows that correspond with ops[0]->id
		foreach($response["reply"] as $record)
		{
			if($record["id"] == self::$repData["ops"][0]->id)
			{
				$returnBest = $record["best_case"];
				$returnProb = $record["probability"];
                $returnCommitStage = $record["commit_stage"];
			}
		}
		
		//check to see if the data to the Opportunity table was saved
		$this->assertEquals($returnProb, self::$repData["ops"][0]->probability, "Opportunity data was not saved.");
				
		//check to see if the best_case in the Worksheet table was saved
		$this->assertEquals($returnBest, self::$repData["op_worksheets"][0]->best_case, "Worksheet best_case was not saved.");

        //check to see if the commit_stage in worksheet table was saved
        $this->assertEquals($returnCommitStage, self::$repData["op_worksheets"][0]->commit_stage, "Worksheet commit_stage was not saved.");
				
    }
    
    /**
    * @group forecastapi
    */
    public function testWorksheetVersionSave(){
    	
    	self::$repData["op_worksheets"][0]->best_case = self::$repData["op_worksheets"][0]->best_case + 100;
    	self::$repData["ops"][0]->probability = self::$repData["ops"][0]->probability + 10;
        $returnBest = '';
        $returnProb = '';
        
    	$postData = array("amount" => self::$repData["ops"][0]->amount,
                             "best_case" => self::$repData["op_worksheets"][0]->best_case,
                             "likely_case" => self::$repData["op_worksheets"][0]->likely_case,                            
                             "forecast" => self::$repData["forecast"],
                             "probability" => self::$repData["ops"][0]->probability,
                             "id" => self::$repData["ops"][0]->id,
                             "worksheet_id" => self::$repData["op_worksheets"][0]->id,
                             "timeperiod_id" => self::$timeperiod->id,
                             "current_user" => self::$repData["id"],
                             "assigned_user_id" => self::$repData["id"],
                             "draft" => 1
                        );
                        
        // set the current user to salesrep
        $this->_user = self::$reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";
        
		$response = $this->_restCall("ForecastWorksheets/" . self::$repData["ops"][0]->id, json_encode($postData), "PUT");
		
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
				
		//loop through response and pick out the rows that correspond with ops[0]->id
		foreach($response["reply"] as $record)
		{
			if($record["id"] == self::$repData["ops"][0]->id)
			{
				$returnBest = $record["best_case"];
				$returnProb = $record["probability"];
				$returnVersion = $record["version"];
				break;
			}
		}
		
		//check to see if the draft data comes back
		$this->assertEquals("0", $returnVersion, "Draft Data was not returned.");
		
		//Now, save as a regular version so things will be reset.
		$postData["draft"] = 0;			
		$response = $this->_restCall("ForecastWorksheets/" . self::$repData["ops"][0]->id, json_encode($postData), "PUT");
		
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
		
		//loop through response and pick out the rows that correspond with ops[0]->id
		foreach($response["reply"] as $record)
		{
			if($record["id"] == self::$repData["ops"][0]->id)
			{
				$returnBest = $record["best_case"];
				$returnProb = $record["probability"];
				$returnVersion = $record["version"];
				break;
			}
		}
		
		//check to see if the live data comes back
		$this->assertEquals("1", $returnVersion, "Live Data was not returned.");			
    }
    
    /**
    * @group forecastapi
    */
    public function testWorksheetDraftVisibility(){
    	
    	self::$repData["op_worksheets"][0]->best_case = self::$repData["op_worksheets"][0]->best_case + 100;
    	self::$repData["ops"][0]->probability = self::$repData["ops"][0]->probability + 10;
        $returnBest = '';
        $returnProb = '';
        
    	$postData = array("amount" => self::$repData["ops"][0]->amount,
                             "best_case" => self::$repData["op_worksheets"][0]->best_case,
                             "likely_case" => self::$repData["op_worksheets"][0]->likely_case,                            
                             "forecast" => self::$repData["forecast"],
                             "probability" => self::$repData["ops"][0]->probability,
                             "id" => self::$repData["ops"][0]->id,
                             "worksheet_id" => self::$repData["op_worksheets"][0]->id,
                             "timeperiod_id" => self::$timeperiod->id,
                             "current_user" => self::$repData["id"],
                             "assigned_user_id" => self::$repData["id"],
                             "draft" => 1
                        );
                        
        // set the current user to salesrep
        $this->_user = self::$reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";
        
		$response = $this->_restCall("ForecastWorksheets/" . self::$repData["ops"][0]->id, json_encode($postData), "PUT");
		
		// set the current user to Manager
        $this->_user = self::$manager['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";
		
		// now get the data back to see if it we get the live version, not the draft version
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
				
		//loop through response and pick out the rows that correspond with ops[0]->id
		foreach($response["reply"] as $record)
		{
			if($record["id"] == self::$repData["ops"][0]->id)
			{
				$returnBest = $record["best_case"];
				$returnProb = $record["probability"];
				$returnVersion = $record["version"];
				break;
			}
		}
		
		//check to see if the live data comes back
		$this->assertEquals("1", $returnVersion, "Live Data was not returned for Manager.");
		
		// set the current user to salesrep
        $this->_user = self::$reportee['user'];
        $GLOBALS['current_user'] = $this->_user;
        $this->authToken = "";
		
		//Now, save as a regular version so things will be reset.
		$postData["draft"] = 0;			
		$response = $this->_restCall("ForecastWorksheets/" . self::$repData["ops"][0]->id, json_encode($postData), "PUT");
		
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$repData["id"] . "&timeperiod_id=" . self::$timeperiod->id);
		
		//loop through response and pick out the rows that correspond with ops[0]->id
		foreach($response["reply"] as $record)
		{
			if($record["id"] == self::$repData["ops"][0]->id)
			{
				$returnBest = $record["best_case"];
				$returnProb = $record["probability"];
				$returnVersion = $record["version"];
				break;
			}
		}
		
		//check to see if the live data comes back
		$this->assertEquals("1", $returnVersion, "Live Data was not returned.");			
    }
    
    /**
     * @group forecastapi
     */
     /*This one needs moved to ForecastsWorksheetManagerApiTest.
     public function testForecastWorksheetQuotaRecalc(){
     	self::$repQuota->amount = 5000;
    	  
    	$postData = array("amount" => self::$repOpp->amount,
                             "quota" => self::$repQuota->amount,
                             "quota_id" => self::$repQuota->id,
                             "best_case" => self::$repForecast->best_case,
                             "likely_case" => self::$repForecast->likely_case,
                             "worst_case" => self::$repForecast->worst_case,
                             "best_adjusted" => self::$managerWorksheetRep->best_case,
                             "likely_adjusted" => self::$managerWorksheetRep->likely_case,
                             "worst_adjusted" => self::$managerWorksheetRep->worst_case,
                             "forecast" => intval(self::$managerWorksheetRep->forecast),
                             "forecast_id" => self::$repForecast->id,
                             "id" => self::$repForecast->id,
                             "worksheet_id" => self::$managerWorksheetRep->id,
                             "show_opps" => false,
                             "name" => self::$reportee->first_name . ' ' . self::$reportee->last_name,
                             "user_id" => self::$reportee->id,
                             "current_user" => self::$manager->id,
                             "timeperiod_id" => self::$timeperiod->id
                        );
        $response = $this->_restCall("ForecastManagerWorksheets/" . self::$repForecast->id, json_encode($postData), "PUT");
						
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=". self::$manager->id . "&timeperiod_id=" . self::$timeperiod->id);
		
		//check to see if the Quota was auto calculated
		$this->assertEquals($response["reply"][0]["quota"], self::$managerQuotaRollup->amount - self::$repQuota->amount, "Quota data was not auto calculated.");
     }*/
     
     /**
     * @group forecastapi
     */
     /*This one needs moved to ForecastsWorksheetManagerApiTest.
     public function testForecastWorksheetQuotaRecalcReps(){
    	  
    	$postData = array("amount" => 0,
                             "quota" => 100000,
                             "quota_id" => '',
                             "best_case" => 0,
                             "likely_case" => 0,
                             "worst_case" => 0,
                             "best_adjusted" => 0,
                             "likely_adjusted" => 0,
                             "worst_adjusted" => 0,
                             "forecast" => 0,
                             "forecast_id" => '',
                             "id" => self::$manager2->id,
                             "worksheet_id" => '',
                             "show_opps" => false,
                             "name" => self::$manager2->first_name . ' ' . self::$manager2->last_name,
                             "user_id" => self::$manager2->id,
                             "current_user" => self::$manager->id,
                             "timeperiod_id" => self::$timeperiod->id
                        );
        $response = $this->_restCall("ForecastManagerWorksheets/" . self::$manager2->id, json_encode($postData), "PUT");
						
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=". self::$manager2->id . "&timeperiod_id=" . self::$timeperiod->id);
		
		//check to see if the Quota was auto calculated
		$this->assertEquals($response["reply"][0]["quota"], 100000, "Quota data was not auto calculated.");
     }*/
}