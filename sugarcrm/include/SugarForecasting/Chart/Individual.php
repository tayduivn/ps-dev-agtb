<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarCurrency.php');
require_once('include/SugarForecasting/Chart/AbstractChart.php');
require_once('include/SugarForecasting/Individual.php');
class SugarForecasting_Chart_Individual extends SugarForecasting_Chart_AbstractChart
{
    /**
     * Default Group By
     *
     * @var string
     */
    protected $group_by = "forecast";

    /**
     * Default Category, 1 = include in forecast, 0 = include everything
     *
     *
     * @var string
     */
    protected $category = 1;

    /**
     * The labels to be used in the legend and how to parse the data
     *
     * @var array
     */
    protected $group_by_labels = array();

    /**
     * The value array that we build out and pass back
     *
     * @var array
     */
    protected $values = array();

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        if (isset($args['category'])) {
            $cat = "";
            if(is_array($args['category'])) {
                $this->category = strtolower(array_shift($args['category']));
            } else {
                $this->category = strtolower($args['category']);
            }
        }
        if (isset($args['group_by']) && !empty($args['group_by'])) {
            $this->group_by = strtolower($args['group_by']);
        }
        parent::__construct($args);
    }

    /**
     * Process the data into the current JIT Chart Format
     * @return array
     */
    public function process()
    {
        $this->getIndividualData();
        $this->parseCategory();
        $this->parseGroupBy();
        $this->convertTimeperiodToChartValues();
        return $this->formatDataForChart();
    }

    /**
     * Run the Individual Code and set the data in this object
     */
    protected function getIndividualData()
    {
        $rep_obj = new SugarForecasting_Individual($this->getArgs());
        $this->dataArray = $rep_obj->process();
    }

    /**
     * Parse any data out that doesn't match the category filter
     * TODO: need to support buckets, currently it doesn't
     */
    protected function parseCategory()
    {
        if (empty($this->category)) {
            // nothing to see here, we just go about our business
            return;
        }

        foreach ($this->dataArray as $key => $val) {
            if ($val['forecast'] == 1) continue;

            unset($this->dataArray[$key]);
        }

        reset($this->dataArray);
    }

    /**
     * Parse out the data that we are grouping by to find the labels that we need for the chart data
     *
     * Currently this only supports the following fields, forecasts, sales_stage and probability
     *
     * TODO: add support for fields to be set via a config or admin setting
     */
    protected function parseGroupBy()
    {
        global $current_language;

        // get the language strings for the modules that we need
        $forecast_strings = return_module_language($current_language, 'Forecasts');
        if ($this->group_by == "sales_stage") {
            foreach ($this->dataArray as $data) {
                $this->group_by_labels[] = $data['sales_stage'];
            }

            $this->group_by_labels = array_unique($this->group_by_labels);
        } else if ($this->group_by == "probability") {
            foreach ($this->dataArray as $data) {
                $this->group_by_labels[] = $data['probability'] . "%";
            }
            $this->group_by_labels = array_unique($this->group_by_labels);
            ksort($this->group_by_labels);
        } else {
            // default to forecast, just on the off chance it's not set
            $this->group_by = "forecast";
            // here we only have a potential for two

            if (empty($this->category)) {
                $this->group_by_labels[] = $forecast_strings['LBL_CHART_NOT_INCLUDED'];
            }
            $this->group_by_labels[] = $forecast_strings['LBL_CHART_INCLUDED'];
        }

        $this->group_by_labels = array_values($this->group_by_labels);
    }

    /**
     * Format the data from the Manager Worksheet into a usable format for the charting engine
     *
     * @return array
     */
    protected function formatDataForChart()
    {
        global $current_user, $current_language;
        $currency_id = $current_user->getPreference('currency');

        // get the language strings for the modules that we need
        $forecast_strings = return_module_language($current_language, 'Forecasts');
        $opp_strings = return_module_language($current_language, 'Opportunities');

        // default the label name to empty to prevent a notice from fireing
        $label_name = "";

        // load up the data into the chart
        foreach ($this->dataArray as $data) {

            // figure out where we need to put this in the array
            $month_value_key = date('m-Y', strtotime($data['date_closed']));

            // figure out where this needs to be put in the values array
            $value_key = 0;
            // TODO support more fields.
            switch($this->group_by) {
                case 'sales_stage':
                    $label_name = $opp_strings['LBL_SALES_STAGE'];
                    $value_key = array_search($data['sales_stage'], $this->group_by_labels);
                    break;
                case 'probability':
                    $label_name = $opp_strings['LBL_PROBABILITY'];
                    $value_key = array_search($data['probability'] . '%', $this->group_by_labels);
                    break;
                case 'forecast':
                    // break left out should fall though to the default
                default:
                    $label_name = $opp_strings['LBL_FORECAST'];
                    if($this->category == "committed") {
                        $value_key = 0;
                    } else if($data['forecast'] == 1) {
                        $value_key = 1;
                    }
                    break;
            }

            // if the data set is likely we need to use the amount field from the data
            $dataset_key = $this->dataset . '_case';
            if($this->dataset == "likely") {
                $dataset_key = "amount";
            }

            // Bug 56330: if the dataset_key doesn't exist default to 0
            $dataset_value = (isset($data[$dataset_key])) ? $data[$dataset_key] : 0;

            // put the values in to their proper locations and add to any that are already there
            $this->values[$month_value_key]['values'][$value_key] += number_format($dataset_value, 2, '.', '');
            $this->values[$month_value_key]['gvalue'] += number_format($dataset_value, 2, '.', '');

        }

        // get the quota for the current user
        $quota = $this->getUserQuota();

        $goal_value_total = 0;
        // final adjust of the data. this sets the labels and the total values for the goal markers
        foreach($this->values as $key => $value) {
            $goal_value_total += $value['gvalue'];
            $this->values[$key]['goalmarkervalue'][0] = number_format($quota, 2, '.', '');
            $this->values[$key]['goalmarkervalue'][1] = number_format($goal_value_total, 2, '.', '');
            $this->values[$key]['goalmarkervaluelabel'][0] = SugarCurrency::formatAmountUserLocale($quota, $currency_id);
            $this->values[$key]['goalmarkervaluelabel'][1] = SugarCurrency::formatAmountUserLocale($goal_value_total, $currency_id);

            $this->values[$key]['gvaluelabel'] = SugarCurrency::formatAmountUserLocale($value['gvalue'], $currency_id);

            // set the labels to be correct
            foreach($value['values'] as $val_key => $val) {
                $this->values[$key]['valuelabels'][$val_key] = SugarCurrency::formatAmountUserLocale($val, $currency_id);
            }

        }

        // figure out the label
        switch ($this->dataset) {
            case "best":
                $label = $forecast_strings['LB_FS_BEST_CASE'];
                break;
            case "worst":
                $label = $forecast_strings['LB_FS_WORST_CASE'];
                break;
            case 'likely':
            default:
                $label = $forecast_strings['LB_FS_LIKELY_CASE'];
                break;
        }

        // set the properties for the return array
        $properties = $this->defaultPropertiesArray;
        $properties['goal_marker_label'][1] = $label;
        $properties['value_name'] = $label;
        $properties['label_name'] = $label_name;

        // create the chart data as the display engine expects it
        $chart = array(
            'properties' => array(
                '0' => $properties
            ),
            'color' => $this->defaultColorsArray,
            'label' => array_values($this->group_by_labels),
            'values' => array_values($this->values),
        );

        return $chart;
    }

    /**
     * Return the quota for the current user and time period
     *
     * @return mixed
     */
    protected function getUserQuota()
    {
        /* @var $quota_bean Quota */
        $quota_bean = BeanFactory::getBean('Quotas');
        $quota = $quota_bean->getCurrentUserQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'));

        return $quota['amount'];
    }


    /**
     * Find the months for a given timeperiod and turn them into values arrays that can be used by the charting
     * display engine
     */
    protected function convertTimeperiodToChartValues()
    {
        /* @var $timeperiod TimePeriod */
        $timeperiod = BeanFactory::getBean('TimePeriods', $this->getArg('timeperiod_id'));

        $months = array();

        $start = strtotime($timeperiod->start_date);
        $end = strtotime($timeperiod->end_date);

        $num_of_items = count($this->group_by_labels);

        $empty_array = $this->defaultValueArray;
        $empty_array['values'] = array_pad(array(), $num_of_items, 0);
        $empty_array['valuelabels'] = array_pad(array(), $num_of_items, "0");
        $empty_array['links'] = array_pad(array(), $num_of_items, "");
        $empty_array['goalmarkervalue'] = array(0, 0);
        $empty_array['goalmarkervaluelabel'] = array("0", "0");

        while ($start < $end) {
            $val = $empty_array;
            $val['label'] = date('F Y', $start);
            $months[date('m-Y', $start)] = $val;
            $start = strtotime("+1 month", $start);
        }

        $this->values = $months;
    }

}