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

require_once('modules/Forecasts/Common.php');

$comm = new Common();
$comm->get_all_users();
$db = DBManagerFactory::getInstance();
$timedate = new TimeDate();
$now = $timedate->nowDbDate();
$timeperiod_id = $db->getOne("SELECT id 
                                FROM timeperiods 
                                WHERE start_date < '{$now}' 
                                    AND end_date > '{$now}' 
                                    AND is_fiscal_year = 0 
                                    AND deleted = 0");
if(!empty($timeperiod_id))
{
    $current_timeperiod_id = $timeperiod_id;
}
else
{
    $GLOBALS['log']->error();
}

foreach($comm->all_users as $user_id => $reports_to)
{
        $opps = get_related_opps($user_id, $timeperiod_id);

        if (!empty($opps))
        {
            $comm->current_user = $user_id;
            $comm->my_managers = array();
            $comm->get_my_managers();

            $best = 0;
            $likely = 0;
            $worst = 0;

            foreach($opps as $opp_id)
            {
                $opp = new Opportunity();
                $opp->retrieve($opp_id);

                $best += $opp->best_case;
                $likely += $opp->likely_case;
                $worst += $opp->worst_case;

                $worksheet = new Worksheet();
                $worksheet->user_id = $user_id;
                $worksheet->timeperiod_id = $current_timeperiod_id;
                $worksheet->forecast_type = 'Direct';
                $worksheet->related_id = $opp->id;
                $worksheet->related_forecast_type = '';
                $worksheet->best_case = $opp->best_case + 500;
                $worksheet->likely_case = $opp->likely_case + 500;
                $worksheet->worst_case = $opp->worst_case + 500;
                $worksheet->save();

                $increment = 1000;

                foreach($comm->my_managers as $manager_id)
                {
                    $worksheet = new Worksheet();
                    $worksheet->user_id = $manager_id;
                    $worksheet->timeperiod_id = $current_timeperiod_id;
                    $worksheet->forecast_type = 'Rollup';
                    $worksheet->related_id = $opp->id;
                    $worksheet->related_forecast_type = '';
                    $worksheet->best_case = $opp->best_case + $increment;
                    $worksheet->likely_case = $opp->likely_case + $increment;
                    $worksheet->worst_case = $opp->worst_case + $increment;
                    $worksheet->save();

                    $increment += 500;
                }
            }
            $increment = 500;

            foreach($comm->my_managers as $manager_id)
            {
                $worksheet = new Worksheet();
                $worksheet->user_id = $manager_id;
                $worksheet->timeperiod_id = $current_timeperiod_id;
                $worksheet->forecast_type = 'Rollup';
                $worksheet->related_id = $user_id;
                $worksheet->related_forecast_type = 'Direct';
                $worksheet->best_case = $best + $increment;
                $worksheet->likely_case = $likely + $increment;
                $worksheet->worst_case = $worst + $increment;
                $worksheet->save();

                $increment += 100;
            }
        }
}

function get_related_opps($user_id, $timeperiod_id)
{
    global $db;

    $opps = array();

    $query = "SELECT o.id
                FROM opportunities o
                WHERE o.assigned_user_id = '$user_id'
                    AND o.timeperiod_id = '$timeperiod_id'
                    AND o.deleted = 0
                    AND o.sales_stage != 'Closed Lost'";

    $result = $db->query($query, false, "Error fetching related opps for user");

    while (($row = $db->fetchByAssoc($result)) != null)
    {
        $opps[] = $row['id'];
    }

    return $opps;
}