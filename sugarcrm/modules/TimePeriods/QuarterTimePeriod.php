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
        $start_year = $timedate->fromDbDate($this->start_date)->format('Y');
        return string_format($this->name_template, array($count, $start_year));
    }


    /**
     * Returns the formatted chart label data for the timeperiod
     *
     * @param $chartData Array of chart data values
     * @return formatted Array of chart data values where the labels are broken down by the TimePeriod's increments
     */
    public function getChartLabels($chartData)
    {
        if(!empty($_SESSION['authenticated_user_language'])) {
            $list_strings = return_mod_list_strings_language($_SESSION['authenticated_user_language'], 'Calendar');
        } else {
            global $current_language;
            $list_strings = return_mod_list_strings_language($current_language, 'Calendar');
        }

        $timedate = TimeDate::getInstance();
        $months = array();
        $startDate = $timedate->fromDbDate($this->start_date)->setTime(0, 0, 0);
        $nextDate = $timedate->fromDbDate($this->start_date)->setTime(0, 0, 0);
        $endDate = $timedate->fromDbDate($this->end_date)->setTime(23, 59, 59);
        $startDay = $startDate->format('j');
        $isFirst = $startDay == 1;
        $isLastDayOfMonth = $startDay == $startDate->format('t');
        $count = 0;

        while($count < 3) {
            $val = $chartData;

            $nextDate->modify($this->chart_data_modifier);
            $startDay = $startDate->format('j');
            $nextDay = $nextDate->format('j');

            //If the startDay was greater than the 28th and the nextDay is less than the 4th we know we have skipped a month
            //and so we subtract out the number of days we have gone over
            if($startDay > 28 && $nextDay < 4) {
                $nextDate->modify("-{$nextDay} day");
            } else if($isLastDayOfMonth) {
                $nextDate->setDate($nextDate->format('Y'), $nextDate->format('n'), $endDate->format('t'));
            }

            if($isFirst) {
                $month = $startDate->format('n');
                if(isset($list_strings['dom_cal_month_long'][$month])) {
                    $val['label'] = $list_strings['dom_cal_month_long'][$month] . ' ' . $startDate->format('Y');
                } else {
                    $val['label'] = $startDate->format($this->chart_label);
                }
            } else if ($count == 2) {
                $val['label'] = $startDate->format('n/j') . '-' . $timedate->fromDbDate($this->end_date)->format('n/j');
            } else {
                $val['label'] = $startDate->format('n/j') . '-' . $timedate->fromDbDate($nextDate->asDbDate())->modify('-1 day')->format('n/j');
            }
            $val['start_timestamp'] = $startDate->getTimestamp();
            $val['end_timestamp'] = $nextDate->getTimestamp();
            $startDate = $timedate->fromDbDate($nextDate->asDbDate());
            $months[$count++] = $val;
        }
        return $months;
    }


    /**
     * Returns the key for the chart label data for the date closed value
     *
     * @param String The date_closed value in db date format
     * @return String value of the key to use to map to the chart labels
     */
    public function getChartLabelsKey($dateClosed)
    {
        $key = $this->id . ':keys';
        $keys = sugar_cache_retrieve($key);
        $timedate = TimeDate::getInstance();
        $ts = $timedate->fromDbDate($dateClosed)->getTimestamp();

        if(!empty($keys)) {
            foreach($keys as $timestamp=>$count) {
               if($ts <= $timestamp) {
                   return $count;
               }
            }
            return 2;
        }

        $keys = array();
        $startDate = $timedate->fromDbDate($this->start_date);
        $nextDate = $timedate->fromDbDate($this->start_date);
        $endDate = $timedate->fromDbDate($this->end_date);
        $startDay = $startDate->format('j');
        $isLastDayOfMonth = $startDay == $startDate->format('t');
        $count = 0;

        while($count < 3) {
            $nextDate->modify($this->chart_data_modifier);
            $startDay = $startDate->format('j');
            $nextDay = $nextDate->format('j');

            //If the startDay was greater than the 28th and the nextDay is less than the 4th we know we have skipped a month
            //and so we subtract out the number of days we have gone over
            if($startDay > 28 && $nextDay < 4) {
                $nextDate->modify("-{$nextDay} day");
            } else if($isLastDayOfMonth) {
                $nextDate->setDate($nextDate->format('Y'), $nextDate->format('n'), $endDate->format('t'));
            }

            if($count == 2) {
                $tsKey = $timedate->fromDbDate($this->end_date)->getTimestamp();
            } else {
                $tsKey = $timedate->fromDbDate($nextDate->asDbDate())->modify('-1 day')->getTimestamp();
            }

            $keys[$tsKey] = $count;
            $startDate = $timedate->fromDbDate($nextDate->asDbDate());
            $count++;
        }

        sugar_cache_put($key, $keys);
        foreach($keys as $tsKey=>$count) {
            if($ts <= $tsKey) {
                return $count;
            }
        }
        return 2;
    }
}
