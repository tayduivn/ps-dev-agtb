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

    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the annual time period
     * @param bool $fiscal_period flag to determine if the timeperiod is meant to be a fiscal or calendar based period
     */
    public function __construct($start_date = null, $fiscal_period = false) {
        parent::__construct();
        $timedate = TimeDate::getInstance();

        //set defaults
        $this->time_period_type = 'Annual';
        $this->is_fiscal = $fiscal_period;
        $this->is_leaf = false;
        $this->date_modifier = $this->is_fiscal ? '52 week' : '1 year';

        $this->setStartDate($start_date);
    }

    /**
     * override parent function so to add a name for the annual time period.  This can
     *
     * @param null $startDate  db format date string to set the start date of the annual time period
     */
    public function setStartDate($start_date = null) {
        parent::setStartDate($start_date);
        $timedate = TimeDate::getInstance();

        if(empty($this->name)) {
            $start_date_time = $timedate->fromDbDate($this->start_date);
            $this->name = $this->is_fiscal ? "Fiscal " : "" . "Year ".$start_date_time->format("Y");
        }
    }

    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     *
     * @param string $timePeriodType
     * @return mixed
     */
    public function buildLeaves($timePeriodType) {
        if($this->hasLeaves()) {
            throw new Exception("This TimePeriod already has leaves");
        }

        if($this->is_leaf) {
            throw new Exception("Leaf Time Periods cannot have leaves");
        }

        $n = 0;
        $timedate = TimeDate::getInstance();
        $start_date_time = $timedate->fromDbDate($this->start_date);
        $nameStart = "Q";

        $this->load_relationship('related_timeperiods');
        //valid time periods to be leaves of this period
        switch($timePeriodType) {
            //set up the first leaf
            case "Quarter";
                $n = 4;
                $nameStart = "Q";
                $leafPeriod = BeanFactory::newBean("QuarterTimePeriods");
                break;
            case "Quarter544";
                $n = 4;
                $nameStart = "FQ";
                $leafPeriod = BeanFactory::newBean("Quarter544TimePeriods");
                break;
            case "Quarter454";
                $n = 4;
                $nameStart = "FQ";
                $leafPeriod = BeanFactory::newBean("Quarter454TimePeriods");
                break;
            case "Quarter445";
                $n = 4;
                $nameStart = "FQ";
                $leafPeriod = BeanFactory::newBean("Quarter445TimePeriods");
                break;
            case "Month";
                $n = 12;
                $nameStart = $this->is_fiscal ? "FM" : "M";
                $leafPeriod = BeanFactory::newBean("MonthTimePeriods");
                $leafPeriod->is_fiscal = $this->is_fiscal;
                break;
            default;
                $n = 4;
                if($this->is_fiscal) {
                    $leafPeriod = BeanFactory::newBean("QuarterTimePeriods445");
                    $nameStart = "FQ";
                } else {
                    $leafPeriod = BeanFactory::newBean("QuarterTimePeriods");
                    $nameStart = "Q";
                }
                break;

        }
        $leafPeriod->setStartDate($this->start_date);
        $leafPeriod->is_leaf = true;
        $leafPeriod->name = $nameStart."1 ".$start_date_time->format("Y");
        $leafPeriod->save();
        $this->related_timeperiods->add($leafPeriod->id);

        //loop the count to create the next n leaves to fill out the relationship
        for($i = 2; $i <= $n; $i++) {
            if($timePeriodType == "Month" && ((i) % 3 == 0)) {
                // leaf is monthly and need to even out the fiscal numbering
                $leafPeriod = $leafPeriod->createNextTimePeriod(5);
            } else {
                $leafPeriod = $leafPeriod->createNextTimePeriod();
            }
            $leafPeriod->name = $nameStart.$i." ".$start_date_time->format("Y");
            $this->related_timeperiods->add($leafPeriod->id);
        }

    }
}