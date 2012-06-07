<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * WorksheetSeedData.php
 *
 * This is a class used for creating worksheet seed data.
 *
 */

class WorksheetSeedData {

/**
 * populateSeedData
 *
 */
public static function populateSeedData()
{
require_once('modules/Forecasts/Common.php');
require_once('modules/TimePeriods/TimePeriod.php');

$comm = new Common();
$comm->get_all_users();
$db = DBManagerFactory::getInstance();

$created_ids = array();

//Get the previous, current and next timeperiods
$result = $GLOBALS['db']->query("SELECT id FROM timeperiods WHERE is_fiscal_year = 0");
while(($row = $GLOBALS['db']->fetchByAssoc($result)) != null)
{
    $timeperiod_id = $row['id'];

    foreach($comm->all_users as $user_id => $reports_to)
    {
            $opps = self::getRelatedOpportunities($user_id, $timeperiod_id);

            echo var_export($opps, true);

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

                    //This is a sales rep's worksheet entry
                    $worksheet = new Worksheet();
                    $worksheet->user_id = $user_id;
                    $worksheet->timeperiod_id = $timeperiod_id;
                    $worksheet->forecast_type = 'Direct';
                    $worksheet->related_id = $opp->id;
                    $worksheet->related_forecast_type = '';
                    $worksheet->best_case = $opp->best_case + 500;
                    $worksheet->likely_case = $opp->likely_case + 500;
                    $worksheet->worst_case = $opp->worst_case + 500;
                    $worksheet->save();
                    $created_ids[] = $worksheet->id;

                    /*
                    $increment = 1000;

                    //This is the manager's rollup worksheet.  Comment this out here because in
                    //6.6 we are not allowing a manager to adjust an opportunity

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
                        $created_ids[] = $worksheet->id;
                        $increment += 500;
                    }
                    */
                }

                //This is the rollup worksheet for the manager
                $increment = 500;

                foreach($comm->my_managers as $manager_id)
                {
                    $worksheet = new Worksheet();
                    $worksheet->user_id = $manager_id;
                    $worksheet->timeperiod_id = $timeperiod_id;
                    $worksheet->forecast_type = 'Rollup';
                    $worksheet->related_id = $user_id;
                    $worksheet->related_forecast_type = 'Direct';
                    $worksheet->best_case = $best + $increment;
                    $worksheet->likely_case = $likely + $increment;
                    $worksheet->worst_case = $worst + $increment;
                    $worksheet->save();
                    $created_ids[] = $worksheet->id;
                    $increment += 100;
                }
            }
    }

    return $created_ids;

}


/**
 * getRelatedOpportunities
 *
 * Returns the opportunities assigned to the user for the given timeperiod
 *
 * @param $user_id
 * @param $timeperiod_id
 *
 * @return array Array of opportunities assigned to the user for the given timeperiod
 */
public static function getRelatedOpportunities($user_id, $timeperiod_id)
{
    global $db;
    $opps = array();
    $query = "SELECT id FROM opportunities WHERE assigned_user_id = '$user_id' AND timeperiod_id = '$timeperiod_id' AND deleted = 0 AND sales_stage != 'Closed Lost'";
    $result = $db->query($query, false, "Error fetching related opps for user");
    while (($row = $db->fetchByAssoc($result)) != null)
    {
        $opps[] = $row['id'];
    }
    return $opps;
}


}
