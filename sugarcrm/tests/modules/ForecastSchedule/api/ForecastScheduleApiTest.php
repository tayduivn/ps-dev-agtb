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
 * This test class is used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecasts
 */
class ForecastScheduleApiTest extends RestTestBase
{
	/**
	 * @var object Manager user
	 */
	protected static $manager;
	
	/**
	 * @var object Reportee user
	 */
	protected static $reportee;
	
	/**
	 * @var string Timeperiod ID
	 */
	protected static $timeperiod;

	/**
	 * @var Array ForecastSchedule instances;
	 */
    protected static $forecastSchedule1;

    /**
   	 * @var Array ForecastSchedule instances;
   	 */
    protected static $forecastSchedule2;


    /**
     * @static
     * @outputBuffering disabled
     */
    public static function setUpBeforeClass()
    {
    	self::$manager = SugarTestUserUtilities::createAnonymousUser();
    	self::$manager->save();

        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager->id;
        self::$reportee->save();
        
        //create Timeperiod
        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

    	//create ForecastSchedule
        self::$forecastSchedule1 = SugarTestForecastScheduleUtilities::createForecastSchedule(self::$timeperiod, self::$manager);
        self::$forecastSchedule2 = SugarTestForecastScheduleUtilities::createForecastSchedule(self::$timeperiod, self::$reportee);

    	parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass(){
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastScheduleUtilities::removeAllCreatedForecastSchedules();
    	parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager;
        $GLOBALS['current_user'] = $this->_user;

    }

    /**
     * This method is to test the retrieval function from the /ForecastSchedule REST endpoint
     * @outputBuffering disabled
     */
    public function testForecastSchedule()
    {
        global $current_user;
        //Call /ForecastSchedule with a timeperiod_id, but without a user_id
		$response = $this->_restCall("ForecastSchedule?timeperiod_id=" . self::$timeperiod->id);
        $schedule = $response['reply']['records'][0];
        $this->assertEquals(self::$forecastSchedule1->id, $schedule['id'], 'Assert we have found the ForecastSchedule entry for the manager user');

        //Call /ForecastSchedule with a timeperiod_id and with manager's id
        $response = $this->_restCall('ForecastSchedule?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$manager->id);
        $schedule = $response['reply']['records'][0];
        $this->assertEquals(self::$forecastSchedule1->id, $schedule['id'], 'Assert we have found the ForecastSchedule entry for the manager user with his id');

        //Call /ForecastSchedule with a timeperiod_id and with employee's id
        $response = $this->_restCall('ForecastSchedule?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$reportee->id);
        $schedule = $response['reply']['records'][0];
        $this->assertEquals(self::$forecastSchedule2->id, $schedule['id'], 'Assert we have found the ForecastSchedule entry for the reportee user with his id');
    }
    

    /**
     * This method is to test the save function from the /ForecastSchedule REST endpoint to update an entry
     * @outputBuffering disabled
     */
    public function testForecastScheduleSave()
    {
        global $current_user;
        //Call /ForecastSchedule with a timeperiod_id, but without a user_id
		$response = $this->_restCall("ForecastSchedule?timeperiod_id=" . self::$forecastSchedule1->timeperiod_id);
        $schedule = $response['reply']['records'][0];
        $this->assertEquals(self::$forecastSchedule1->id, $schedule['id'], 'Assert we have found the ForecastSchedule entry for the manager user');

        $post = array('expected_best_case' => 123,
                      'expected_likely_case' => 122,
                      'id' => self::$forecastSchedule1->id);

        //Update the ForecastSchedule instance
        $saved = false;

        try {
            $this->_restCall('ForecastSchedule/' . self::$forecastSchedule1->id, json_encode($post), 'PUT');

            //Call commit to ensure values are saved
            $GLOBALS['db']->commit();
            $saved = true;
        } catch (Exception $ex) {
            $saved = false;
        }

        $this->assertTrue($saved);

        //Re-visit this section later as OAUTH issues are coming into play here
        //Call /ForecastSchedule with a timeperiod_id and with manager's id
        //$response = $this->_restCall('ForecastSchedule?timeperiod_id=' . self::$timeperiod->id . '&user_id=' . self::$manager->id);
        //$schedule = $response['reply']['records'][0];
        //$this->assertEquals(self::$forecastSchedule1->id, $schedule['id'], 'Assert we have found the ForecastSchedule entry for the manager user with his id');
        //$this->assertEquals('123', $schedule['expected_best_case'], 'Assert we have updated the expected_best_case');
        //$this->assertEquals('122', $schedule['expected_likely_case'], 'Assert we have updated the expected_likely_case');
    }
}