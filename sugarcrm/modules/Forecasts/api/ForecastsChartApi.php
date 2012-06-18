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

class ForecastsChartApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'chart' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','chart'),
                'pathVars' => array('',''),
                'method' => 'chart',
                'shortHelp' => 'forecast chart',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastChartApi.html#chart',
            ),
        );
        return $parentApi;
    }

    public function chart($api, $args) {
        require_once('modules/Reports/Report.php');
        global $current_user, $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $saved_report = new SavedReport();
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case_worksheet","label":"Best Case (adjusted)","table_key":"self"},{"name":"likely_case_worksheet","label":"Likely Case (adjusted)","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"best_case_worksheet","label":"SUM: Best Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"abc123","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"","assigned_user_id":"seed_chris_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_4",null,null,"Filter.1_table_filter_row_4",null,null],"module":"Users","label":"Assigned to User"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"probability","table_key":"self","qualifier_name":"between","runtime":1,"input_name0":"70","input_name1":"100"},"1":{"name":"sales_stage","table_key":"self","qualifier_name":"one_of","runtime":1,"input_name0":["Prospecting","Qualification","Needs Analysis","Value Proposition","Id. Decision Makers","Perception Analysis","Proposal\/Price Quote","Negotiation\/Review","Closed Won","Closed Lost"]},"2":{"name":"timeperiod_id","table_key":"self","qualifier_name":"is","runtime":1,"input_name0":["2cb25447-b5c3-c71e-78bc-4fd2262190fa"]},"3":{"name":"id","table_key":"Opportunities:assigned_user_link","qualifier_name":"is","runtime":1,"input_name0":["Current User"]}}}}', 'detailed_summary', 'vBarF');        //$result = $saved_report->save_report(-1, $current_user->id, $report_defs['ForecastSeedReport1'][1], $report_defs['ForecastSeedReport1'][0], $report_defs['ForecastSeedReport1'][3], $report_defs['ForecastSeedReport1'][2], 1, '1', $report_defs['ForecastSeedReport1'][4]);
        $report = new Report($report_defs['ForecastSeedReport1'][2]);


        $testFilters = array(
            'timeperiod_id' => isset($args['tp']) ? $args['tp'] : array('$is' => TimePeriod::getCurrentId()),
            'assigned_user_link' => array('id' => 'seed_chris_id'),
            //'probability' => array('$between' => array('0', '70')),
            //'sales_stage' => array('$in' => array('Prospecting', 'Qualification', 'Needs Analysis')),
        );

        require_once('include/SugarParsers/Filter.php');
        require_once("include/SugarParsers/Converter/Report.php");
        require_once("include/SugarCharts/ReportBuilder.php");

        $rb = new ReportBuilder("Opportunities");
        $rb->setDefaultReport($report_defs['ForecastSeedReport1'][2]);

        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse($testFilters);
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        $report->report_def['filters_def'] = $reportFilters;


        //Get the goal marker values
        require_once("include/SugarCharts/ChartDisplay.php");
        $chartDisplay = new ChartDisplay();
        $chartDisplay->setReporter($report);

        if ($chartDisplay->canDrawChart() === false) {
            // no chart to display, so lets just kick back the error message
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }

        $chart = $chartDisplay->getSugarChart();
        $json = $chart->buildJson($chart->generateXML());
        // fix-up the json since it builds it wrong for the php parser
        $json = str_replace(array("\t", "\n"), "", $json);
        $json = str_replace("'", '"', $json);

        $dataArray = json_decode($json, true);

        $dataArray['properties']['goal_market_type'] = array('group', 'group');
        $dataArray['properties']['goal_marker_color'] = array('#3FB300', '#444444');
        $dataArray['properties']['goal_market_label'] = array('Quota', 'Likely');

        return $dataArray;


        //$report->chart_rows gives us the data
        //$report->chart_rows

        //Todo: separate the return types depending on what is requested
        //Here is a sample of a vertical stacked bar chart data
        $response = array(
            'properties' => array(
                'gauge_target_list'=>'Array',
                'title'=>'Title',
                'subtitle'=>'Subtitle',
                'type'=>'horizontal group by chart',
                'legend'=>'on',
                'labels'=>'value',
                'print'=>'on',
                'goal_marker_type' => array('group', 'group'), //Two groups of markers
                'goal_marker_color' => array('#3FB300', '#444444'),
                'goal_marker_label' => array('Quota', 'Likely'),
                'label_name' => 'Sales Stage',
                'value_name' => 'Amount'
            ),
            'label' => array('Qualified','Proposal','Negotiation','Closed'),
            'color' => array('#8c2b2b', '#468c2b', '#2b5d8c', '#cd5200', '#e6bf00', '#7f3acd', '#00a9b8'),
        );

        //This will get messy... maybe we just have to query directly?
        $values = array();
        //_pp($report->chart_rows);
        foreach($report->chart_rows as $data)
        {
            foreach($data['cells'] as $key1=>$items)
            {
                 foreach($items as $key2=>$value)
                 {

                 }
            }
        }

        $response['values'] = $values;
        return $response;

    }

}
