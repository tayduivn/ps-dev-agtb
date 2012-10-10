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
 * Implements the fiscal quarter representation of a time period where the monthly
 * leaves are split by the longest month occurring first
 * @api
 */
class Quarter544TimePeriod extends TimePeriod implements TimePeriodInterface {
    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the quarter time period
     */
    public function __construct($start_date = null) {
        parent::__construct();
        $timedate = TimeDate::getInstance();

        //set defaults
        $this->time_period_type = 'Quarter544';
        $this->is_fiscal = true;
        $this->is_leaf = false;

        $this->setStartDate($start_date);
    }

    /**
     * sets the start date, based on a db formatted date string passed in.  If null is passed in, now is used.
     * The end date is adjusted as well to hold to the contract of this being an quarter time period
     *
     * @param null $startDate  db format date string to set the start date of the quarter time period
     */
    public function setStartDate($start_date = null) {
        $timedate = TimeDate::getInstance();

        //check start_date, put it to now if it's not passed in
        if(is_null($start_date)) {
            $start_date = $timedate->asDbDate($timedate->getNow());
        }
        $end_date = $timedate->fromDbDate($start_date);

        //set the start/end date
        $this->start_date = $start_date;
        $end_date = $end_date->modify('+13 week');
        $end_date = $end_date->modify('-1 day');
        $this->end_date = $timedate->asDbDate($end_date);
    }

    /**
     * creates a new Quarter544TimePeriod to start to use
     *
     * @return Quarter544TimePeriod
     */
    public function createNextTimePeriod() {
        $timedate = TimeDate::getInstance();
        $nextStartDate = $timedate->fromDbDate($this->end_date);
        $nextStartDate = $nextStartDate->modify('+1 day');
        $nextPeriod = BeanFactory::newBean($this->time_period_type."TimePeriods");
        $nextPeriod->setStartDate($timedate->asDbDate($nextStartDate));
        $nextPeriod->is_leaf = $this->is_leaf;
        $nextPeriod->save();

        return $nextPeriod;
    }

    /**
     * creates a new Quarter544TimePeriod to keep past records
     *
     * @return Quarter544TimePeriod
     */
    public function createPreviousTimePeriod() {
        $timedate = TimeDate::getInstance();
        $previousStartDate = $timedate->fromDbDate($this->start_date);
        $previousStartDate = $previousStartDate->modify('-13 week');
        $previousPeriod = BeanFactory::newBean($this->time_period_type."TimePeriods");
        $previousPeriod->is_fiscal = $this->is_fiscal;
        $previousPeriod->setStartDate($timedate->asDbDate($previousStartDate));
        $previousPeriod->save();

        return $previousPeriod;
    }

    /**
     * loads related time periods and returns whether there are leaves populated.
     *
     * @return bool
     */
    public function hasLeaves() {
        if(count($this->getLeaves()))
            return true;

        return false;

    }

    /**
     * removes related timeperiods
     */
    public function removeLeaves() {
        $this->load_relationship('related_timeperiods');
        $this->related_timeperiods->delete($this->id);
    }

    /**
     * loads the related time periods and returns the array
     *
     * @return mixed
     */
    public function getLeaves() {
        //$this->load_relationship('related_timeperiods');
        $leaves = array();
        $db = DBManagerFactory::getInstance();
        $query = "select id, time_period_type from timeperiods "
        . "WHERE parent_id = " . $db->quoted($this->id) . " "
        . "AND is_leaf = 1 AND deleted = 0 order by start_date_timestamp";

        $result = $db->query($query);

        while($row = $db->fetchByAssoc($result)) {
            array_push($leaves, BeanFactory::getBean($row['time_period_type']."TimePeriods", $row['id']));
        }
        return $leaves;
    }

    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     *
     * @param string $timePeriodType ignored for now as current requirements only allow monthly for quarters.  Left in place in case it is used in the future for weeks/fortnights/etc
     * @return mixed
     */
    public function buildLeaves($timePeriodType) {
        if($this->hasLeaves()) {
            throw new Exception("This TimePeriod already has leaves");
        }

        if($this->is_leaf) {
            throw new Exception("Leaf Time Periods cannot have leaves");
        }

        $this->load_relationship('related_timeperiods');

        //1st month leaf gets the extra week here
        $leafPeriod = BeanFactory::newBean('MonthTimePeriods');
        $leafPeriod->is_fiscal = true;
        $leafPeriod->setStartDate($this->start_date, 5);
        $leafPeriod->is_leaf = 1;
        $leafPeriod->save();
        $this->related_timeperiods->add($leafPeriod->id);

        //create second month leaf
        $leafPeriod = $leafPeriod->createNextTimePeriod(4);
        $this->related_timeperiods->add($leafPeriod->id);

        //create third month leaf
        $leafPeriod = $leafPeriod->createNextTimePeriod(4);
        $this->related_timeperiods->add($leafPeriod->id);

    }
}