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
require_once('modules/TimePeriods/iTimePeriod.php');
class AnnualTimePeriod extends TimePeriod implements iTimePeriod {

    /**
     * Constructor
     */
    public function __construct($start_date = null, $fiscal_period = false) {
        parent::__construct();
        $timedate = TimeDate::getInstance();

        //set defaults
        $this->time_period_type = 'Annual';
        $this->is_fiscal_year = $fiscal_period;
        $this->is_leaf = false;

        //check start_date, put it to now if it's not passed in
        if(is_null($start_date)) {
            $start_date = $timedate->getNow()->asDbDate();
        }

        $start_date = $timedate->fromDbDate($start_date);

        //set the start/end date
        $this->start_date = $timedate->asUserDate($start_date);
        if($this->is_fiscal_year) {
            $endDate = $start_date->modify('+52 week');
            $endDate = $endDate->modify('-1 day');
            $this->end_date = $timedate->asUserDate($endDate);
        } else {
            $endDate = $start_date->modify('+1 year');
            $endDate = $endDate->modify('-1 day');
            $this->end_date = $timedate->asUserDate($endDate);
        }
    }

    /**
     * Saves the Annual TimePeriod
     *
     * @param bool $check_notify
     * @return mixed
     */
    public function save($check_notify=false) {
        return parent::save($check_notify);
    }

    /**
     * creates a new AnnualTimePeriod to start to use
     *
     * @return AnnualTimePeriod
     */
    public function createNextTimePeriod() {
        $nextPeriod = new AnnualTimePeriod();
        $timedate = TimeDate::getInstance();
        $nextStartDate = $timedate->fromUserDate($this->start_date);
        $nextEndDate = $timedate->fromUserDate($this->end_date);

        $nextStartDate = $nextStartDate->modify('+1 year');
        $nextEndDate = $nextEndDate->modify('+1 year');
        $nextPeriod->start_date = $timedate->asUserDate($nextStartDate);
        $nextPeriod->end_date = $timedate->asUserDate($nextEndDate);

        $nextPeriod->save();

        return $nextPeriod;
    }

    /**
     * loads related time periods and returns whether there are leaves populated.
     *
     * @return bool
     */
    public function hasLeaves() {
        $this->load_relationship('related_timeperiods');

        if(count($this->related_timeperiods))
            return true;

        return false;

    }

    /**
     * loads the related time periods and returns the array
     *
     * @return mixed
     */
    public function getLeaves() {
        $this->load_relationship('related_timeperiods');

        return $this->related_timeperiods;
    }

    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     *
     * @param string $timePeriodType
     * @return mixed
     */
    public function buildLeaves($timePeriodType) {
        if($this->hasLeaves()) {
            return;
        }
        $timedate = TimeDate::getInstance();
        $leafStartDate = $timedate->fromUserDate($this->start_date);

        $n = 0;

        $this->load_relationship('related_timeperiods');
        //valid time periods to be leaves of this period
        switch($timePeriodType) {
            //set up the first leaf
            case "Quarter":
                $n = 4;
                $leafPeriod = BeanFactory::newBean("QuarterTimePeriods");
                $leafPeriod->start_date = $this->start_date;
                $leafEndDate = $leafStartDate->modify("+3 month");
                $leafPeriod->end_date = $timedate->asUserDate($leafEndDate);
                $this->related_timeperiods->add($leafPeriod->id);
                break;
            case "Quarter544":
                $n = 4;
                $leafPeriod = BeanFactory::newBean("QuarterTimePeriods544");
                $leafPeriod->start_date = $this->start_date;
                $leafEndDate = $leafStartDate->modify("+13 week");
                $leafPeriod->end_date = $timedate->asUserDate($leafEndDate);
                $this->related_timeperiods->add($leafPeriod->id);
                break;
            case "Quarter445":
                $n = 4;
                $leafPeriod = BeanFactory::newBean("QuarterTimePeriods445");
                $leafPeriod->start_date = $this->start_date;
                $leafEndDate = $leafStartDate->modify("+13 week");
                $leafPeriod->end_date = $timedate->asUserDate($leafEndDate);
                $this->related_timeperiods->add($leafPeriod->id);
                break;
            case "Month":
                $n = 12;
                $leafPeriod = BeanFactory::newBean("MonthTimePeriods");
                $leafPeriod->start_date = $this->start_date;
                $leafEndDate = $leafStartDate->modify("+1 month");
                $leafPeriod->end_date = $timedate->asUserDate($leafEndDate);
                $this->related_timeperiods->add($leafPeriod->id);
                break;
            case "Week":
                $n = 52;
                $leafPeriod = BeanFactory::newBean("WeekTimePeriods");
                $leafPeriod->start_date = $this->start_date;
                $leafEndDate = $leafStartDate->modify("+1 week");
                $leafPeriod->end_date = $timedate->asUserDate($leafEndDate);
                $this->related_timeperiods->add($leafPeriod->id);
                break;

        }

        //loop the count to create the next n leaves to fill out the relationship
        for($i = 1; $i < $n; $i++) {
            $leafPeriod = $leafPeriod->createNextTimePeriod();
            $this->related_timeperiods->add($leafPeriod->id);
        }

        $this->save();

    }
}