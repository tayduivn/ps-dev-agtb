<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'modules/Forecasts/clients/base/api/ForecastsModuleApi.php';

class ForecastsModuleApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ForecastsModuleApi
     */
    protected $api;

    /**
     * @var ServiceBase
     */
    protected $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        // load up the unifiedSearchApi for good times ahead
        $this->api = new ForecastsModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for create record test
     *
     * @return array
     */
    public function createRecordDataProvider()
    {
        $timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();
        $expectedSalesRep = array(
            'timeperiod_id' => $timeperiod->id,
            'amount' => 0,
            'best_case' => 0,
            'worst_case' => 0,
            'overall_amount' => 0,
            'overall_best' => 0,
            'overall_worst' => 0,
            'lost_count' => 0,
            'lost_amount' => 0,
            'lost_best' => 0,
            'lost_worst' => 0,
            'won_count' => 0,
            'won_amount' => 0,
            'won_best' => 0,
            'won_worst' => 0,
            'included_opp_count' => 0,
            'total_opp_count' => 0,
            'includedClosedCount' => 0,
            'includedClosedAmount' => 0,
            'includedClosedBest' => 0,
            'includedClosedWorst' => 0,
            'pipeline_amount' => 0,
            'pipeline_opp_count' => 0,
            'likely_case' => 0,
            'closed_amount' => 0,
        );
        $expectedManager = array(
            'quota' => 0,
            'best_case' => 0,
            'best_adjusted' => 0,
            'likely_case' => 0,
            'likely_adjusted' => 0,
            'worst_case' => 0,
            'worst_adjusted' => 0,
            'included_opp_count' => 0,
            'pipeline_opp_count' => 0,
            'pipeline_amount' => 0,
            'closed_amount' => 0,
        );

        return array(
            array(
                array(
                    'forecast_type' => 'Direct',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedSalesRep
            ),
            array(
                array(
                    'forecast_type' => 'direct',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedSalesRep
            ),
            array(
                array(
                    'forecast_type' => 'Rollup',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedSalesRep
            ),
            array(
                array(
                    'forecast_type' => 'rollup',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedSalesRep
            ),
            array(
                array(
                    'forecast_type' => 'Direct',
                    'commit_type' => 'manager',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedManager
            ),
            array(
                array(
                    'forecast_type' => 'direct',
                    'commit_type' => 'manager',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedManager
            ),
            array(
                array(
                    'forecast_type' => 'Rollup',
                    'commit_type' => 'manager',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedManager
            ),
            array(
                array(
                    'forecast_type' => 'rollup',
                    'commit_type' => 'manager',
                    'timeperiod_id' => $timeperiod->id,
                ),
                $expectedManager
            ),
        );
    }

    /**
     * Test creating record.
     *
     * @dataProvider createRecordDataProvider
     */
    public function testCreateRecord($args, $expected)
    {
        $result = $this->api->createRecord($this->serviceMock, $args);

        $this->assertNotEmpty($result);

        foreach($expected as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $result);
            $this->assertEquals($expectedValue, $result[$expectedKey]);
        }
    }
}