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
class AnnualTimePeriod extends TimePeriod implements TimePeriodInterface {

    public function __construct() {
        $this->module_name = 'AnnualTimePeriods';

        parent::__construct();

        //The time period type
        $this->type = TimePeriod::ANNUAL_TYPE;

        //The leaf period type
        $this->leaf_period_type = TimePeriod::QUARTER_TYPE;

        //The number of leaf periods
        $this->leaf_periods = 4;

        $this->periods_in_year = 1;

        //Fiscal is 52-week based, chronological is year based
        $this->is_fiscal = false;

        $this->is_fiscal_year = true;

        //The next period modifier
        $this->next_date_modifier = $this->is_fiscal ? '52 week' : '1 year';

        //The previous period modifier
        $this->previous_date_modifier = $this->is_fiscal ? '-52 week' : '-1 year';

        global $app_strings;
        //The name template
        $this->name_template = $app_strings['LBL_ANNUAL_TIMEPERIOD_FORMAT'];

        //The leaf name template
        $this->leaf_name_template = $app_strings['LBL_QUARTER_TIMEPERIOD_FORMAT'];
    }


    /**
     * getTimePeriodName
     *
     * Returns the timeperiod name.  The TimePeriod base implementation simply returns the $count argument passed
     * in from the code
     *
     * @param $count The timeperiod series count
     * @return string The formatted name of the timeperiod
     */
    public function getTimePeriodName($count)
    {
        $timedate = TimeDate::getInstance();
        return string_format($this->name_template, array($timedate->fromDbDate($this->start_date)->format('Y')));
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
            $val['label'] = date('Y', $start);
            $months[date('Y', $start)] = $val;
            $start = strtotime('+1 year', $start);
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
        return date('Y', strtotime($dateClosed));
    }

}