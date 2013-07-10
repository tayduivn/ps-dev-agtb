<?php
// FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('include/SugarForecasting/Chart/Manager.php');
class SugarForecasting_Chart_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarForecasting_Chart_Manager
     */
    protected $obj;

    public function setUp()
    {
        $this->obj = $this->getMock(
            'SugarForecasting_Chart_Manager',
            array('getForecastConfig', 'getTimeperiod', 'getRollupQuota', 'getModuleLanguage'),
            array(array())
        );

        $this->obj->expects($this->atLeastOnce())
            ->method('getForecastConfig')
            ->will(
                $this->returnValue(
                    array(
                        'show_worksheet_worst' => 0,
                        'show_worksheet_best' => 1,
                        'show_worksheet_likely' => 1
                    )
                )
            );

        $tp_mock = $this->getMock('TimePeriod', array('save'));
        $tp_mock->name = 'Q2 2012';

        $this->obj->expects($this->atLeastOnce())
            ->method('getTimeperiod')
            ->will($this->returnValue($tp_mock));

        $this->obj->expects($this->atLeastOnce())
            ->method('getModuleLanguage')
            ->will(
                $this->returnValue(
                    array(
                        'LBL_CHART_FORECAST_FOR' => 'Test {0}'
                    )
                )
            );

        $this->obj->expects($this->atLeastOnce())
            ->method('getRollupQuota')
            ->will($this->returnValue(50.00));


        $data_array = array(
            array(
                'id' => 'wkst_test_1',
                'user_id' => 'test_1',
                'name' => 'Test 1',
                'best_case' => 50.00,
                'best_case_adjusted' => 60.00,
                'likely_case' => 40.00,
                'likely_case_adjusted' => 50.00,
                'worst_case' => 30.00,
                'worst_case_adjusted' => 40.00,
                'base_rate' => 1,
                'currency_id' => '-99'
            ),
            array(
                'id' => 'wkst_test_2',
                'user_id' => 'test_2',
                'name' => 'Test 2',
                'best_case' => 55.00,
                'best_case_adjusted' => 65.00,
                'likely_case' => 45.00,
                'likely_case_adjusted' => 55.00,
                'worst_case' => 35.00,
                'worst_case_adjusted' => 45.00,
                'base_rate' => 1,
                'currency_id' => '-99'
            ),
            array(
                'id' => 'wkst_test_3',
                'user_id' => 'test_3',
                'name' => 'Test 3',
                'best_case' => 57.00,
                'best_case_adjusted' => 67.00,
                'likely_case' => 47.00,
                'likely_case_adjusted' => 57.00,
                'worst_case' => 37.00,
                'worst_case_adjusted' => 47.00,
                'base_rate' => 1,
                'currency_id' => '-99'
            ),
            array(
                'id' => '',
                'user_id' => 'test_4',
                'name' => 'Test 4',
                'best_case' => 0,
                'best_case_adjusted' => 0,
                'likely_case' => 0,
                'likely_case_adjusted' => 0,
                'worst_case' => 0,
                'worst_case_adjusted' => 0,
                'base_rate' => 1,
                'currency_id' => '-99'
            ),
        );

        // set the data
        SugarTestReflection::setProtectedValue($this->obj, 'dataArray', $data_array);
    }

    public function testDataContainsAllUsers()
    {
        $data = $this->obj->process();
        $this->assertEquals(4, count($data['data']));
    }

    public function testNameIsSet()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['title']);
        $this->assertEquals('Test Q2 2012', $data['title']);
    }

    public function testQuotaIsSet()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['quota']);
        $this->assertEquals(50.00, $data['quota']);
    }

    public function testWorstNotInData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['data']);
        $this->assertNotContains('worst', array_keys($data['data'][0]));
        $this->assertNotContains('worst_adjusted', array_keys($data['data'][0]));
    }

    public function testBestInData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['data']);
        $this->assertContains('best', array_keys($data['data'][0]));
        $this->assertContains('best_adjusted', array_keys($data['data'][0]));
    }
}
