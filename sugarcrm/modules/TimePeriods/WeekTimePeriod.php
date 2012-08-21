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
class WeekTimePeriod extends TimePeriod implements iTimePeriod {

    /**
     * Constructor
     */
    public function WeekTimePeriod() {
        parent::TimePeriod();

        $this->time_period_type = 'Weekly';
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
     * subtracts the end from the start date to return the date length in days
     *
     * @return mixed
     */
    public function getLengthInDays() {
        $diff = $this->end_date->diff($this->start_date);
        return $diff->d;
    }

    /**
     * creates a new AnnualTimePeriod to start to use
     *
     * @return AnnualTimePeriod
     */
    public function createNextTimePeriod() {
        $nextPeriod = new WeekTimePeriod();

        $nextPeriod->start_date = $this->end_date->modify('+1 day');
        $nextPeriod->end_date = $this->start_date->modify('+1 week');

        return $nextPeriod;
    }

    /**
     * loads related time periods and returns whether there are leaves populated.
     *
     * @return bool
     */
    public function hasLeaves() {
        $this->load_relationship('related_timeperiods');

        if(length($this->related_timeperiods))
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
}