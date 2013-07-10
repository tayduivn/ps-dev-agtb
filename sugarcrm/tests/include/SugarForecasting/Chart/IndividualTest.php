<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarForecasting/Chart/Individual.php');
class SugarForecasting_Chart_IndividualTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarForecasting_Chart_Individual
     */
    protected $obj;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }


    public function setUp()
    {
        $this->obj = $this->getMock(
            'SugarForecasting_Chart_Individual',
            array('getForecastConfig', 'getTimeperiod', 'getUserQuota', 'getModuleLanguage'),
            array(array())
        );

        $this->obj->expects($this->atLeastOnce())
            ->method('getForecastConfig')
            ->will(
                $this->returnValue(
                    array(
                        'show_worksheet_worst' => 0,
                        'show_worksheet_best' => 1,
                        'show_worksheet_likely' => 1,
                        'buckets_dom' => 'commit_stage_binary_dom'
                    )
                )
            );

        $tp_mock = $this->getMock('TimePeriod', array('save', 'getChartLabels'));
        $tp_mock->name = 'Q2 2012';

        $tp_mock->expects($this->atLeastOnce())
            ->method('getChartLabels')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'label' => 'x-axis 1'
                        ),
                        array(
                            'label' => 'x-axis 2'
                        ),
                        array(
                            'label' => 'x-axis 3'
                        )
                    )
                )
            );

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
            ->method('getUserQuota')
            ->will($this->returnValue(50.00));


        $data_array = array(
            array(
                'id' => 'wkst_test_1',
                'name' => 'Test 1',
                'best_case' => 50.00,
                'likely_case' => 40.00,
                'worst_case' => 30.00,
                'base_rate' => 1,
                'currency_id' => '-99',
                'commit_stage' => 'include',
                'sales_stage' => 'test_1',
                'probability' => 50,
                'date_closed_timestamp' => '10000'
            ),
            array(
                'id' => 'wkst_test_2',
                'user_id' => 'test_2',
                'name' => 'Test 2',
                'best_case' => 55.00,
                'likely_case' => 45.00,
                'worst_case' => 35.00,
                'base_rate' => 1,
                'currency_id' => '-99',
                'commit_stage' => 'include',
                'sales_stage' => 'test_1',
                'probability' => 50,
                'date_closed_timestamp' => '10000'
            ),
            array(
                'id' => 'wkst_test_3',
                'user_id' => 'test_3',
                'name' => 'Test 3',
                'best_case' => 57.00,
                'likely_case' => 47.00,
                'worst_case' => 37.00,
                'base_rate' => 1,
                'currency_id' => '-99',
                'commit_stage' => 'include',
                'sales_stage' => 'test_1',
                'probability' => 50,
                'date_closed_timestamp' => '10000'
            ),
            array(
                'id' => '',
                'user_id' => 'test_4',
                'name' => 'Test 4',
                'best_case' => 0,
                'likely_case' => 0,
                'worst_case' => 0,
                'base_rate' => 1,
                'currency_id' => '-99',
                'commit_stage' => 'include',
                'sales_stage' => 'test_1',
                'probability' => 50,
                'date_closed_timestamp' => '10000'
            ),
        );

        // set the data
        SugarTestReflection::setProtectedValue($this->obj, 'dataArray', $data_array);
    }

    public function testDataContainsAllData()
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
    }

    public function testBestInData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['data']);
        $this->assertContains('best', array_keys($data['data'][0]));
    }

    public function testXaxisHasData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['x-axis'], var_export($data, true));
    }
}
