<?php
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
	/**
	 * @var User Manager user
	 */
	protected static $manager;
	
	/**
	 * @var User Reportee user
	 */
	protected static $reportee;
	
	/**
	 * @var User Secondary Manager user for "new user" test
	 */
	protected static $manager2;
	
	/**
	 * @var User Secondary Reportee user to make manager2 a manager
	 */
	protected static $reportee2;
	
	/**
	 * @var Timeperiod Timeperiod ID
	 */
	protected static $timeperiod;
	
	/**
	 * @var	Opportunity Manager Opportunity;
	 */
	protected static $managerOpp;
	
	/**
	 * @var Opportunity Rep Opportunity;
	 */
	protected static $repOpp;
	
	/**
	 * @var Quota Manager Quota;
	 */
	protected static $managerQuota;
	
	/**
	 * @var Quota Manager Quota Rollup version
	 */ 
	protected static $managerQuotaRollup;
	
	/**
	 * @var Quota Rep Quota;
	 */
	protected static $repQuota;
	
	/**
	 * @var Forecast Manager Forecast
	 */
	protected static $managerForecast;
	
	/**
	 * @var Forecast Rep Forecast;
	 */
	protected static $repForecast;
	
	/**
	 * @var Worksheet Manager Worksheet;
	 */
	protected static $managerWorksheet;
	
	/**
	 * @var Worksheet Manager Worksheet for the reportee;
	 */
	protected static $managerWorksheetRep;
	
	/**
	 * @var Worksheet Rep Worksheet;
	 */
	protected static $repWorksheet;

	public static function setUpBeforeClass(){
    	self::$manager = SugarTestUserUtilities::createAnonymousUser();
    	self::$manager->save();
        
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager->id;
        self::$reportee->save();
        
        self::$manager2 = SugarTestUserUtilities::createAnonymousUser();
        self::$manager2->reports_to_id = self::$manager->id;
        self::$manager2->save();
        
        self::$reportee2 = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee2->reports_to_id = self::$manager2->id;
        self::$reportee2->save();
        
        //create timeperiod
        self::$timeperiod = new TimePeriod();
        self::$timeperiod->start_date = "2012-01-01";
        self::$timeperiod->end_date = "2012-03-31";
        self::$timeperiod->name = "Test";
        self::$timeperiod->save();

        //setup opps
        self::$managerOpp = SugarTestOpportunityUtilities::createOpportunity();
        self::$managerOpp->assigned_user_id = self::$manager->id;
        self::$managerOpp->timeperiod_id = self::$timeperiod->id;
        self::$managerOpp->team_set_id = 1;
        self::$managerOpp->amount = 1800;
        self::$managerOpp->forecast = 1;
        self::$managerOpp->save();

        self::$repOpp = SugarTestOpportunityUtilities::createOpportunity();
        self::$repOpp->assigned_user_id = self::$reportee->id;
        self::$repOpp->timeperiod_id = self::$timeperiod->id;
        self::$repOpp->team_set_id = 1;
        self::$repOpp->amount = 1300;
        self::$repOpp->forecast = 1;
        self::$repOpp->save();

        //setup quotas
        self::$managerQuota = SugarTestQuotaUtilities::createQuota(2000);
        self::$managerQuota->user_id = self::$manager->id;
        self::$managerQuota->quota_type = "Direct";
        self::$managerQuota->timeperiod_id = self::$timeperiod->id;
        self::$managerQuota->save();
        
        self::$managerQuotaRollup = SugarTestQuotaUtilities::createQuota(20000);
        self::$managerQuotaRollup->user_id = self::$manager->id;
        self::$managerQuotaRollup->quota_type = "Rollup";
        self::$managerQuotaRollup->timeperiod_id = self::$timeperiod->id;
        self::$managerQuotaRollup->save();

        self::$repQuota = SugarTestQuotaUtilities::createQuota(1500);
        self::$repQuota->user_id = self::$reportee->id;
        self::$repQuota->quota_type = "Direct";
        self::$repQuota->timeperiod_id = self::$timeperiod->id;
        self::$repQuota->save();

        //setup forecasts
        self::$managerForecast = new Forecast();
        self::$managerForecast->user_id = self::$manager->id;
        self::$managerForecast->best_case = 1500;
        self::$managerForecast->likely_case = 1200;
        self::$managerForecast->worst_case = 900;
        self::$managerForecast->timeperiod_id = self::$timeperiod->id;
        self::$managerForecast->forecast_type = "Direct";
        self::$managerForecast->save();

        self::$repForecast = new Forecast();
        self::$repForecast->user_id = self::$reportee->id;
        self::$repForecast->best_case = 1100;
        self::$repForecast->likely_case = 900;
        self::$repForecast->worst_case = 700;
        self::$repForecast->timeperiod_id = self::$timeperiod->id;
        self::$repForecast->forecast_type = "Direct";
        self::$repForecast->save();

        //setup worksheets
        self::$managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        self::$managerWorksheet->user_id = self::$manager->id;
        self::$managerWorksheet->related_id = self::$manager->id;
        self::$managerWorksheet->forecast_type = "Rollup";
        self::$managerWorksheet->related_forecast_type = "Direct";
        self::$managerWorksheet->timeperiod_id = self::$timeperiod->id;
        self::$managerWorksheet->best_case = 1550;
        self::$managerWorksheet->likely_case = 1250;
        self::$managerWorksheet->worst_case = 950;
        self::$managerWorksheet->forecast = 1;
        self::$managerWorksheet->save();
        
        self::$managerWorksheetRep = SugarTestWorksheetUtilities::createWorksheet();
        self::$managerWorksheetRep->user_id = self::$manager->id;
        self::$managerWorksheetRep->related_id = self::$reportee->id;
        self::$managerWorksheetRep->forecast_type = "Rollup";
        self::$managerWorksheetRep->related_forecast_type = "Direct";
        self::$managerWorksheetRep->timeperiod_id = self::$timeperiod->id;
        self::$managerWorksheetRep->best_case = 1550;
        self::$managerWorksheetRep->likely_case = 1250;
        self::$managerWorksheetRep->worst_case = 950;
        self::$managerWorksheetRep->forecast = 1;
        self::$managerWorksheetRep->save();

        self::$repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
        self::$repWorksheet->user_id = self::$reportee->id;
        self::$repWorksheet->related_id = self::$repOpp->id;
        self::$repWorksheet->forecast_type = "Direct";
        self::$repWorksheet->related_forecast_type = "";
        self::$repWorksheet->timeperiod_id = self::$timeperiod->id;
        self::$repWorksheet->best_case = 1150;
        self::$repWorksheet->likely_case = 950;
        self::$repWorksheet->worst_case = 750;
        self::$repWorksheet->forecast = 1;
        self::$repWorksheet->save();
    	
    	parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass(){
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        $GLOBALS['db']->query("DELETE FROM forecasts WHERE id IN ('" . self::$managerForecast->id . "','" . self::$repForecast->id . "')");
        $GLOBALS['db']->query("DELETE FROM timeperiods WHERE id ='" . self::$timeperiod->id . "';");
    	parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager;
        $GLOBALS['current_user'] = $this->_user;

    }
    
	public function tearDown(){}
	
    /***
     * @group forecastapi
     */
    public function testForecastWorksheetsManager()
    {
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=". self::$manager->id . "&timeperiod_id=" . self::$timeperiod->id);
     	$this->assertNotEmpty($response["reply"], "Rest reply is empty. Manager data should have been returned. ");        
    }
    
    /***
     * @group forecastapi
     */
    public function testForecastWorksheetsManager_NonManager()
    {
        $response = $this->_restCall("ForecastManagerWorksheets?user_id=" . self::$reportee->id . "&timeperiod_id=" . self::$timeperiod->id);
        $this->assertEmpty($response["reply"], "Rest reply should be empty. Non-manager ID used.");        
    }
    
    /***
     * @group forecastapi
     */
    public function testForecastWorksheets()
    {
        $response = $this->_restCall("ForecastWorksheets?user_id=" . self::$reportee->id . "&timeperiod_id=" . self::$timeperiod->id);
        $this->assertNotEmpty($response["reply"], "Rest reply is empty. Rep data should have been returned.");        
    }
    
    /**
     * @group forecastapi
     */
    public function testForecastWorksheetManagerSave(){
    	self::$managerQuota->amount = self::$managerQuota->amount + 100;
    	self::$managerWorksheet->best_case = self::$managerWorksheet->best_case + 100;
    	$postData = array("amount" => self::$managerOpp->amount,
                             "quota" => self::$managerQuota->amount,
                             "quota_id" => self::$managerQuota->id,
                             "best_case" => self::$managerForecast->best_case,
                             "likely_case" => self::$managerForecast->likely_case,
                             "worst_case" => self::$managerForecast->worst_case,
                             "best_adjusted" => self::$managerWorksheet->best_case,
                             "likely_adjusted" => self::$managerWorksheet->likely_case,
                             "worst_adjusted" => self::$managerWorksheet->worst_case,
                             "forecast" => intval(self::$managerWorksheet->forecast),
                             "forecast_id" => self::$managerForecast->id,
                             "id" => self::$managerForecast->id,
                             "worksheet_id" => self::$managerWorksheet->id,
                             "show_opps" => false,
                             "name" => self::$manager->first_name . ' ' . self::$manager->last_name,
                             "user_id" => self::$manager->id,
                             "current_user" => self::$manager->id,
                             "timeperiod_id" => self::$timeperiod->id
                        );
       
		$response = $this->_restCall("ForecastManagerWorksheets/" . self::$managerForecast->id, json_encode($postData), "PUT");
						
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastManagerWorksheets?user_id=". self::$manager->id . "&timeperiod_id=" . self::$timeperiod->id);
		
		//check to see if the data to the Quota table was saved
		$this->assertEquals($response["reply"][0]["quota"], self::$managerQuota->amount, "Quota data was not saved.");
		
		//check to see if the data to the Worksheet table was saved
		$this->assertEquals($response["reply"][0]["best_adjusted"], self::$managerWorksheet->best_case, "Worksheet data was not saved.");
				
    }
    
    /**
     * @group forecastapi
     */
    public function testForecastWorksheetSave(){
    	
    	self::$repWorksheet->best_case = self::$repWorksheet->best_case + 100;
    	self::$repOpp->probability = self::$repOpp->probability + 10;
    	self::$repOpp->commit_stage = self::$repOpp->probability - 10;
    	
    	$postData = array("amount" => self::$repOpp->amount,
                             "best_case" => self::$repWorksheet->best_case,
                             "likely_case" => self::$repWorksheet->likely_case,                            
                             "forecast" => intval(self::$repWorksheet->forecast),
                             "id" => self::$repOpp->id,
                             "worksheet_id" => self::$repWorksheet->id,
                             "probability" => self::$repOpp->probability,                             
                             "commit_stage" => self::$repOpp->commit_stage,
                             "timeperiod_id" => self::$timeperiod->id
                        );
       
		$response = $this->_restCall("ForecastWorksheets/" . self::$repOpp->id, json_encode($postData), "PUT");
				
		// now get the data back to see if it was saved to all the proper tables.
		$response = $this->_restCall("ForecastWorksheets?user_id=". self::$reportee->id . "&timeperiod_id=" . self::$timeperiod->id);
		
		//check to see if the data to the Opportunity table was saved
		$this->assertEquals($response["reply"][0]["probability"], self::$repOpp->probability, "Opportunity data was not saved.");
		
		//check to see if the data to the Opportunity table was saved
		$this->assertEquals($response["reply"][0]["commit_stage"], self::$repOpp->commit_stage, "Opportunity data (commit_stage) was not saved.");

		//check to see if the data to the Worksheet table was saved
		$this->assertEquals($response["reply"][0]["best_case"], self::$repWorksheet->best_case, "Worksheet data was not saved.");
				
    }
    
    /**
     * @group forecastapi
     */
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
     }
     
     /**
     * @group forecastapi
     */
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
     }
}