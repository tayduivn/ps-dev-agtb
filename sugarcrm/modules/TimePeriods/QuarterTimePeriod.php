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

    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the quarter time period
     */
    public function __construct() {
        parent::__construct();
        //set defaults
        $this->time_period_type = 'Quarter';
        $this->is_fiscal = false;
        $this->is_leaf = false;
        $this->date_modifier = '3 month';
        $this->next_date_modifier = '3 month';
        $this->previous_date_modifier = '-3 month';
    }

    public function buildPreviousLeaves($timePeriods)
    {

    }

    public function buildNextLeaves($timePeriods)
    {

    }

    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     *
     * @param string $timePeriodType ignored for now as current requirements only allow monthly for quarters.  Left in place in case it is used in the future for weeks/fortnights/etc
     * @return mixed
     */
    /*
    public function buildLeaves($timePeriodType) {
        if($this->hasLeaves()) {
            throw new Exception("This TimePeriod already has leaves");
        }

        if($this->is_leaf) {
            throw new Exception("Leaf Time Periods cannot have leaves");
        }

        $this->load_relationship('related_timeperiods');

        switch($timePeriodType) {
            case "Monthly";
                $n = 3;
                $leafPeriod = BeanFactory::newBean("MonthTimePeriods");
                $leafPeriod->is_fiscal = $this->is_fiscal;
                break;
            default;
                $n = 3;
                $leafPeriod = BeanFactory::newBean("MonthTimePeriods");
                $leafPeriod->is_fiscal = $this->is_fiscal;
                break;
        }
        $leafPeriod->setStartDate($this->start_date);
        $leafPeriod->is_leaf = true;
        $leafPeriod->save();
        $this->related_timeperiods->add($leafPeriod->id);

        //loop the count to create the next n leaves to fill out the relationship
        for($i = 1; $i < $n; $i++) {
            $leafPeriod = $leafPeriod->createNextTimePeriod();
            $this->related_timeperiods->add($leafPeriod->id);
        }
    }
    */
}