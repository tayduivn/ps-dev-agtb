<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('include/api/ModuleApi.php');

class ForecastsWorksheetApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'worksheet' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','worksheet'),
                'pathVars' => array('',''),
                'method' => 'worksheet',
                'shortHelp' => 'A ping',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#ping',
            ),
        );
        return $parentApi;
    }

    /**
     * This method returns the result for a sales rep view/manager's opportunities view
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function worksheet($api, $args)
    {
        require_once('modules/Reports/Report.php');
        global $current_user, $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $saved_report = new SavedReport();
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case_worksheet","label":"Best Case (adjusted)","table_key":"self"},{"name":"likely_case_worksheet","label":"Likely Case (adjusted)","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_2"],"module":"Users","label":"Assigned to User"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"timeperiod_id","table_key":"self","qualifier_name":"is","runtime":1,"input_name0":["aba5fc2a-4693-b984-956d-4fd9f9b32dc6"]},"1":{"name":"id","table_key":"Opportunities:assigned_user_link","qualifier_name":"is","runtime":1,"input_name0":["Current User"]}}}}', 'detailed_summary', 'vBarF');
        $report = new Report($report_defs['ForecastSeedReport1'][2]);

        $testFilters = array(
            'timeperiod_id' => isset($args['timeperiod_id']) ? $args['timeperiod_id'] : array('$is' => TimePeriod::getCurrentId()),
            'id' => isset($args['user_id']) ? $args['user_id'] : $current_user->id
        );


        require_once('include/SugarParsers/Filter.php');
        require_once("include/SugarParsers/Converter/Report.php");
        require_once("include/SugarCharts/ReportBuilder.php");

        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse($testFilters);
        $converter = new SugarParsers_Converter_Report(new ReportBuilder("Opportunities"));
        $reportFilters = $filter->convert($converter);
        $report->report_def['filters_def'] = $reportFilters;

        $report->clear_group_by();
        $report->create_order_by();
        $report->create_select();
        $report->create_where();
        $report->create_group_by(false);
        $report->create_from();
        $report->create_query();
        $limit = false;
        if ($report->report_type == 'tabular' && $report->enable_paging) {
            $report->total_count = $report->execute_count_query();
            $limit = true;
        }
        $result = $GLOBALS['db']->query($report->query);

        $opps = array();
        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $row['id'] = $row['primaryid'];
            $row['forecast'] = $row['opportunities_forecast'];
            $row['name'] = $row['opportunities_name'];
            $row['amount'] = $row['opportunities_amount'];
            $row['date_closed'] = $row['opportunities_date_closed'];
            $row['probability'] = $row['opportunities_probability'];
            $row['sales_stage'] = $row['opportunities_sales_stage'];
            $row['best_case_worksheet'] = $row['OPPORTUNITIES_BEST_CAS81CC16'];
            $row['likely_case_worksheet'] = $row['OPPORTUNITIES_LIKELY_C7E6E04'];

            //Should we unset the data we don't need here so as to limit data sent back?

            $opps[] = $row;
        }
        return $opps;
    }

}
