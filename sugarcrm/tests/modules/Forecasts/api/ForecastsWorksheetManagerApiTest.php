<?php
//FILE SUGARCRM flav=pro ONLY

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


require_once("modules/Forecasts/clients/base/api/ForecastManagerWorksheetsFilterApi.php");
require_once('include/api/RestService.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 */
class ForecastsWorksheetManagerApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    private static $reportee2;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

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
    protected static $managerData2;

    /**
     * @var array
     */
    protected static $repData;

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var ForecastManagerWorksheetsFilterApi
     */
    protected $filterApi;

    /**
     * @var ForecastsWorksheetApi
     */
    protected $putApi;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$manager = SugarTestForecastUtilities::createForecastUser(
            array(
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                ),
            )
        );

        //set up another manager, and assign him to the first manager manually so his data is generated
        //correctly.
        self::$manager2 = SugarTestForecastUtilities::createForecastUser(
            array(
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                ),
            )
        );

        self::$manager2["user"]->reports_to_id = self::$manager['user']->id;
        self::$manager2["user"]->save();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(
            array(
                'user' => array(
                    'reports_to' => self::$manager['user']->id
                ),
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                )
            )
        );
        self::$reportee2 = SugarTestForecastUtilities::createForecastUser(
            array(
                'user' => array(
                    'reports_to' => self::$manager2['user']->id
                ),
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                )
            )
        );

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = array(
            "amount" => self::$manager['opportunities_total'],
            "quota" => self::$manager['quota']->amount,
            "quota_id" => self::$manager['quota']->id,
            "best_case" => self::$manager['forecast']->best_case,
            "likely_case" => self::$manager['forecast']->likely_case,
            "worst_case" => self::$manager['forecast']->worst_case,
            "best_adjusted" => self::$manager['worksheet']->best_case,
            "likely_adjusted" => self::$manager['worksheet']->likely_case,
            "worst_adjusted" => self::$manager['worksheet']->worst_case,
            "commit_stage" => self::$manager['worksheet']->commit_stage,
            "forecast_id" => self::$manager['forecast']->id,
            "worksheet_id" => self::$manager['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager['user']->id,
            "name" => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            "user_id" => self::$manager['user']->id,

        );

        self::$managerData2 = array(
            "amount" => self::$manager2['opportunities_total'],
            "quota" => self::$manager2['quota']->amount,
            "quota_id" => self::$manager2['quota']->id,
            "best_case" => self::$manager2['forecast']->best_case,
            "likely_case" => self::$manager2['forecast']->likely_case,
            "worst_case" => self::$manager2['forecast']->worst_case,
            "best_adjusted" => self::$manager2['worksheet']->best_case,
            "likely_adjusted" => self::$manager2['worksheet']->likely_case,
            "worst_adjusted" => self::$manager2['worksheet']->worst_case,
            "commit_stage" => self::$manager2['worksheet']->commit_stage,
            "forecast_id" => self::$manager2['forecast']->id,
            "worksheet_id" => self::$manager2['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager2['user']->id,
            "name" => 'Opportunities (' . self::$manager2['user']->first_name . ' ' . self::$manager2['user']->last_name . ')',
            "user_id" => self::$manager2['user']->id,

        );

        self::$repData = array(
            "amount" => self::$reportee['opportunities_total'],
            "quota" => self::$reportee['quota']->amount,
            "quota_id" => self::$reportee['quota']->id,
            "best_case" => self::$reportee['forecast']->best_case,
            "likely_case" => self::$reportee['forecast']->likely_case,
            "worst_case" => self::$reportee['forecast']->worst_case,
            "best_adjusted" => self::$reportee['worksheet']->best_case,
            "likely_adjusted" => self::$reportee['worksheet']->likely_case,
            "worst_adjusted" => self::$reportee['worksheet']->worst_case,
            "commit_stage" => self::$reportee['worksheet']->commit_stage,
            "forecast_id" => self::$reportee['forecast']->id,
            "worksheet_id" => self::$reportee['worksheet']->id,
            "show_opps" => true,
            "id" => self::$reportee['user']->id,
            "name" => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            "user_id" => self::$reportee['user']->id,

        );

        // get current settings
        self::$admin = BeanFactory::getBean('Administration');
    }

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager['user'];
        $this->_oldUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->_user;
        //Reset all columns to be shown
        self::$admin->saveSetting('Forecasts', 'show_worksheet_likely', 1, 'base');
        self::$admin->saveSetting('Forecasts', 'show_worksheet_best', 1, 'base');
        self::$admin->saveSetting('Forecasts', 'show_worksheet_worst', 1, 'base');

        $this->filterApi = new ForecastManagerWorksheetsFilterApi();
        //$this->putApi = new ForecastsWorksheetApi();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        parent::tearDown();
    }

    public function tearDown()
    {
        $this->filterApi = null;
        $GLOBALS["current_user"] = null;
        // override since we want to do this after the class is done
    }

    /**
     * Utility Method to get the ServiceMock with a valid user in it
     *
     * @param User $user
     * @return ForecastManagerWorksheetApiServiceMock
     */
    protected function _getServiceMock(User $user)
    {
        $serviceApi = new ForecastManagerWorksheetApiServiceMock();
        $serviceApi->user = $user;

        return $serviceApi;
    }


    /**
     * This test asserts that we get back data.
     *
     * @group forecastapi
     * @group forecasts
     */
    public function testPassedInUserIsManager()
    {
        $GLOBALS["current_user"] = self::$manager["user"];

        $response = $this->filterApi->forecastManagerWorksheetsGet(
            $this->_getServiceMock(self::$manager['user']),
            array(
                'user_id' => self::$manager['user']->id,
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );

        $this->assertNotEmpty($response["records"], "Rest reply is empty. User Is Not A Manager.");
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testPassedInUserIsNotManagerReturnsEmpty()
    {
        $GLOBALS["current_user"] = self::$reportee["user"];

        $this->filterApi->forecastManagerWorksheetsGet(
            $this->_getServiceMock(self::$reportee['user']),
            array(
                'user_id' => self::$reportee['user']->id,
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testCurrentUserIsNotManagerReturnsEmpty()
    {
        $GLOBALS['current_user'] = self::$reportee['user'];

        $this->filterApi->forecastManagerWorksheetsGet(
            $this->_getServiceMock(self::$reportee['user']),
            array(
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );
    }
}

class ForecastManagerWorksheetApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}