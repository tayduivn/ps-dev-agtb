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
require_once("modules/Forecasts/clients/base/api/ForecastWorksheetsFilterApi.php");
class SugarForecasting_Chart_IndividualTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $args = array();

    /**
     * @var array
     */
    protected static $user;

    /*
     * @var Timeperiod
     */
    protected static $timeperiod;

    /**
     * @var String
     */
    protected static $configTimeperiodType;

    /**
     * @var String
     */
    protected static $configTimeperiodLeafType;

    /**
     * @var array
     */
    protected static $dataArray;

    /**
     * @var ForecastWorksheetsFilterApi
     */
    protected static $filterApi;

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('Forecasts'));
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig(array(
                'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE
            ));

        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('Forecasts', 'base');
        self::$configTimeperiodType = $config['timeperiod_interval'];
        self::$configTimeperiodLeafType = $config['timeperiod_leaf_interval'];
        //Set the timeperiod_leaf_interval to TimePeriod::QUARTER_TYPE for testing purposes
        $admin->saveSetting('Forecasts', 'timeperiod_interval', TimePeriod::ANNUAL_TYPE, 'base');
        $admin->saveSetting('Forecasts', 'timeperiod_leaf_interval', TimePeriod::QUARTER_TYPE, 'base');

        self::$timeperiod = TimePeriod::getByType(TimePeriod::QUARTER_TYPE);
        self::$timeperiod->start_date = '2009-01-01';
        self::$timeperiod->end_date = '2009-03-31';
        self::$timeperiod->save();

        SugarTestTimePeriodUtilities::$_createdTimePeriods[] = self::$timeperiod;
        self::$args['timeperiod_id'] = self::$timeperiod->id;

        SugarTestForecastUtilities::setTimePeriod(self::$timeperiod);

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        // set the current user currency to the one we created
        $GLOBALS['current_user']->setPreference('currency', self::$currency->id);

        self::$user = SugarTestForecastUtilities::createForecastUser(array(
            'timeperiod_id' => self::$timeperiod->id,
            'currency_id' => self::$currency->id
        ));
        self::$args['user_id'] = self::$user['user']->id;
        self::$filterApi = new ForecastWorksheetsFilterApi();
        // get the current data set for use in the processing
        $dataArray = self::$filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$user['user']),
            array('user_id' => self::$args['user_id'], 'timeperiod_id' => self::$args['timeperiod_id'], 'module' => 'ForecastWorksheets')
        );
        self::$args['data_array'] = $dataArray['records'];
    }

    public function setUp()
    {
        $this->_user = self::$user["user"];
        $GLOBALS["current_user"] = $this->_user;
        $this->authToken = "";
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        self::$filterApi = null;
        $GLOBALS["current_user"] = null;
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testQuotaConvertedToBaseRate()
    {
        $obj = new SugarForecasting_Chart_Individual(self::$args);
        $data = $obj->process();

        $expected = SugarCurrency::convertAmountToBase(self::$user['quota']->amount, self::$user['quota']->currency_id);
        $actual = doubleval($data['values'][0]['goalmarkervalue'][0]);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @group forecasts
     * @group forecastschart
     */
    public function testQuotaLabelContainsBaseCurrencySymbol()
    {
        $obj = new SugarForecasting_Chart_Individual(self::$args);
        $data = $obj->process();

        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertStringStartsWith($base_currency->symbol, $data['values'][0]['goalmarkervaluelabel'][0]);
    }

    /**
     * @dataProvider dataProviderDatasets
     * @param string $dataset
     * @group forecasts
     * @group forecastschart
     */
    public function testChartValuesConvertedToBase($dataset)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;
        $args['ranges'] = 'include';
        $obj = new SugarForecasting_Chart_Individual($args);
        $data = $obj->process();

        $actual = 0;
        foreach($data['values'] as $value) {
            $actual += $value['gvalue'];
        }

        $expected = SugarCurrency::convertWithRate(self::$user['included_opps_totals'][$dataset], self::$user['included_opps_totals']['base_rate']);
        $actual = doubleval($actual);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @dataProvider dataProviderDatasets
     * @param string $dataset
     * @group forecasts
     * @group forecastschart
     */
    public function testChartValuesLabelsContainBaseCurrencySymbol($dataset)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;
        $obj = new SugarForecasting_Chart_Individual($args);
        $data = $obj->process();

        $base_currency = SugarCurrency::getBaseCurrency();

        foreach($data['values'] as $value) {
            $this->assertStringStartsWith($base_currency->symbol, $value['gvaluelabel']);
        }
    }

    /**
     * @dataProvider dataProviderParetoData
     * @param string $dataset
     * @param integer $chart_position
     * @group forecasts
     * @group forecastschart
     */
    public function testChartParetoLineConvertedToBase($dataset, $chart_position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;
        $args['ranges'] = 'include';
        $obj = new MockSugarForecasting_Chart_Individual($args);
        $data = $obj->process();

        // figure out which value to use
        $dataset_key = $dataset . '_case';
        if($dataset == "likely") {
            $dataset_key = "amount";
        }

        // build out what is expected to be in the pareto lines according to the months
        $expected = 0;
        $months = array_keys($obj->convertTimeperiodToChartValues());

        $arrExpected = array_combine($months, array_pad(array(), count($months), 0));

        foreach(self::$user['opportunities'] as $opp) {
            if($opp->commit_stage == "include") {
                //$month_value_key = date('m-Y', strtotime($opp->date_closed));
                $month_value_key = self::$timeperiod->getChartLabelsKey($opp->date_closed);
                $arrExpected[$month_value_key] += $opp->$dataset_key;
            }
        }

        // combine the values for the pareto lines until we hit where we are at on the chart
        $arrExpected = array_values($arrExpected);
        foreach($arrExpected as $key => $value) {
            if($key > $chart_position) {
                break;
            }
            $expected += $value;
        }

        // convert the expected back to base
        $expected = SugarCurrency::convertAmountToBase($expected, self::$currency->id);
        $actual = doubleval($data['values'][$chart_position]['goalmarkervalue'][1]);

        $this->assertEquals($expected, $actual, null, 2);
    }

    /**
     * @dataProvider dataProviderParetoData
     * @param string $dataset
     * @param integer $chart_position
     * @group forecasts
     * @group forecastschart
     * @outputBuffering disabled
     */
    public function testChartParetoLineLabelContainsBaseCurrencySymbol($dataset, $chart_position)
    {
        $args = self::$args;
        $args['dataset'] = $dataset;
        $obj = new MockSugarForecasting_Chart_Individual($args);
        $data = $obj->process();

        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertStringStartsWith($base_currency->symbol, $data['values'][$chart_position]['goalmarkervaluelabel'][1]);
    }

    /**
     * @dataProvider dataProviderRanges
     * @group forecasts
     * @group forecastschart
     */
    public function testChartFiltering($ranges)
    {
        $args = self::$args;
        $args['ranges'] = $ranges;
        $obj = new SugarForecasting_Chart_Individual($args);
        $data = $obj->process();

        foreach ($ranges as $key => $value)
        {
            $ranges[$key] = ucfirst($value);
        }

        if (!empty($ranges))
        {
            $categories = $ranges;
        }
        else
        {
            $opp_ids = SugarTestOpportunityUtilities::getCreatedOpportunityIds();
            $categories = array();
            foreach($opp_ids as $id)
            {
                $opp = BeanFactory::getBean('Opportunities', $id);
                $categories[] = ucfirst($opp->commit_stage);
            }
            $categories = array_unique($categories);
        }

        $this->assertEmpty(array_diff($categories, $data['label']));
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderRanges()
    {
        return array(
            array(array('include')),
            array(array('include', 'exclude')),
            array(array()),
        );
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        return array(
            array('likely'),
            array('best'),
            array('worst'),
        );
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderParetoData()
    {
        return array(
            array('likely', 0),
            array('likely', 1),
            array('likely', 2),
            array('best', 0),
            array('best', 1),
            array('best', 2),
            array('worst', 0),
            array('worst', 1),
            array('worst', 2),
        );
    }
}

class MockSugarForecasting_Chart_Individual extends SugarForecasting_Chart_Individual
{
    public function convertTimeperiodToChartValues()
    {
        parent::convertTimeperiodToChartValues();

        return $this->values;
    }
}
