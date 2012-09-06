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
class QuarterTimePeriod445 extends TimePeriod implements iTimePeriod {

    /**
     * Constructor
     */
    public function __construct() {
        parent::TimePeriod();

        $this->time_period_type = 'Quarter445';
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
     * creates a new QuarterTimePeriod445 to start to use
     *
     * @return QuarterTimePeriod445
     */
    public function createNextTimePeriod() {
        $nextPeriod = new QuarterTimePeriod445();
        $timedate = TimeDate::getInstance();
        $nextStartDate = $timedate->fromUserDate($this->start_date);
        $nextEndDate = $timedate->fromUserDate($this->end_date);

        $nextStartDate = $nextStartDate->modify('+13 week');
        $nextEndDate = $nextEndDate->modify('+13 week');
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

        switch($timePeriodType) {
            case "Monthly":
                break;
            case "Weekly":
                break;

        }

    }
}