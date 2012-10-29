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

    var $module_name = 'MonthTimePeriods';

    public function __construct() {
        parent::__construct();
        //The time period type
        $this->time_period_type = TimePeriod::MONTH_TYPE;

        //Fiscal is 52-week based, chronological is year based
        $this->is_fiscal = false;

        //Used to indicate whether or not TimePeriod instance is a leaf type
        $this->is_leaf = true;

        //The next period modifier
        $this->next_date_modifier = '1 month';

        //The previous period modifier
        $this->previous_date_modifier = '-1 month';

        //The name template
        $this->name_template = "%s %d";
    }


    /**
     * getTimePeriodName
     *
     * Returns the timeperiod name
     *
     * @return string The formatted name of the timeperiod
     */
    public function getTimePeriodName($count)
    {
        $timedate = TimeDate::getInstance();
        return sprintf($this->name_template, $timedate->fromDbDate($this->start_date)->format('M'), $timedate->fromDbDate($this->start_date)->format('Y'));
    }

    /**
     * sets the start date, based on a db formatted date string passed in.  If null is passed in, now is used.
     * The end date is adjusted as well to hold to the contract of this being an quarter time period
     *
     * @param null $startDate  db format date string to set the start date of the quarter time period
     */
    /*
    public function setStartDate($start_date = null, $week_count = 4) {
        $timedate = TimeDate::getInstance();
        //check start_date, put it to now if it's not passed in
        if(is_null($start_date)) {
            $start_date = $timedate->getNow()->asDbDate();
        }

        $end_date = $timedate->fromDbDate($start_date);

        //set the start/end date
        $this->start_date = $start_date;

        if($this->is_fiscal) {
            $end_date = $end_date->modify('+'.$week_count.' week');
            $end_date = $end_date->modify('-1 day');
        } else {
            $end_date = $end_date->modify('+1 month');
            $end_date = $end_date->modify('-1 day');
        }
        $this->end_date = $timedate->asDbDate($end_date);
    }
    */

    /**
     * creates a new MonthTimePeriod to start to use
     *
     * @param int $week_length denotes how many weeks should be included in month for a fiscal month
     *
     * @return MonthTimePeriod
     */
    public function createNextTimePeriod($week_length=4) {
        $timedate = TimeDate::getInstance();
        $nextEndDate = $timedate->fromDbDate($this->end_date);

        $nextStartDate = $nextEndDate->modify('+1 day');
        $nextStartDate = $timedate->asDbDate($nextStartDate);
        $nextPeriod = BeanFactory::newBean($this->time_period_type."TimePeriods");
        $nextPeriod->is_fiscal = $this->is_fiscal;
        $nextPeriod->setStartDate($nextStartDate, $week_length);
        $nextPeriod->is_leaf = $this->is_leaf;
        $nextPeriod->save();

        return $nextPeriod;
    }

}