<?php

require_once('include/SugarCurrency.php');
require_once('include/SugarForecasting/Chart/AbstractChart.php');
require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_Chart_Manager extends SugarForecasting_Chart_AbstractChart
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        $this->isManager = true;

        parent::__construct($args);
    }

    /**
     * Process the data into the current JIT Chart Format
     * @return array
     */
    public function process()
    {
        $this->getManagerData();
        return $this->formatDataForChart();
    }

    /**
     * Run the Manager Code and set the data in this object
     */
    public function getManagerData()
    {
        $mgr_obj = new SugarForecasting_Manager($this->getArgs());
        $this->dataArray = $mgr_obj->process();
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

        // get the quota from the data
        $quota = $this->getQuotaTotalFromData();

        // sort the data so it's in the correct order
        usort($this->dataArray, array($this, 'sortChartColumns'));

        // loop variables
        $sum_value = 0;
        $values = array();

        // load up the data into the chart
        foreach($this->dataArray as $data) {
            $val = $this->defaultValueArray;

            $val['label'] = $data['name'];
            $val['gvalue'] = number_format($data[$this->dataset . '_adjusted'], 2, '.', '');
            $val['gvaluelabel'] = number_format($data[$this->dataset . '_adjusted'], 2, '.', '');
            $val['values'][] = number_format($data[$this->dataset . '_adjusted'], 2, '.', '');
            $val['valuelabels'][] = SugarCurrency::formatAmountUserLocale($data[$this->dataset . '_adjusted'], $currency_id);
            $val['links'][] = "";
            $val['goalmarkervalue'][] = number_format($quota, 2, '.', '');
            $sum_value += $data[$this->dataset . '_adjusted'];
            $val['goalmarkervalue'][] = number_format($sum_value, 2, '.', '');
            $val['goalmarkervaluelabel'][] = SugarCurrency::formatAmountUserLocale($quota, $currency_id);
            $val['goalmarkervaluelabel'][] = SugarCurrency::formatAmountUserLocale($sum_value, $currency_id);

            $values[] = $val;
        }

        // figure out the label
        switch($this->dataset) {
            case "best":
                $label = $forecast_strings['LBL_BEST_CASE_VALUE'];
                break;
            case "worst":
                $label = $forecast_strings['LBL_WORST_CASE_VALUE'];
                break;
            case 'likely':
            default:
                $label = $forecast_strings['LBL_LIKELY_CASE_VALUE'];
                break;
        }

        // fix the properties
        $properties = $this->defaultPropertiesArray;
        $properties['goal_marker_label'][1] = $label;
        $properties['value_name'] = $label;
        $properties['label_name'] = $opp_strings['LBL_FORECAST'];

        // create the chart array
        $chart = array(
            'properties' => array(
                '0' => $properties
            ),
            'colors' => $this->defaultColorsArray,
            'label' => array($forecast_strings['LBL_CHART_INCLUDED']),
            'values' => $values,
        );

        return $chart;
    }

    /**
     * Get the quota from the sum of all the rows in the dataset
     *
     * @return int
     */
    protected function getQuotaTotalFromData()
    {
        $quota = 0;

        foreach($this->dataArray as $data) {
            $quota += $data['quota'];
        }

        return $quota;
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
        if (intval($a[$this->dataset . '_adjusted']) > intval($b[$this->dataset . '_adjusted'])) {
            return -1;
        } else if (intval($a[$this->dataset . '_adjusted']) < intval($b[$this->dataset . '_adjusted'])) {
            return 1;
        } else {
            return 0;
        }
    }


}