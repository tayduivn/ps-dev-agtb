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

require_once('modules/Forecasts/ForecastsDefaults.php');
require_once('include/generic/LayoutManager.php');
require_once('modules/Reports/Report.php');

class Bug62783Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        // Setup Forecast defaults
        ForecastsDefaults::setupForecastSettings();
    }

    public function tearDown()
    {
        //Clear config table of Forecasts values
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config WHERE category = 'Forecasts'");

        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if fiscal query filters for DateTime type fields are working properly
     *
     * @param $qualifier - qualifier (year/quarter)
     * @param $startDate - Fiscal start date
     * @param $date - date for which to to find the fiscal quarter/year
     * @param $modifyStart - Modification to start date
     * @param $modifyEnd - Modification to end date
     * @param $expectedStart - Expected start date in query
     * @param $expectedEnd - Expected end date in query
     * @param $timezone - User timezone
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter($qualifier, $startDate, $date, $modifyStart, $modifyEnd, $expectedStart, $expectedEnd, $timezone)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', json_encode($startDate), 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new SugarWidgetFielddatetimeTest($layoutManager);
        $layoutDef = array('qualifier_name' => $qualifier);

        $result = $SWFDT->getFiscalYearFilter($layoutDef, $modifyStart, $modifyEnd, $date);

        $this->assertContains($expectedStart, $result, 'Greater than part of query generated incorrectly.');
        $this->assertContains($expectedEnd, $result, 'Lower than part of query generated incorrectly.');
    }

    /**
     * Test if groupBy query for fiscal year/quarter
     * on DateTime type fields is working properly
     *
     * @param $startDate - Fiscal start date
     * @param $timezone - User timezone
     * @param $expected - Expected result
     * @param $reportDef - Report def
     *
     * @dataProvider groupDataProvider
     */
    public function testDateTimeFiscalQueryGroupBy($startDate, $timezone, $expected, $reportDef)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', json_encode($startDate), 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->date_closed = $startDate;
        $opportunity->save();

        $reportDef = preg_replace('/\s\s+/', '', str_replace('{REPLACE}', $opportunity->id, $reportDef));

        $report = new Report($reportDef);
        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertEquals(1, $row['count'], 'Report count should be 1');
        $this->assertEquals($expected, $row['cells'][0], 'Wrong grouping result');
    }

    public static function groupDataProvider()
    {
        return array(
            array(
                '2013-05-05',
                'America/Los_Angeles',
                '2012',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Year: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
            array(
                '2013-05-05',
                'UTC',
                '2013',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Year: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
            array(
                '2013-05-05',
                'Europe/Helsinki',
                '2013',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Year: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Year: Expected Close Date",
                        "column_function":"fiscalYear",
                        "qualifier":"fiscalYear",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
            array(
                '2013-05-05',
                'America/Los_Angeles',
                'Q4 2012',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Quarter: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
            array(
                '2013-05-05',
                'UTC',
                'Q1 2013',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Quarter: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
            array(
                '2013-05-05',
                'Europe/Helsinki',
                'Q1 2013',
                '{
                    "display_columns":[],
                    "module":"Opportunities",
                    "group_defs":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self",
                        "type":"date",
                        "force_label":"Fiscal Quarter: Expected Close Date"
                    }],
                    "summary_columns":[{
                        "name":"date_closed",
                        "label":"Fiscal Quarter: Expected Close Date",
                        "column_function":"fiscalQuarter",
                        "qualifier":"fiscalQuarter",
                        "table_key":"self"
                    }],
                    "report_name":"Bug62783",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{"
                        self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"id",
                                "table_key":"self",
                                "qualifier_name":"is",
                                "input_name0":"{REPLACE}"
                            }
                        }
                    }
                }'
            ),
        );
    }

    public static function filterDataProvider()
    {
        return array(
            array(
                'quarter',
                '1987-01-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-03-31 07:00:00'",
                "< '2013-07-01 07:00:00'",
                'America/Los_Angeles'
            ),
            array(
                'year',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2013-12-31 22:00:00'",
                "< '2014-12-31 22:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                '1987-01-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= '2013-01-01 00:00:00'",
                "< '2013-04-01 00:00:00'",
                'UTC'
            ),
            array(
                'year',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-01-01 00:00:00'",
                "< '2015-01-01 00:00:00'",
                'UTC'
            ),
            array(
                'quarter',
                '2018-05-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-04-30 07:00:00'",
                "< '2013-07-30 07:00:00'",
                'America/Los_Angeles'
            ),
            array('year',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-04-30 21:00:00'",
                "< '2015-04-30 21:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                '2018-05-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= '2013-02-01 00:00:00'",
                "< '2013-05-01 00:00:00'",
                'UTC'
            ),
            array(
                'year',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-05-01 00:00:00'",
                "< '2015-05-01 00:00:00'",
                'UTC'
            ),
        );
    }
}

/**
 * Helper class for testing getFiscalYearFilter() method
 */
class SugarWidgetFielddatetimeTest extends SugarWidgetFielddatetime
{
    public function getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date = '')
    {
        return parent::getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date);
    }
}
