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

require_once("modules/Forecasts/clients/base/api/ForecastsFilterApi.php");
require_once('include/api/RestService.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsCommittedApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private static $reportee;

    /**
     * @var User
     */
    protected static $manager;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var ForecastsFilterApi
     */
    protected $api;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$manager = SugarTestForecastUtilities::createForecastUser();
  
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager['user']->id;
        self::$reportee->save();

    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $this->api = new ForecastsFilterApi();
    }

    public function tearDown()
    {
        unset($this->api);
    }

    protected function _getMockApi($class_name)
    {
        return $this->getMock($class_name);
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilter()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $response = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id)
        );

        $this->assertNotEmpty($response["records"], "Rest reply is empty. Rep data should have been returned.");
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAManagerTryingToViewAnotherUser()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'user_id' => self::$manager['user']->id)
        );

    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowExceptionWhenRepViewingHisOwnSheet()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'user_id' => self::$reportee->id)
        );

        $this->assertInternalType('array', $return);
    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidUserId()
    {
        $GLOBALS["current_user"] = self::$manager['user'];

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('module' => 'Forecasts', 'user_id' => 'im_not_valid')
        );

    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidTimeperiodId()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => 'im_not_valid')
        );

    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidForecastType()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $stub = $this->_getMockApi('ForecastsFilterApi');
        $stub->expects($this->any())
             ->method('filterList')
             ->will($this->returnValue(
                    array("next_offset" => -1,"records" => array())
                ));


        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => 'invalid_type')
        );
    }

    /**
     * @dataProvider forecastTypesDataProvider
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowsAnExceptionWithAValidForecastType($forecast_type)
    {
        $GLOBALS["current_user"] = self::$reportee;

        $stub = $this->_getMockApi('ForecastsFilterApi');
        $stub->expects($this->any())
             ->method('filterList')
             ->will($this->returnValue(
                    array("next_offset" => -1,"records" => array())
                ));


        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => $forecast_type)
        );

        $this->assertSame(array("next_offset" => -1,"records" => array()), $return);
    }

    public static function forecastTypesDataProvider()
    {
        return array(
            array('direct'),
            array('Direct'),
            array('rollup'),
            array('Rollup')
        );
    }

}

class ForecastFilterApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}