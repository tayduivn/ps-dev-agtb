<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarForecasting/Progress/Manager.php');
require_once 'modules/Opportunities/Opportunity.php';

class SugarForecasting_Progress_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array args to be passed onto methods
     */
    protected static $args = array();

    /**
     * @var array array of users used throughout class
     */
    protected static $users = array();

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('manager', 'Forecasts'));
        SugarTestHelper::setup('current_user');
        
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$users['top_manager'] = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => array('amount' => 30000)
        ));

        self::$users['manager'] = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => array('amount' => 50000),
            'user' =>
            array('manager', 'reports_to' => self::$users['top_manager']['user']->id)
        ));

        global $current_user;

        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['manager']['user']->id;
        $current_user->setPreference('currency', self::$currency->id);

        $current_user = self::$users['manager']['user'];

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'user' =>
            array('manager', 'reports_to' => self::$users['manager']['user']->id),
            'quota' => array('amount' => 27000)
        );
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);

    }

    /**
     * reset after each test back to manager id, some tests may have changed to use top manager
     */
    public function setup() {
        self::$args['user_id'] = self::$users['manager']['user']->id;

        global $current_user;
        $current_user = self::$users['manager']['user'];
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = true;
        parent::tearDown();
    }

    /**
     * destroy some parts after each test
     */
    public function tearDown() {
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }
    
    /**
     * Utility function for calculating pipeline sums
     * 
     * @param obj Top level manager
     * @param obj users, passed in as individual arguments ($manager, $user1, $user2, ... $userN)
     */
    protected function calculatePipelineAmount($manager, $user)
    {
        $amount = 0;
        $users = array($user);
                
        $numargs = func_num_args();
        if ($numargs > 2) {
            for ($i = 2; $i < $numargs; $i++) {
                $users[] = func_get_arg($i);
            }
        }
        
    	foreach($manager["opportunities"] as $opp){
            if($opp->sales_stage != Opportunity::STAGE_CLOSED_WON || $opp->sales_stage != Opportunity::STAGE_CLOSED_LOST){
                $amount += $opp->amount;
            }
        }
                    
        foreach($users as $user){
        	$amount += ($user["forecast"]->pipeline_amount);
        }
        
        return $amount;
    }
    /**
     * Check for manager with no committed forecasts, but reps with committed forecasts
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerNoOpsRepsCommiteedOps()
    {                
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 0,
                'include_in_forecast' => 0
            )
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $reportee1, $reportee2);
            
        SugarTestForecastUtilities::createManagerRollupForecast($manager, $reportee1, $reportee2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
      
        //Make sure the pipeline count includes committed ops from reps
        $this->assertEquals("4", $data["opportunities"]);
        
        //Make sure that the pipeline revenue includes committed ops from reps
        $this->assertEquals($amount, $data["pipeline_revenue"]);    
    }
    
     /**
     * Check for manager with no committed forecasts, but reps with some committed forecasts and some not.
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerNoOpsRepsSomeCommiteedOps()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 0,
                'include_in_forecast' => 0
            )
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $reportee1, $reportee2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes committed ops, and excludes non committed ops from reps
        $this->assertEquals("2", $data["opportunities"]);
        
        //Make sure that the pipeline revenue includes committed ops from reps
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /**
     * Check for manager with committed forecasts and reps with committed forecasts
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerOpsRepsCommiteedOps()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            )
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        
        SugarTestForecastUtilities::createManagerRollupForecast($manager, $reportee1, $reportee2);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $reportee1, $reportee2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes committed ops from both manager and reps
        $this->assertEquals("6", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /**
     * Check for manager with some committed forecasts and reps with committed forecasts
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerSomeOpsRepsCommiteedOps()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        
        SugarTestForecastUtilities::createManagerRollupForecast($manager, $reportee1, $reportee2);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $reportee1, $reportee2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes all ops from manager and committed ops reps
        $this->assertEquals("6", $data["opportunities"]);
        
        //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /**
     * Check for manager with some committed forecasts and reps with some committed forecasts
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerSomeOpsRepsSomeCommiteedOps()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        
        SugarTestForecastUtilities::createManagerRollupForecast($manager, $reportee1, $reportee2);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $reportee1, $reportee2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes all manager ops and only committed rep ops
        $this->assertEquals("4", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /**
     * Check for a manager with submanagers with reps with committed opps.. make sure the cascade works.
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerWithSubManagerWithReps()
    {        
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager1["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager2["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
                
        $subManager1["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);        
        $manager["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $subManager1, $subManager2);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes all manager ops and only committed rep ops
        $this->assertEquals("6", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);
           
    }
    
     /**
     * Check for a manager with reps and submanagers with reps with committed opps.. make sure the cascade works.
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerWithSubManagerAndReps()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager1["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager2["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));        
        $reportee3 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        
        $subManager1["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);        
        $manager["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $subManager1, $subManager2, $reportee3);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes all manager ops and only committed rep ops
        $this->assertEquals("7", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /**
     * Check for a manager with reps and submanagers with reps with committed opps.. make sure the cascade works after
     * a simulated multisave (commiting multiple times)
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerWithSubManagerAndReps_multisave()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager1["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager2["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));        
        $reportee3 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        
        SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);        
        SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);
        
        //Simulate committing again (values don't matter, we want to make sure we don't sum them all together)
        $subManager1["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);        
        $manager["forecast"] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $subManager1, $subManager2, $reportee3);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        $data = $obj->process();
        
        //Make sure the pipeline count includes all manager ops and only committed rep ops
        $this->assertEquals("7", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
     /* Check for a manager with reps and submanagers with reps with committed opps.. make sure the cascade works after
     * a simulated multisave (commiting multiple times) and marking some as close won/lost
     * 
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerWithSubManagerAndReps_multisave_withCloseLostWon()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            )
        ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 1
            ),
        ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager1["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $subManager2["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));        
        $reportee3 = SugarTestForecastUtilities::createForecastUser(array(
            'user' => array(
                'reports_to' => $manager["user"]->id
            ),
            'opportunities' => array(
                'total' => 2,
                'include_in_forecast' => 2
            ),
        ));
                  
        //now we want to change the stage to close lost/close won of a few opps, commit, and make sure they are excluded
        $reportee3['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $reportee3['opportunities'][0]->save();
        $reportee2['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_LOST;
        $reportee2['opportunities'][0]->save();
        
        //sleep needed so that the new committed forecasts are clearly newer
        sleep(1);
        //recommit
        $reportee3['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee3);
        $reportee2['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee2);
        $subManager1['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);        
        $manager['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);
        
        //calculate what the amount should be
        $amount = 0;
        $amount += $this->calculatePipelineAmount($manager, $subManager1, $subManager2, $reportee3);
        
        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));
        
        $data = $obj->process();
        
        //Make sure the pipeline count includes all manager ops and only committed rep ops that aren't close won/lost
        $this->assertEquals("8", $data["opportunities"]);
        
         //Make sure that the pipeline revenue has something in it.
        $this->assertEquals($amount, $data["pipeline_revenue"]);     
    }
    
    /**
    * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        // keys are as follows
        // 1 -> quota amount
        // 2 -> quota currency id

        return array(
            array(false, false, 0, 0, -99),
            array(true, false, 15000, 0, -99),
            array(false, true, 0, 30000, -99),
            array(true, true, 15000, 30000, -99),
        );
    }

    /**
     * check top level manager quota to make sure it returns the expected sum of values for manager that doesn't report to anyone
     *
     * @dataProvider dataProviderDatasets
     * @group forecasts
     * @group forecastsprogress
     */
    public function testGetTopLevelManagerQuota($createManagerWorksheet, $createRepWorksheet, $managerQuotaAmount, $repQuotaAmount, $quotaCurrencyId)
    {
        global $current_user;
        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['top_manager']['user']->id;

        if($createManagerWorksheet) {
            //create worksheet data based on dataprovider
            $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $managerWorksheet->user_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->related_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $managerWorksheet->quota = $managerQuotaAmount;
            $managerWorksheet->currency_id = $quotaCurrencyId;
            $managerWorksheet->forecast_type = 'Rollup';
            $managerWorksheet->related_forecast_type = 'Direct';
            $managerWorksheet->version = 0;
            $managerWorksheet->save();
        }
        if($createRepWorksheet) {
            //create worksheet data based on dataprovider
            $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $repWorksheet->user_id = self::$users['top_manager']['user']->id;
            $repWorksheet->related_id = self::$users['manager']['user']->id;
            $repWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $repWorksheet->quota = $repQuotaAmount;
            $repWorksheet->currency_id = $quotaCurrencyId;
            $repWorksheet->forecast_type = 'Rollup';
            $repWorksheet->related_forecast_type = 'Rollup';
            $repWorksheet->version = 0;
            $repWorksheet->save();
        }
        $obj = new SugarForecasting_Progress_Manager(self::$args);
        $quotaAmount = $obj->getQuotaTotalFromData();

        $expectedQuotaAmount = 0;
        //determine how much quota should be
        foreach(SugarTestQuotaUtilities::getCreatedQuotaIds() as $quotaID) {
            $quota = BeanFactory::getBean('Quotas', $quotaID);
            if($quota->timeperiod_id == self::$args['timeperiod_id'])
            {
                if($quota->quota_type == "Direct" && $quota->user_id == self::$args['user_id'])
                {
                    //personal quota for the top level manager
                    //use worksheet quota number as it will cause an override in the function
                    $expectedQuotaAmount += ($createManagerWorksheet ? $managerQuotaAmount : $quota->amount);
                }
                if($quota->quota_type == "Rollup" && $quota->user_id == self::$users['manager']['user']->id)
                {
                    $expectedQuotaAmount += ($createRepWorksheet ? $repQuotaAmount : $quota->amount);
                }

            }
        }
        //compare expected and actual
        $this->assertEquals($expectedQuotaAmount, $quotaAmount, "Quota not matching expected amount.  Expected: ".$expectedQuotaAmount." Actual: ".$quotaAmount);
    }
}