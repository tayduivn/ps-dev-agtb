<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/TimePeriods/TimePeriodInterface.php');
/**
 * Implements the calendar quarter representation of a time period where the monthly
 * leaves are split by the calendar month
 * @api
 */
class QuarterTimePeriod extends TimePeriod implements TimePeriodInterface {

    public function __construct() {
        //Override module_name to distinguish bean for BeanFactory
        $this->module_name = 'QuarterTimePeriods';

        parent::__construct();

        //The time period type
        $this->type = TimePeriod::QUARTER_TYPE;

        //The leaf period type
        $this->leaf_period_type = TimePeriod::MONTH_TYPE;

        //The number of leaf periods
        $this->leaf_periods = 3;

        //The number of periods in a year
        $this->periods_in_year = 4;

        //Fiscal is 52-week based, chronological is year based
        $this->is_fiscal = false;

        $this->is_fiscal_year = false;

        //The next period modifier
        $this->next_date_modifier = '3 month';

        //The previous period modifier
        $this->previous_date_modifier = '-3 month';

        //The name template
        global $app_strings;
        $this->name_template = $app_strings['LBL_QUARTER_TIMEPERIOD_FORMAT'];

        //The leaf name template
        $this->leaf_name_template = $app_strings['LBL_MONTH_TIMEPERIOD_FORMAT'];

        //The chart label
        $this->chart_label = "F Y";

        //The date formatting key for chart labels
        $this->chart_data_key = "m-Y";

        //The chart data interval modifier
        $this->chart_data_modifier = '+1 month';
    }

    /**
     * Returns the timeperiod name
     *
     * @param $count int value of the time period count (not used in MonthTimePeriod class)
     * @return string The formatted name of the timeperiod
     */
    public function getTimePeriodName($count)
    {
        global $sugar_config;
        $timedate = TimeDate::getInstance();
        $start = $timedate->fromDbDate($this->start_date)->format($sugar_config['datef']);
        $end = $timedate->fromDbDate($this->end_date)->format($sugar_config['datef']);
        return string_format($this->name_template, array($count, $start, $end));
    }


    /**
     * Returns the formatted chart label data for the timeperiod
     *
     * @param $chartData Array of chart data values
     * @return formatted Array of chart data values where the labels are broken down by the timeperiod's increments
     */
    public function getChartLabels($chartData) {
        $months = array();

        $start = strtotime($this->start_date);
        $end = strtotime($this->end_date);

        while ($start < $end) {
            $val = $chartData;
            $val['label'] = date($this->chart_label, $start);
            $months[date($this->chart_data_key, $start)] = $val;
            $start = strtotime($this->chart_data_modifier, $start);
        }

        return $months;
    }


    /**
     * Returns the key for the chart label data for the date closed value
     *
     * @param String The date_closed value in db date format
     * @return String value of the key to use to map to the chart labels
     */
    public function getChartLabelsKey($dateClosed) {
        return date($this->chart_data_key, strtotime($dateClosed));
    }
}