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

require_once('include/api/ChartApi.php');

class ForecastsChartApi extends ChartApi {

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'forecasts_chart' => array(
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
        global $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case_worksheet","label":"Best Case (adjusted)","table_key":"self"},{"name":"likely_case_worksheet","label":"Likely Case (adjusted)","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"best_case_worksheet","label":"SUM: Best Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"abc123","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"","assigned_user_id":"seed_chris_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":[]}', 'detailed_summary', 'vBarF');


        if(!isset($args['user']) || empty($args['user'])) {
            global $current_user;
            $args['user'] = $current_user->id;
        }

        $testFilters = array(
            'timeperiod_id' => isset($args['tp']) ? $args['tp'] : array('$is' => TimePeriod::getCurrentId()),
            'assigned_user_link' => array('id' => $args['user']),
            //'probability' => array('$between' => array('0', '70')),
            //'sales_stage' => array('$in' => array('Prospecting', 'Qualification', 'Needs Analysis')),
        );

        require_once('include/SugarParsers/Filter.php');
        require_once("include/SugarParsers/Converter/Report.php");
        require_once("include/SugarCharts/ReportBuilder.php");

        // create the a report builder instance
        $rb = new ReportBuilder("Opportunities");
        // load the default report into the report builder
        $rb->setDefaultReport($report_defs['ForecastSeedReport1'][2]);

        // parse any filters from above
        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse($testFilters);
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder
        $rb->addFilter($reportFilters);

        if(isset($args['ct']) && !empty($args['ct'])) {
            $rb->setChartType($this->mapChartType($args['ct']));
        }

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        //Get the goal marker values
        require_once("include/SugarCharts/ChartDisplay.php");
        // create the chart display engine
        $chartDisplay = new ChartDisplay();
        // set the reporter with the chart contents from the report builder
        $chartDisplay->setReporter(new Report($chart_contents));

        // if we can't draw the chart, kick it back
        if ($chartDisplay->canDrawChart() === false) {
            // no chart to display, so lets just kick back the error message
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }

        // lets get some json!
        $json = $chartDisplay->generateJson();

        if($json == "No Data") {
            return '';
        }

        // decode the data to add stuff to the properties
        $dataArray = json_decode($json, true);

        // add the goal marker stuff
        $dataArray['properties']['subtitle'] = $args['user'];
        $dataArray['properties']['goal_market_type'] = array('group', 'group');
        $dataArray['properties']['goal_marker_color'] = array('#3FB300', '#444444');
        $dataArray['properties']['goal_market_label'] = array('Quota', 'Likely');

        // return the data now
        return $dataArray;
    }

}
