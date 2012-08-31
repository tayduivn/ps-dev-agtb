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
    /**
     * X-Axis Label
     *
     * @var string
     */
    protected $xaxisLabel = 'Amount';

    /**
     * Y-Axis Label
     *
     * @var string
     */
    protected $yaxisLabel = '';

    /**
     * Pareto Label
     *
     * @var string
     */
    protected $goalParetoLabel = '';

    /**
     * Do we need to display the manager chart?
     *
     * @var bool
     */
    protected $isManager = false;

    /**
     * Which field we need to pull in to the manager chart from the forecast worksheet
     *
     * @var string
     */
    protected $managerAdjustedField = 'likely_adjusted';

    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
    {
        $parentApi = array(
            'forecasts_chart' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'chart'),
                'pathVars' => array('', ''),
                'method' => 'chart',
                'shortHelp' => 'Retrieve the Chart data for the given data in the Forecast Module',
                'longHelp' => 'modules/Forecasts/api/help/ForecastChartApi.html',
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
        require_once('modules/Forecasts/data/ChartAndWorksheetManager.php');

        global $mod_strings, $app_list_strings, $app_strings, $current_user, $current_language;

        $app_list_strings = return_app_list_strings_language($current_language);
        $app_strings = return_application_language($current_language);
        $mod_strings = return_module_language($current_language, 'Opportunities');
        $forecast_strings = return_module_language($current_language, 'Forecasts');

        $mgr = new ChartAndWorksheetManager();
        //define worksheet type: 'manager' or 'individual'
        $this->isManager = (isset($args['display_manager']) && $args['display_manager'] == 'true') ? true : false;

        $report_defs = $mgr->getWorksheetDefinition(($this->isManager) ? 'manager' : 'individual', 'opportunities');

        $timeperiod_id = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $user_id = isset($args['user_id']) ? $args['user_id'] : $current_user->id;
        $args['group_by'] = !isset($args['group_by']) ? "forecast" : $args['group_by'];

        if (!$this->isManager) {
            $filters = array(
                'timeperiod_id' => array('$is' => $timeperiod_id),
                'assigned_user_link' => array('id' => $user_id),
            );
        } else {
            $filters = array(
                'timeperiod_id' => array('$is' => $timeperiod_id),
                'assigned_user_link' => array('id' => array('$or' => array('$is' => $user_id, '$reports' => $user_id)))
            );
            // no matter what for the manager we need to get the group by to be "forecast";
            $args['group_by'] = "forecast";
        }

        if (isset($args['category']) && $args['category'] == "Committed") {
            $filters['forecast'] = array('$is' => 1);
        }

        // generate the report builder instance
        $rb = $this->generateReportBuilder('Opportunities', $report_defs[2], $filters, $args);

        $this->processDataset($rb, $args);

        if (isset($args['chart_type']) && !empty($args['chart_type'])) {
            $rb->setChartType($this->mapChartType($args['chart_type']));
        }

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
        if ($this->isManager) {
            $quota = $quota_bean->getGroupQuota($timeperiod_id, false, $user_id);
            $quota = array('amount' => $quota, 'formatted_amount' => format_number($quota, null, null, array('currency_symbol' => true)));
        } else {
            $quota = $quota_bean->getCurrentUserQuota($timeperiod_id, $user_id);
        }
        $likely_values = $this->getDataSetValues($filters, $args);

        // decode the data to add stuff to the properties
        $dataArray = json_decode($json, true);

        if (!$this->isManager) {
            $validTimePeriods = $this->getTimePeriodMonths($timeperiod_id);

            if (count($validTimePeriods) != count($dataArray['values'])) {
                // we need to add in the values
                $dataArray = $this->combineReportData($dataArray, $validTimePeriods);
            }
        } else {
            // we have a manager so lets
            $reportees = $this->getUserReportees($user_id);

            if(count($reportees) != count($dataArray['values'])) {
                $dataArray = $this->combineReportData($dataArray, $reportees);
            }

            // since we are on a manager, we want to show the adjusted values for which ever data set we are on
            require_once("modules/Forecasts/api/ForecastsWorksheetManagerApi.php");
            $mgr_worksheet = new ForecastsWorksheetManagerApi();
            $mgr_worksheet->setUserId($user_id);
            $mgr_worksheet->setTimePeriodId($timeperiod_id);

            // get the adjusted values
            $adjusted_values = $mgr_worksheet->getWorksheetBestLikelyAdjusted();

            // get the forecast rows
            $forecast_rows = $mgr_worksheet->getForecastValues();

            // find where the included is at if we have more than one label
            $pos = 0;
            if(count($dataArray['label']) > 1) {
                foreach($dataArray['label'] as $pos => $label) {
                    if($label == "1") {
                        break;
                    }
                }
            }

            // apply the adjusted values to the chart data
            foreach($dataArray['values'] as $key => $value) {
                // don't overwrite if we get 0's back for the one we are replacing.
                if(isset($adjusted_values[$value['label']]) && array_sum($value['values']) != 0) {
                    $adj_value = $adjusted_values[$value['label']][$this->managerAdjustedField];

                    // if we don't have a forecast for this person set the value to 0
                    if(!isset($forecast_rows[$value['label']])) {
                        $adj_value = 0;
                    }

                    $dataArray['values'][$key]['values'][$pos] = floatval($adj_value);
                    $dataArray['values'][$key]['valuelabels'][$pos] = $adj_value;

                    // adjust the group total
                    $sum = array_sum($dataArray['values'][$key]['values']);
                    $dataArray['values'][$key]['gvalue'] = floatval($sum);
                    $dataArray['values'][$key]['gvaluelabel'] = $sum;
                    if(isset($likely_values[$value['label']])) {
                        $likely_values[$value['label']]['amount'] = $sum;
                    }
                }
            }

            // fix the labels to show that it's the adjusted values
            $this->goalParetoLabel .= $forecast_strings['LBL_CHART_ADJUSTED'];
            $this->xaxisLabel .= $forecast_strings['LBL_CHART_ADJUSTED'];

            // always make sure that the columns go from the largest to the smallest
            // if we are displaying the manager chart
            usort($dataArray['values'], array($this, 'sortChartColumns'));
        }

        if(isset($args['group_by'])) {
            if($args['group_by'] == "forecast" && isset($dataArray['label'][0]))
            {
                // fix the labels
                $dataArray['label'][0] = ($dataArray['label'][0] == 0) ? $forecast_strings['LBL_CHART_NOT_INCLUDED'] : $forecast_strings['LBL_CHART_INCLUDED'];
                if(isset($dataArray['label'][1])) {
                    $dataArray['label'][1] = ($dataArray['label'][1] == 0) ? $forecast_strings['LBL_CHART_NOT_INCLUDED'] : $forecast_strings['LBL_CHART_INCLUDED'];
                }
            } else if($args['group_by'] == "probability") {
                foreach($dataArray['label'] as $key => $value) {
                    $dataArray['label'][$key] = $value . '%';
                }
            }
        }

        // add the goal marker stuff
        $dataArray['properties'][0]['goal_marker_type'] = array('group', 'pareto');
        $dataArray['properties'][0]['goal_marker_color'] = array('#3FB300', '#7D12B2');
        $dataArray['properties'][0]['goal_marker_label'] = array('Quota', $this->goalParetoLabel);
        $dataArray['properties'][0]['label_name'] = $this->yaxisLabel;
        $dataArray['properties'][0]['value_name'] = $this->xaxisLabel;

        // remove the title
        $dataArray['properties'][0]['title'] = null;

        $likely_sum = 0;

        foreach ($dataArray['values'] as $key => $value) {

            $likely = 0;

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
                $likely = $likely_values[$value['label']]['amount'];
            }

            if ($this->isManager) {
                // fix the names
                $user = BeanFactory::getBean("Users");
                $user->retrieve_by_string_fields(array('user_name' => $value['label']));

                if ($user->id == $user_id) {
                    $dataArray['values'][$key]['label'] = string_format($forecast_strings['LBL_MY_OPPORTUNITIES'],
                        array($user->get_summary_text()));
                } else {
                    $dataArray['values'][$key]['label'] = $user->get_summary_text();
                }
                unset($user);
            }

            $likely_sum += floatval($likely);
            $likely_label = format_number($likely_sum, null, null, array('currency_symbol' => true));

            // set the variables
            $dataArray['values'][$key]['goalmarkervalue'] = array(floatval($quota['amount']), $likely_sum);
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

        // if the type is manager we need to adjust the group by to be that of user_name to match up with the main report
        if ($this->isManager) {
            $rb->removeGroupBy($rb->getGroupBy('date_closed'));
            $rb->addGroupBy('user_name', 'Users', 'Opportunities:assigned_user_link');
            $rb->removeSummaryColumn($rb->getSummaryColumns('date_closed'));
            $rb->removeSummaryColumn($rb->getSummaryColumns('user_name'));
            $rb->setXAxis($rb->addSummaryColumn('user_name', 'Users', 'Opportunities:assigned_user_link'));
        }

        // run the report
        $report = new Report($rb->toJson());
        $report->run_chart_queries();

        $results = array();
        $sum = 0;

        // lets build a usable array
        foreach ($report->chart_rows as $row) {
            // ignore the total line
            if (count($row['cells']) != 2) continue;

            // keep a running total of the values
            //$sum += unformat_number($row['cells'][1]['val']);

            // key is the same that would be used for the main report
            $results[$row['cells'][0]['val']] = array(
                'amount' => unformat_number($row['cells'][1]['val']), // use the unformatted number for the value in the chart
                //'amount_formatted' => format_number($sum, null, null, array('currency_symbol' => true)) // format the number for the label
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

            if (is_array($summary)) {
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
     * Process the DataSet parameter
     *
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
                $this->managerAdjustedField = 'best_adjusted';
                $this->goalParetoLabel = 'Best';
                $rb->addSummaryColumn('best_case', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('best_case');
                break;
            case 'worst_case':
            case 'worst':
                $this->managerAdjustedField = 'worst_adjusted';
                $this->goalParetoLabel = 'Worst';
                $rb->addSummaryColumn('worst_case', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('worst_case');
                break;
            case 'likely_case':
            case 'likely':
            default:
                $this->managerAdjustedField = 'likely_adjusted';
                $this->goalParetoLabel = 'Likely';
                $rb->addSummaryColumn('amount', $rb->getDefaultModule(), null, array('group_function' => 'sum'));
                $rb->setChartColumn('amount');
                break;

        }

        $this->xaxisLabel = $this->goalParetoLabel;

        // return!
        return $rb;
    }

    /**
     * Method for sorting the dataArray before we return it so that the tallest bar is always first and the
     * lowest bar is always last.
     *
     * @param array $a          The left side of the compare
     * @param array $b          The right side of the compare
     * @return int
     */
    protected function sortChartColumns($a, $b)
    {
        if (intval($a['gvalue']) > intval($b['gvalue'])) {
            return -1;
        } else if (intval($a['gvalue']) < intval($b['gvalue'])) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get the months for the current time period
     *
     * @param $timeperiod_id
     * @return array
     */
    protected function getTimePeriodMonths($timeperiod_id)
    {
        /* @var $timeperiod TimePeriod */
        $timeperiod = BeanFactory::getBean('TimePeriods', $timeperiod_id);

        $months = array();

        $start = strtotime($timeperiod->start_date);
        $end = strtotime($timeperiod->end_date);
        while ($start < $end) {
            $months[] = date('F Y', $start);
            $start = strtotime("+1 month", $start);
        }

        return $months;
    }

    /**
     * Get the direct reportees for a user.
     *
     * @param $user_id
     * @return array
     */
    protected function getUserReportees($user_id)
    {
        $sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id',
            'id, user_name, first_name, last_name, reports_to_id, _level', false,
            "id = '{$user_id}' AND status = 'Active' AND deleted = 0", null, " AND status = 'Active' AND deleted = 0"
        );

        $result = $GLOBALS['db']->query($sql);

        $reportees = array();

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            if($row['_level'] > 2) continue;

            if($row['_level'] == 1) {
                array_unshift($reportees, $row['user_name']);
            } else {
                array_push($reportees, $row['user_name']);
            }
        }

        return $reportees;
    }

    /**
     * Add any missing data to the chart data.
     *
     * This can be users or timeperiods
     *
     * @param $dataArray
     * @param $newData
     * @return array
     */
    protected function combineReportData($dataArray, $newData) {
        $num_of_items = count($dataArray['label']);
        $empty_array = array(
            'label' => '',
            'gvalue' => '',
            'gvaluelabel' => '',
            'values' => array_pad(array(), $num_of_items, 0),
            'valuelabels' => array_pad(array(), $num_of_items, "0"),
            'links' => array_pad(array(), $num_of_items, ""),

        );
        $current_values = $dataArray['values'];
        $new_values = array_combine($newData, array_pad(array(), count($newData), $empty_array));

        foreach ($current_values as $c_val) {
            $new_values[$c_val['label']] = $c_val;
        }

        // fix the labels
        array_walk($new_values, function(&$item, $key) {
            $item['label'] = $key;
        });

        $dataArray['values'] = array_values($new_values);
        return $dataArray;
    }

}
