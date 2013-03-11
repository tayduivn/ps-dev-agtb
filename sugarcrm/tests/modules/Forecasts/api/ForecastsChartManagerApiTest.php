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

require_once('include/api/RestService.php');
require_once('modules/Forecasts/clients/base/api/ForecastsChartApi.php');
require_once('modules/Forecasts/clients/base/api/ForecastManagerWorksheetsFilterApi.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsChartManagerApiTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $rep;

    /**
     * @var TimePeriod;
     */
    protected static $timeperiod;

    /**
     * @var chartApi
     */
    protected $chartApi;

    /**
     * Set-up the variables needed for this to run.
     *
     * @static
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');


        self::$manager = SugarTestForecastUtilities::createForecastUser();
        self::$rep = SugarTestForecastUtilities::createForecastUser(array('user' => array('reports_to' => self::$manager['user']->id)));

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        
    }

    /**
     * Clean up the class
     *
     * @static
     */
    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();

        parent::tearDownAfterClass();
        // this strange as we only want to call this when the class expires;
        parent::tearDown();
    }

    public function setUp()
    {
        $this->_user = self::$manager['user'];
        $this->chartApi = new ForecastManagerWorksheetsFilterApi();
    }

    /**
     * Ignore the teardown so we don't remove users that might be needed.
     */
    public function tearDown()
    {
        $this->chartApi = null;
    }

    /**
     * Utility Method to get the ServiceMock with a valid user in it
     *
     * @param User $user
     * @return ForecastChartApiServiceMock
     */
    protected function _getServiceMock(User $user)
    {
        $serviceApi = new ForecastChartManagerApiServiceMock();
        $serviceApi->user = $user;

        return $serviceApi;
    }

    /**
     * Utility Method to run the same command for each test.
     *
     * @param string $dataset           What data set we want to test for
     * @return mixed
     */
    protected function runRestCommand($dataset = 'likely')
    {
        $GLOBALS['current_user'] = self::$manager['user'];
        $args = array(
            'timeperiod_id' => self::$timeperiod->id,
            'user_id' => self::$manager['user']->id,
            'display_manager' => true,
            'group_by' => 'sales_stage',
            'dataset' => $dataset,
            'module' => 'ForecastManagerWorksheets'
        );

        return $this->chartApi->forecastManagerWorksheetsChartGet($this->_getServiceMock(self::$manager['user']), $args);
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testChartDataShouldContainTwoUsers()
    {
        $chart = $this->runRestCommand();
        $this->assertEquals(2, count($chart['values']));
    }

    public function dataProviderWorksheetValues()
    {
        // keys are as follows
        // 1 -> where do we get the data from
        // 2 -> dataset type
        // 3 -> dataset field name
        // 4 -> position in value array
        return array(
            array('worksheet', 'likely', '_case', 1),
            array('worksheet', 'best', '_case', 1),
            array('worksheet', 'worst', '_case', 1),
            array('forecast', 'likely', '_case', 0),
            array('forecast', 'best', '_case', 0),
            array('forecast', 'worst', '_case', 0)
        );
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @dataProvider dataProviderWorksheetValues
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testManagerValueIsCorrectValueFromWorksheet($type, $dataset, $field, $pos)
    {
        $data = $this->runRestCommand($dataset);
        $_field = $dataset . $field;

        // get the proper DataSet
        $testData = array();
        foreach($data['values'] as $data_value) {
            if(strpos($data_value['label'], self::$manager['user']->name) !== false) {
                $testData = $data_value;
                break;
            }
        }
        $this->assertEquals(self::$manager[$type]->$_field, $testData['values'][$pos]);
    }

    /**
     * @depends testChartDataShouldContainTwoUsers
     * @dataProvider dataProviderWorksheetValues
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testReporteeValueIsCorrectValueFromWorksheet($type, $dataset, $field, $pos)
    {
        $data = $this->runRestCommand($dataset);
        $_field = $dataset . $field;

        // get the proper DataSet
        $testData = array();
        foreach($data['values'] as $data_value) {
            if(strpos($data_value['label'], self::$rep['user']->name) !== false) {
                $testData = $data_value;
                break;
            }
        }
        $this->assertEquals(self::$rep[$type]->$_field, $testData['values'][$pos]);
    }

    /**
     * @group forecastapi
     * @group forecasts
     * @group forecastschart
     */
    public function testThirdReporteeValueZeroWithoutForecastRecord()
    {

        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language('en_us');
        $rep = SugarTestForecastUtilities::createForecastUser(array('createOpportunities' => false,'user' => array('reports_to' => self::$manager['user']->id)));

        $repOpp = SugarTestOpportunityUtilities::createOpportunity();
        $repOpp->assigned_user_id = self::$manager['user']->id;
        $repOpp->timeperiod_id = self::$timeperiod->id;
        $repOpp->amount = 1800;
        $repOpp->likely_case = 1700;
        $repOpp->best_case = 1900;
        $repOpp->probability = '85';
        $repOpp->date_closed = '2012-01-30';
        $repOpp->team_id = '1';
        $repOpp->team_set_id = '1';
        $repOpp->save();

        //setup quotas
        $repQuota = SugarTestQuotaUtilities::createQuota(2000);
        $repQuota->user_id = self::$manager['user']->id;
        $repQuota->quota_type = "Direct";
        $repQuota->timeperiod_id = self::$timeperiod->id;
        $repQuota->team_set_id = 1;
        $repQuota->save();

        $data = $this->runRestCommand();
        $this->assertEquals(0, $data['values'][2]['values'][0]);
    }
}

class ForecastChartManagerApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}
