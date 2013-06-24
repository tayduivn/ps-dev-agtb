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
 * Implements the annual representation of a time period
 * @api
 */
class MonthTimePeriod extends TimePeriod implements TimePeriodInterface {

    public function __construct() {
        //Override module_name to distinguish bean for BeanFactory
        $this->module_name = 'MonthTimePeriods';

        parent::__construct();

        //The time period type
        $this->type = TimePeriod::MONTH_TYPE;

        //Fiscal is 52-week based, chronological is year based
        $this->is_fiscal = false;

        $this->is_fiscal_year = false;

        //The number of periods in a year
        $this->periods_in_year = 12;

        //The next period modifier
        $this->next_date_modifier = '1 month';

        //The previous period modifier
        $this->previous_date_modifier = '-1 month';

        //The name template
        global $app_strings;
        $this->name_template = $app_strings['LBL_MONTH_TIMEPERIOD_FORMAT'];

        //The chart label
        $this->chart_label = "n/j";

        //The chart data interval modifier
        $this->chart_data_modifier = '+1 week';
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
        $start = $timedate->fromDbDate($this->start_date)->format('F Y');
        return string_format($this->name_template, array($start));
    }


    /**
     * Returns the formatted chart label data for the timeperiod
     *
     * @param $chartData Array of chart data values
     * @return formatted Array of chart data values where the labels are broken down by the timeperiod's increments
     */
    public function getChartLabels($chartData)
    {
        $weeks = array();
        $start = strtotime($this->start_date);
        $end = strtotime($this->end_date);
        $count = 0;

        while ($start <= $end) {
            //Find out how many days are left for this period
            $remainingDays = floor(abs($end - $start) / 86400);

            //Create the modifier for the timeperiod
            $modifier = $remainingDays > 6 ? '+6 day' : '+' . ceil($remainingDays) . ' day';

            $val = $chartData;
            $val['label'] = date($this->chart_label, $start) . '-' . date($this->chart_label, strtotime($modifier, $start));

            //We internally use $count to store the corresponding data set for the week in the given timeperiod.
            //For a one month interval we will most likely get 4 weeks except for the case of non-leap year February
            $weeks[$count++] = $val;
            $start = strtotime($this->chart_data_modifier, $start);
        }

        return $weeks;
    }


    /**
     * Returns the key for the chart label data for the date closed value
     *
     * @param String The date_closed value in db date format
     * @return String value of the key to use to map to the chart labels
     */
    public function getChartLabelsKey($dateClosed)
    {
        $timedate = TimeDate::getInstance();
        $ts = $timedate->fromDbDate($dateClosed)->getTimestamp();

        $key = $this->id . ':keys';
        $keys = sugar_cache_retrieve($key);

        if(!empty($keys)) {
            foreach($keys as $timestamp=>$count) {
               if($ts < $timestamp) {
                   return $count;
               }
            }
            return count($keys);
        }

        $keys = array();
        $start = $timedate->fromDbDate($this->start_date);
        $end = $timedate->fromDbDate($this->end_date);
        $count = 0;

        while ($start <= $end) {
            $start->modify($this->chart_data_modifier);
            $tsKey = $start->getTimestamp();
            $keys[$tsKey] = $count;
            $count++;
        }

        sugar_cache_put($key, $keys);

        foreach($keys as $tsKey=>$count) {
            if($ts < $tsKey) {
                return $count;
            }
        }

        return count($keys);
    }
}
