<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


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
        $this->markTestIncomplete('SFA - Test breaks in test suite. Timeperiods are not being cleaned up correctly');
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities'));

        $this->assertEquals(self::$managerData['amount'], $return['properties']['total']);
    }

    public function testInvalidTypeReturnsCurrentUsersPipeline()
    {
        $this->markTestIncomplete('SFA - Test breaks in test suite. Timeperiods are not being cleaned up correctly');
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'invalid_type'));

        $this->assertEquals(self::$managerData['amount'], $return['properties']['total']);
    }

    public function testTypeOfTeamReturnsAllReproteesDataInPipeline()
    {
        $this->markTestIncomplete('SFA - Test breaks in test suite. Timeperiods are not being cleaned up correctly');
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'team'));

        $total = self::$manager['opportunities_total'] + self::$manager2['opportunities_total'] + self::$reportee['opportunities_total'];

        $this->assertEquals($total, $return['properties']['total']);
    }

    public function testManagerReporteeOnlyReturnsSelfPlusReporteeAndNotWholeTree()
    {
        $this->markTestIncomplete('SFA - Test breaks in test suite. Timeperiods are not being cleaned up correctly');
        $GLOBALS['current_user'] = self::$manager2['user'];
        $restService = SugarTestRestUtilities::getRestServiceMock(self::$manager2['user']);
        $return = $this->api->pipeline($restService, array('module' => 'Opportunities', 'type' => 'team'));

        $total = self::$manager2['opportunities_total'] + self::$reportee['opportunities_total'];

        $this->assertEquals($total, $return['properties']['total']);
    }
}
