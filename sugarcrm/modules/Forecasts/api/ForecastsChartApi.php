<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
require_once('include/SugarParsers/Filter.php');
require_once("include/SugarParsers/Converter/Report.php");
require_once("include/SugarCharts/ReportBuilder.php");

class ForecastsChartApi extends ChartApi
{
    protected $xaxisLabel = 'Amount';
    protected $yaxisLabel = '';

    protected $goalParetoLabel = '';

    public function registerApiRest()
    {
        $parentApi = array(
            'forecasts_chart' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'chart'),
                'pathVars' => array('', ''),
                'method' => 'chart',
                'shortHelp' => 'Retrieve the Chart data for the given data in the Forecast Module',
                'longHelp' => 'modules/Forecasts/api/help/ForecastChartApi.html#chart',
            ),
        );
        return $parentApi;
    }

    /**
     * Build out the chart for the sales rep view in the forecast module
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function chart($api, $args)
    {
        require_once('modules/Reports/Report.php');
        global $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case_worksheet","label":"Best Case (adjusted)","table_key":"self"},{"name":"likely_case_worksheet","label":"Likely Case (adjusted)","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"abc123","chart_type":"vBarF","do_round":0,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"","assigned_user_id":"seed_chris_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":[]}', 'detailed_summary', 'vBarF');

        if (!isset($args['user_id']) || empty($args['user_id'])) {
            global $current_user;
            $args['user_id'] = $current_user->id;
        }

        $timeperiod = TimePeriod::getCurrentId();
        if (isset($args['timeperiod_id']) && !empty($args['timeperiod_id'])) {
            $timeperiod = $args['timeperiod_id'];
        }

        $testFilters = array(
            'timeperiod_id' => array('$is' => $timeperiod),
            'assigned_user_link' => array('id' => $args['user_id']),
            //'probability' => array('$between' => array('0', '70')),
            //'sales_stage' => array('$in' => array('Prospecting', 'Qualification', 'Needs Analysis')),
        );

        if(isset($args['category']) && $args['category'] == "Committed") {
            $testFilters['forecast'] = array('$is' => 1);
        }

        // generate the report builder instance
        $rb = $this->generateReportBuilder('Opportunities', $report_defs['ForecastSeedReport1'][2], $testFilters, $args);

        if (isset($args['chart_type']) && !empty($args['chart_type'])) {
            $rb->setChartType($this->mapChartType($args['chart_type']));
        }

        // make sure the chart column is the amount field
        $rb->setChartColumn($rb->getSummaryColumns('amount'));

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        //return $rb->toArray();

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

        // if we have no data return an empty string
        if ($json == "No Data") {
            return '';
        }

        // since we have data let get the quota line
        /* @var $quota_bean Quota */
        $quota_bean = BeanFactory::getBean('Quotas');
        $quota = $quota_bean->getCurrentUserQuota($timeperiod, $args['user_id']);
        $likely_values = $this->getDataSetValues($testFilters, $args);

        // decode the data to add stuff to the properties
        $dataArray = json_decode($json, true);

        // add the goal marker stuff
        $dataArray['properties'][0]['goal_marker_type'] = array('group', 'pareto');
        $dataArray['properties'][0]['goal_marker_color'] = array('#3FB300', '#7D12B2');
        $dataArray['properties'][0]['goal_marker_label'] = array('Quota', $this->goalParetoLabel);
        $dataArray['properties'][0]['label_name'] = $this->yaxisLabel;
        $dataArray['properties'][0]['value_name'] = $this->xaxisLabel;

        foreach ($dataArray['values'] as $key => $value) {

            $likely = 0;
            $likely_label = 0;

            //$dataArray['values'][$key]['sales_stage'] = $dataArray['label'];

            // format the value labels
            if ($rb->getChartColumnType() == "currency") {
                foreach ($value['valuelabels'] as $vl_key => $vl_val) {
                    // ignore the empties
                    if (empty($vl_val)) continue;

                    $dataArray['values'][$key]['valuelabels'][$vl_key] = format_number($vl_val, null, null, array('currency_symbol' => true));
                }
            }

            // extract the values to variables
            if (isset($likely_values[$value['label']])) {
                list($likely, $likely_label) = array_values($likely_values[$value['label']]);
            }

            // set the variables
            $dataArray['values'][$key]['goalmarkervalue'] = array(intval($quota['amount']), intval($likely));
            $dataArray['values'][$key]['goalmarkervaluelabel'] = array($quota['formatted_amount'], $likely_label);
        }

        // return the data now
        return $dataArray;
    }

    /**
     * Run a report to generate the likely values for the main report
     *
     * @param array $arrFilters     Which filters to apply to the report
     * @param array $args           Service Arguments
     * @return array                The likely values from the system.
     */
    protected function getDataSetValues($arrFilters, $args)
    {
        // base report
        $report_base = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"}],"report_name":"Test Goal Marker Report","chart_type":"none","do_round":0,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{}}';

        // generate a report builder instance
        // ignore any group by for this method
        unset($args['group_by']);
        $rb = $this->generateReportBuilder("Opportunities", $report_base, $arrFilters, $args);

        $this->processDataset($rb, $args);

        // run the report
        $report = new Report($rb->toJson());
        $report->run_chart_queries();

        $results = array();
        $sum = 0;

        error_log(var_export($report->chart_rows, true));

        // lets build a usable arary
        foreach ($report->chart_rows as $row) {
            // ignore the total line
            if (count($row['cells']) != 2) continue;

            // keep a running total of the values
            $sum += unformat_number($row['cells'][1]['val']);

            // key is the same that would be used for the main report
            $results[$row['cells'][0]['val']] = array(
                'amount' => $sum, // use the unformatted number for the value in the chart
                'amount_formatted' => format_number($sum, null, null, array('currency_symbol' => true)) // format the number for the label
            );
        }

        // return the array
        return $results;
    }

    /**
     * Common code to generate the report builder
     *
     * @param string|SugarBean $module      Which module are we basing this off of
     * @param string $report_base           The base report to start with in a json string
     * @param array $filters                What filters to apply
     * @param array $args                   Service Arguments
     * @return ReportBuilder
     */
    protected function generateReportBuilder($module, $report_base, $filters, $args = array())
    {
        // make sure module is a string and not a sugar bean
        if ($module instanceof SugarBean) {
            $module = $module->module_dir;
        }

        // create the a report builder instance
        $rb = new ReportBuilder($module);
        // load the default report into the report builder
        $rb->setDefaultReport($report_base);

        // create the filter parser with the base module
        $filter = new SugarParsers_Filter(BeanFactory::getBean($module));
        $filter->parse($filters);
        // convert the filters into a reporting engine format
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder
        $rb->addFilter($reportFilters);

        // handle any group by if it is set
        $this->processGroupBy($rb, $args);

        // return the report builder
        return $rb;
    }

    /**
     * Handle any group by arguments in the code
     *
     * @param ReportBuilder $rb         ReportBuilder Instance
     * @param array $args               Service Arguments
     * @return ReportBuilder
     */
    protected function processGroupBy($rb, $args)
    {
        if (isset($args['group_by']) && !empty($args['group_by'])) {
            // get the current group by
            $group_by = $rb->getGroupBy();

            // if we have more than one, remove all but the date_closed
            if (count($group_by) > 1) {
                // remove anyone that is not the date_closed column
                foreach ($group_by as $gb) {
                    if ($gb['name'] != "date_closed") {
                        $rb->removeGroupBy($gb);
                    }
                }
            }
            // now lets add the new gb
            //$rb->addGroupBy($args['group_by']);
            // the group is really a summary column that is made to be the y-axis
            $summary = $rb->addSummaryColumn($args['group_by']);

            if(is_array($summary)) {
                $rb->setYAxis($summary);
            }

            // set the label
            $this->yaxisLabel = $summary['label'];

            // add a count column just in case.
            //$rb->addSummaryCount();
        }

        return $rb;
    }

    /**
     * @param ReportBuilder $rb
     * @param $args
     * @return ReportBuilder
     */
    protected function processDataset($rb, $args)
    {
        // make sure something is set
        if (!isset($args['dataset']) || empty($args['dataset'])) {
            $args['dataset'] = "likely";
        }

        // switch around it
        switch (strtolower($args['dataset'])) {
            case 'best_case':
            case 'best':
                $this->goalParetoLabel = 'Best';
                $rb->addSummaryColumn('best_case_worksheet', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('best_case_worksheet');
                break;
            case 'worst_case':
            case 'worst':
                $this->goalParetoLabel = 'Worst';
                $rb->addSummaryColumn('worst_case', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('worst_case');
                break;
            case 'likely_case':
            case 'likely':
            default:
                $this->goalParetoLabel = 'Likely';
                $rb->addSummaryColumn('likely_case_worksheet', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('likely_case_worksheet');
                break;

        }

        // return!
        return $rb;
    }

}
