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

    /***
     * @group forecastapi
     */
    public function testForecastsCommittedApi()
    {
        require_once('modules/TimePeriods/TimePeriod.php');
        require_once('modules/Forecasts/Common.php');

        $timeperiods = TimePeriod::get_timeperiods_dom();
        $comm = new Common();
        $commit_order=$comm->get_forecast_commit_order();

        foreach ($timeperiods as $timeperiod_id=>$start_date)
        {
        	foreach($commit_order as $commit_type_array)
            {
        		//create forecast schedule for this timeperiod record and user.
        		//create forecast schedule using this record becuse there will be one
        		//direct entry per user, and some user will have a Rollup entry too.
        		if ($commit_type_array[1] == 'Direct')
                {
        			$fcst_schedule = new ForecastSchedule();
        			$fcst_schedule->timeperiod_id=$timeperiod_id;
        			$fcst_schedule->user_id=$commit_type_array[0];
        			$fcst_schedule->cascade_hierarchy=0;
        			$fcst_schedule->forecast_start_date=$start_date;
        			$fcst_schedule->status='Active';
        			$fcst_schedule->save();
        		}
        	}
        }
    }

    public function testForecastsCommitted()
    {
        $response = $this->_restCall("Forecasts/committed");
        $this->assertNotEmpty($response["reply"], "Rest reply is empty. Default manager data should have been returned.");
    }
}