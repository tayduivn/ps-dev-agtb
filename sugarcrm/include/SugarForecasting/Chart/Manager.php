<?php


class SugarForecasting_Chart_Manager extends SugarForecasting_Chart_AbstractChart
{

    public function process()
    {
        $rb = $this->setUpReportBuilder();
        $dataArray = $this->generateChartDataArray($rb);
        $dataArray = $this->checkAllDataExists($dataArray);
        $dataArray = $this->adjustData($dataArray);
        $dataArray = $this->fixGroupByLabels($dataArray);

    }

    public function getQuota()
    {
        $args = $this->getArgs();
        /* @var $quota_bean Quota */
        $quota_bean = BeanFactory::getBean('Quotas');
        $quota = $quota_bean->getGroupQuota($args['timeperiod_id'], false, $args['user_id']);
        $quota = array('amount' => $quota, 'formatted_amount' => format_number($quota, null, null, array('currency_symbol' => true)));

        return $quota;
    }

    /**
     * Make sure that we have all the data that is required for the chart data
     *
     * @param array $dataArray
     * @return array
     */
    protected function checkAllDataExists($dataArray)
    {
        $args = $this->getArgs();
        // we have a manager so lets
        $reportees = $this->getUserReportees($args['user_id']);

        if(count($reportees) != count($dataArray['values'])) {
            $dataArray = $this->combineReportData($dataArray, $reportees);
        }

        return $dataArray;
    }

    protected function adjustData($dataArray)
    {
        global $current_language;
        $likely_values = $this->getDataSetValues($this->dataClass->getChartFilters($this->getArgs()));
        $forecast_strings = return_module_language($current_language, 'Forecasts');

        // get the adjusted values
        $adjusted_values = $this->getWorksheetBestLikelyAdjusted();
        // get the forecast rows
        $forecast_rows = $this->getForecastValues();

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

        return $dataArray;
    }

    /**
     * Overwrite since we always need to group by forecasts when we are displaying the manager chart
     *
     * @param array $args
     * @return SugarForecasting_AbstractForecast
     */
    public function setArgs($args)
    {
        // for the manager chart we always group by forecasts
        $args['group_by'] = "forecast";

        // make sure a data set is actually set.
        if (!isset($args['dataset']) || empty($args['dataset'])) {
            $args['dataset'] = "likely";
        }

        return parent::setArgs($args);
    }

    /**
     * Run a report to generate the likely values for the main report
     *
     * @param array $arrFilters     Which filters to apply to the report
     * @return array                The likely values from the system.
     */
    protected function getDataSetValues($arrFilters)
    {
        $args = $this->getArgs();

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
     * Process the DataSet parameter
     *
     * @param ReportBuilder $rb
     * @return ReportBuilder
     */
    protected function processDataset($rb)
    {
        $args = $this->getArgs();

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
     * Handle any group by arguments in the code
     *
     * @param ReportBuilder $rb         ReportBuilder Instance
     * @return ReportBuilder
     */
    protected function processGroupBy($rb)
    {
        $args = $this->getArgs();
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
     * This can be users or timeperiods
     *
     * @param $dataArray
     * @param $newData
     * @return array
     */
    protected function combineReportData($dataArray, $newData)
    {
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
        array_walk($new_values, function (&$item, $key) {
            $item['label'] = $key;
        });

        $dataArray['values'] = array_values($new_values);
        return $dataArray;
    }
}