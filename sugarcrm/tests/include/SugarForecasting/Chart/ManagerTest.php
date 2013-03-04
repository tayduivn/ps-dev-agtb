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
require_once('include/SugarForecasting/Chart/Manager.php');
class SugarForecasting_Chart_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected static $args = array();

    protected static $users = array();

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('manager', 'Forecasts'));
        SugarTestHelper::setup('current_user');

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$users['manager'] = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            //'createQuota' => false
        ));

        global $current_user;
        $current_user = self::$users['manager']['user'];
        $current_user->setPreference('currency', self::$currency->id);

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            //'createQuota' => false,
            'user' =>
            array('manager', 'reports_to' => self::$users['manager']['user']->id)
        );
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testQuotaConvertedToBase()
    {
        $obj = new SugarForecasting_Chart_Manager(self::$args);
        $data = $obj->process();

        // get the quota from the first record
        $actual = doubleval($data['values'][0]['goalmarkervalue'][0]);
        $expected = self::$users['manager']['quota']->amount + self::$users['reportee']['quota']->amount;

        $expected = SugarCurrency::convertAmountToBase($expected, self::$currency->id);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testQuotaLabelContainsBaseCurrencySymbol()
    {
        $obj = new SugarForecasting_Chart_Manager(self::$args);
        $data = $obj->process();

        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertStringStartsWith($base_currency->symbol, $data['values'][0]['goalmarkervaluelabel'][0]);
    }

    /**
     * @dataProvider dataProviderDatasets
     * @group forecasts
     * @group forecastschart
     */
    public function testChartValuesConvertedToBase($user, $type, $dataset, $position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;

        $obj = new SugarForecasting_Chart_Manager($args);
        $data = $obj->process();
        // get the proper DataSet
        $testData = array();
        foreach($data['values'] as $data_value) {
            if(strpos($data_value['label'], self::$users[$user]['user']->name) !== false) {
                $testData = $data_value;
                break;
            }
        }

        $field = $dataset . '_case';

        $actual = doubleval($testData['values'][$position]);
        $expected = SugarCurrency::convertAmountToBase(self::$users[$user][$type]->$field, self::$users[$user][$type]->currency_id);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @dataProvider dataProviderDatasets
     * @group forecasts
     * @group forecastschart
     */
    public function testChartValuesLabelsContainsBaseCurrencySymbol($user, $type, $dataset, $position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;

        $obj = new SugarForecasting_Chart_Manager($args);
        $data = $obj->process();

        // get the proper DataSet
        $testData = array();
        foreach($data['values'] as $data_value) {
            if(strpos($data_value['label'], self::$users[$user]['user']->name) !== false) {
                $testData = $data_value;
                break;
            }
        }

        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertStringStartsWith($base_currency->symbol, $testData['valuelabels'][$position]);
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testLoadUsersReturnsTwoUsersForCurrentUser()
    {
        $obj = new SugarForecasting_Chart_Manager(self::$args);
        $data = $obj->process();

        $this->assertEquals(2, count($data['values']));
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderParetoValues
     * @group forecastschart
     * @group forecasts
     *
     * @param $type
     * @param $dataset
     * @param $chart_position
     * @param $user_position
     */
    public function testChartParetoLinesConvertedToBase($type, $dataset, $chart_position, $user_position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;

        $obj = new SugarForecasting_Chart_Manager($args);
        $data = $obj->process();

        $data = $data['values'][$user_position];

        $field = $dataset . '_case';
        $expected = 0;
        if($user_position == 0) {
            // find the user in the current position
            foreach(self::$users as $user) {
                if(strpos($data['label'], $user['user']->name) !== false) {
                    $expected = $user[$type]-> $field;
                    break;
                }
            }
        } else {
            foreach(self::$users as $user) {
                $expected += $user[$type]->$field;
            }
        }

        $expected = SugarCurrency::convertAmountToBase($expected, self::$currency->id);
        $actual = doubleval($data['goalmarkervalue'][$chart_position+1]);

        $this->assertEquals($expected, $actual, null, 2);

    }

    /**
     * Test that the manager's label does not have the Opportunities () text surrounding the name
     *
     * @group forecasts
     * @group forecastschart
     */
    public function testTopLevelManagerUserDataOnlyContainsName()
    {
        global $locale;

        $obj = new SugarForecasting_Chart_Manager(self::$args);
        $data = $obj->process();
        $managerName = $locale->getLocaleFormattedName(self::$users['manager']['user']->first_name, self::$users['manager']['user']->last_name);
        $foundManager = false;
        foreach($data['values'] as $value)
        {
            if($value['label'] == $managerName)
            {
                $foundManager = true;
            }
        }
        $this->assertTrue($foundManager, 'Unable to find the manager name');
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderParetoValues
     * @group forecastschart
     * @group forecasts
     *
     * @param $type
     * @param $dataset
     * @param $chart_position
     * @param $user_position
     */
    public function testChartParetoLinesLabelsContainsBaseCurrencySymbol($type, $dataset, $chart_position, $user_position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;

        $obj = new SugarForecasting_Chart_Manager($args);
        $data = $obj->process();

        $data = $data['values'][$user_position];

        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertStringStartsWith($base_currency->symbol, $data['goalmarkervaluelabel'][$chart_position+1]);

    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        // keys are as follows
        // 1 -> what user data to use
        // 2 -> where do we get the data from
        // 3 -> dataset type
        // 4 -> position in value array
        return array(
            array('manager', 'worksheet', 'likely', 1),
            array('manager', 'worksheet', 'best', 1),
            array('manager', 'worksheet', 'worst', 1),
            array('manager', 'forecast', 'likely', 0),
            array('manager', 'forecast', 'best', 0),
            array('manager', 'forecast', 'worst', 0),
            array('reportee', 'worksheet', 'likely', 1),
            array('reportee', 'worksheet', 'best', 1),
            array('reportee', 'worksheet', 'worst', 1),
            array('reportee', 'forecast', 'likely', 0),
            array('reportee', 'forecast', 'best', 0),
            array('reportee', 'forecast', 'worst', 0)
        );
    }

    public function dataProviderParetoValues()
    {
        // keys are as follows
        // 1 -> where do we get the data from
        // 1 -> dataset type
        // 3 -> pareto line to check
        // 4 -> user position
        return array(
            array('forecast', 'likely', 0, 0),
            array('worksheet', 'likely', 1, 0),
            array('forecast', 'best', 0, 0),
            array('worksheet', 'best', 1, 0),
            array('forecast', 'worst', 0, 0),
            array('worksheet', 'worst', 1, 0),
            array('forecast', 'likely', 0, 1),
            array('worksheet', 'likely', 1, 1),
            array('forecast', 'best', 0, 1),
            array('worksheet', 'best', 1, 1),
            array('forecast', 'worst', 0, 1),
            array('worksheet', 'worst', 1, 1),

        );
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testTopLevelManagerQuotaEqualToWorksheetData()
    {
        $obj = new SugarForecasting_Chart_Manager(self::$args);
        $data = $obj->process();

        // get the totals;

        // get the quota from the first record
        $actual = doubleval($data['values'][0]['goalmarkervalue'][0]);
        $expected = self::$users['manager']['quota']->amount + self::$users['reportee']['quota']->amount;

        $expected = SugarCurrency::convertAmountToBase($expected, self::$currency->id);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @group forecasts
     * @group forecastschart
     * @outputBuffering disabled
     */
    public function testMidLevelManagerQuotaEqualToRollup()
    {
        $db = DBManagerFactory::getInstance();

        $reportee = SugarTestUserUtilities::createAnonymousUser(false);
        $reportee->reports_to_id = self::$users['reportee']['user']->id;
        $reportee->save();

        // add a rollup quota for the new reportee user
        /* @var $quota Quota */
        $result = $db->limitQuery(sprintf("SELECT id FROM quotas WHERE quota_type = 'Rollup' AND user_id = '%s' AND timeperiod_id = '%s' AND deleted = 0 ORDER BY date_modified DESC",
            self::$users['reportee']['user']->id,
            SugarTestForecastUtilities::getCreatedTimePeriod()->id),
            0,
            1
        );

        if(!empty($result)) {
            $row = $db->fetchByAssoc($result);
            $quota = BeanFactory::getBean('Quotas', $row['id']);
            $quota->amount = 1500;
            $quota->currency_id = '-99';
            $quota->quota_type = "Rollup";
            $quota->timeperiod_id = SugarTestForecastUtilities::getCreatedTimePeriod()->id;
            $quota->user_id = self::$users['reportee']['user']->id;
            $quota->save();
        } else {
            $quota = SugarTestQuotaUtilities::createQuota(1500);
            $quota->quota_type = "Rollup";
            $quota->timeperiod_id = SugarTestForecastUtilities::getCreatedTimePeriod()->id;
            $quota->user_id = self::$users['reportee']['user']->id;
            $quota->save();
        }

        $args = self::$args;
        $args['user_id'] = self::$users['reportee']['user']->id;

        $obj = new SugarForecasting_Chart_Manager($args);
        $data = $obj->process();

        // get the quota from the first record
        $actual = doubleval($data['values'][0]['goalmarkervalue'][0]);
        $expected = $quota->amount;

        $this->assertEquals($expected, $actual, null, 2);

        // this just un-sets the report_to for the reportee that was created in this test
        // this will get deleted when the test cleans up
        $reportee->reports_to_id = null;
        $reportee->save();
    }
}