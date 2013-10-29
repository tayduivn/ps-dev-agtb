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
/**
 * Create a job for updating time periods
 */
class SugarUpgradeCreateTimeperiodJob extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (!version_compare($this->from_version, '6.7.0', '<'))
        {
            // only for upgrades from below 6.7
            return;
        }
        // add class::SugarJobCreateNextTimePeriod job if not there
        $job = new Scheduler();
        $job->retrieve_by_string_fields(array("job" => 'class::SugarJobCreateNextTimePeriod'));
        if(empty($job->id)) {
                $job->name               = translate('LBL_OOTB_CREATE_NEXT_TIMEPERIOD', 'Schedulers');
                $job->job                = 'class::SugarJobCreateNextTimePeriod';
                $job->date_time_start    = '2013-01-01 00:00:01';
                $job->date_time_end      = '2030-12-31 23:59:59';
                $job->job_interval       = '0::23::*::*::*';
                $job->status             = 'Active';
                $job->created_by         = '1';
                $job->modified_user_id   = '1';
                $job->catch_up           = '0';
                $job->save();
        }
    }
}
