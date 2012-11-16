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
 * leaves are split by the longest month occurring at the end of the quarter
 * @api
 */
class Quarter445TimePeriod extends TimePeriod implements TimePeriodInterface {

    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the quarter time period
     */
    public function __construct($start_date = null) {
        parent::__construct();
        //set defaults
        $this->type = 'Quarter445';
        $this->is_fiscal = true;
        $this->date_modifier = '13 week';
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

        $this->load_relationship('related_timeperiods');

        //1st month
        $leafPeriod = BeanFactory::newBean('MonthTimePeriods');
        $leafPeriod->is_fiscal = true;
        $leafPeriod->setStartDate($this->start_date, 4);
        $leafPeriod->save();
        $this->related_timeperiods->add($leafPeriod->id);
        $leafPeriod->save();

        //create second month leaf
        $leafPeriod = $leafPeriod->createNextTimePeriod(4);
        $this->related_timeperiods->add($leafPeriod->id);
        $leafPeriod->save();

        //create third month leaf, this one gets the extra week
        $leafPeriod = $leafPeriod->createNextTimePeriod(5);
        $this->related_timeperiods->add($leafPeriod->id);
        $leafPeriod->save();

        $this->save();

    }
}