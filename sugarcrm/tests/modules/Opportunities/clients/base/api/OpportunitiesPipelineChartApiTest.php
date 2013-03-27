<?php
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


require_once("modules/Opportunities/clients/base/api/OpportunitiesPipelineChartApi.php");

class OpportunitiesPipelineChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $reportee;

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
     * @var Administration
     */
    protected static $admin;

    /**
     * @var OpportunitiesPipelineChartApi
     */
    protected $api;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 1');

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

        // get current settings
        self::$admin = BeanFactory::getBean('Administration');
    }

    public function setUp()
    {
        $this->api = new OpportunitiesPipelineChartApi();
        $GLOBALS['current_user'] = self::$manager['user'];
    }

    public static function tearDownAfterClass()
    {
        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 0 where deleted = 1');
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        parent::tearDownAfterClass();
    }

    public function tearDown()
    {
        $GLOBALS["current_user"] = null;
        $this->api = null;
    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testInvalidTimePeriodThrowsException()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $this->api->pipeline($restService, array('timeperiod_id' => 'invalid_tp', 'module' => 'Opportunities'));
    }

    public function testNoParamsReturnsCurrentUsersPipeLineData()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities'));

        $this->assertEquals(self::$managerData['amount'], $return['properties']['total']);
    }

    public function testInvalidTypeReturnsCurrentUsersPipeline()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'invalid_type'));

        $this->assertEquals(self::$managerData['amount'], $return['properties']['total']);
    }

    public function testTypeOfTeamReturnsAllReproteesDataInPipeline()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'team'));

        $total = self::$manager['opportunities_total'] + self::$manager2['opportunities_total'] + self::$reportee['opportunities_total'];

        $this->assertEquals($total, $return['properties']['total']);
    }

    public function testManagerReporteeOnlyReturnsSelfPlusReporteeAndNotWholeTree()
    {
        $GLOBALS['current_user'] = self::$manager2['user'];
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager2['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'team'));

        $total = self::$manager2['opportunities_total'] + self::$reportee['opportunities_total'];

        $this->assertEquals($total, $return['properties']['total']);
    }
}