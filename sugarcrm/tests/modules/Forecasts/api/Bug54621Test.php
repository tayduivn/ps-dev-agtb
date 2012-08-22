<?php
//FILE SUGARCRM flav=ent ONLY

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

require_once('modules/Forecasts/api/ForecastsWorksheetManagerApi.php');
/**
 * Bug54621Test.php
 *
 * This is a test for the ForecastWorksheetManagerApi class.  We are testing that the getForecastValues method
 * returns the most recent forecast entry.  There is a bug with some MySQL implementation where the max(datetime)
 * function does not correctly return the most recent datetime and so we could not use that function in our code.
 *
 */
class Bug54621Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $reportee;
    private $forecast2;
    private $timeperiod;

    public function setUp()
    {
        global $beanFiles, $beanList, $current_user, $app_list_strings, $app_strings, $timedate;
        $timedate = TimeDate::getInstance();
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->is_admin = 1;
        $current_user->save();

        $this->reportee = SugarTestUserUtilities::createAnonymousUser();
        $this->reportee->reports_to_id = $current_user->id;
        $this->reportee->user_name = 'employee1';
        $this->reportee->save();

        $this->timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        //Create two forecasts
        $forecast1 = SugarTestForecastUtilities::createForecast($this->timeperiod, $this->reportee);
        $forecast1->best_case = 4321;
        $forecast1->likely_case = 4321;
        $forecast1->save();

        //This is a second entry
        $this->forecast2 = SugarTestForecastUtilities::createForecast($this->timeperiod, $this->reportee);
        $this->forecast2->best_case = 3241;
        $this->forecast2->likely_case = 3241;
        $this->forecast2->save();
        //Manually alter the date_modified value so that we get a value that is more recent to show up in our test
        $timedate = TimeDate::getInstance();
        $GLOBALS['db']->query("UPDATE forecasts SET date_modified = '" . $timedate->asDbDate($timedate->getNow()->modify("+3 months")) . "' WHERE id = '{$this->forecast2->id}'");
        $GLOBALS['db']->commit();

        //This is the most recently modified entry
        $this->forecast3 = SugarTestForecastUtilities::createForecast($this->timeperiod, $this->reportee);
        $this->forecast3->best_case = 1234;
        $this->forecast3->likely_case = 1234;
        $this->forecast3->save();

        //Manually alter the date_modified value so that we get a value that is more recent to show up in our test
        $timedate = TimeDate::getInstance();
        $GLOBALS['db']->query("UPDATE forecasts SET date_modified = '" . $timedate->asDbDate($timedate->getNow()->modify("+6 months")) . "' WHERE id = '{$this->forecast3->id}'");
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        parent::tearDownAfterClass();
    }

    /**
     * Here we call the subclass of ForecastWorksheetManagerApi.  Since the getForecastValues is protected we
     * override the function in our mock object.
     *
     */
    public function testReturnsMostRecentForecast()
    {
        global $current_user;
        $mock = new ForecastsWorksheetManagerApiMock();
        $data = $mock->getForecastValuesOverride($current_user->id, $this->timeperiod->id);

        $found = false;

        foreach($data as $user_name=>$entry)
        {
            if($entry['forecast_id'] == $this->forecast3->id)
            {
                $this->assertEquals(1234, $entry['best_case'], 'Failed asserting best_case is 1234');
                $this->assertEquals(1234, $entry['likely_case'], 'Failed asserting likely_case is 1234');
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Failed to find the created forecast record');
    }
}

/**
 * ForecastWorksheetManagerApiMock
 *
 * This is our mock class to override the ForecastsWorksheetManagerApi class
 */
class ForecastsWorksheetManagerApiMock extends ForecastsWorksheetManagerApi
{
    public function getForecastValuesOverride($user_id, $timeperiod_id)
    {
         $this->user_id = $user_id;
         $this->timeperiod_id = $timeperiod_id;
         return $this->getForecastValues();
    }
}