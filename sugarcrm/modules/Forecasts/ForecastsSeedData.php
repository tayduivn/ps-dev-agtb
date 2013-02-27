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
 * Handles populating seed data for Forecasts module
 */
class ForecastsSeedData {

    /**
     * @static
     *
     * @param Array $timeperiods Array of $timeperiod instances to build forecast data for
     */
    public static function populateSeedData($timeperiods)
    {

        require_once('modules/Forecasts/Common.php');

        global $timedate, $current_user, $app_list_strings;

        $user = BeanFactory::getBean('Users');
        $comm = new Common();
        $commit_order=$comm->get_forecast_commit_order();

        foreach ($timeperiods as $timeperiod_id=>$timeperiod) {

            foreach($commit_order as $commit_type_array) {
                //create forecast schedule for this timeperiod record and user.
                //create forecast schedule using this record becuse there will be one
                //direct entry per user, and some user will have a Rollup entry too.
                if ($commit_type_array[1] == 'Direct') {

                    //commit a direct forecast for this user and timeperiod.
                    $forecastopp = BeanFactory::getBean('ForecastOpportunities');
                    $forecastopp->current_timeperiod_id = $timeperiod_id;
                    $forecastopp->current_user_id = $commit_type_array[0];
                    $opp_summary_array= $forecastopp->get_opportunity_summary(false);

                    if($opp_summary_array['OPPORTUNITYCOUNT'] == 0)
                    {
                        continue;
                    }

                    $multiplier = mt_rand(1,6);

                    /* @var $quota Quota */
                    $quota = BeanFactory::getBean('Quotas');
                    $quota->timeperiod_id=$timeperiod_id;
                    $quota->user_id = $commit_type_array[0];
                    $quota->quota_type='Direct';
                    $quota->currency_id=-99;
                    $ratio = array('.8','1','1.2','1.4');
                    $key = array_rand($ratio);
                    $quota->amount = ($opp_summary_array['TOTAL_AMOUNT'] * $ratio[$key]) / 2;
                    $quota->amount_base_currency = $quota->amount;
                    $quota->committed=1;
                    $quota->set_created_by = false;
                    if ($commit_type_array[0] == 'seed_sarah_id' || $commit_type_array[0] == 'seed_will_id' || $commit_type_array[0] == 'seed_jim_id')
                        $quota->created_by = 'seed_jim_id';
                    else if ($commit_type_array[0] == 'seed_sally_id' || $commit_type_array[0] == 'seed_max_id')
                        $quota->created_by = 'seed_sarah_id';
                    else if ($commit_type_array[0] == 'seed_chris_id')
                        $quota->created_by = 'seed_will_id';
                    else
                        $quota->created_by = $current_user->id;

                    $quota->save();

                    if(!$user->isManager($commit_type_array[0])) {
                        /* @var $quotaRollup Quota */
                        $quotaRollup = BeanFactory::getBean('Quotas');
                        $quotaRollup->timeperiod_id=$timeperiod_id;
                        $quotaRollup->user_id = $commit_type_array[0];
                        $quotaRollup->quota_type='Rollup';
                        $quota->currency_id=-99;
                        $quotaRollup->amount = ($opp_summary_array['TOTAL_AMOUNT'] * $ratio[$key]) / 2;
                        $quotaRollup->amount_base_currency = $quotaRollup->amount;
                        $quotaRollup->committed=1;
                        $quotaRollup->set_created_by = false;
                        if ($commit_type_array[0] == 'seed_sarah_id' || $commit_type_array[0] == 'seed_will_id' || $commit_type_array[0] == 'seed_jim_id')
                            $quotaRollup->created_by = 'seed_jim_id';
                        else if ($commit_type_array[0] == 'seed_sally_id' || $commit_type_array[0] == 'seed_max_id')
                            $quotaRollup->created_by = 'seed_sarah_id';
                        else if ($commit_type_array[0] == 'seed_chris_id')
                            $quotaRollup->created_by = 'seed_will_id';
                         else
                             $quotaRollup->created_by = $current_user->id;

                        $quotaRollup->save();
                    }

                    /* @var $forecast Forecast */
                    $forecast = BeanFactory::getBean('Forecasts');
                    $forecast->timeperiod_id=$timeperiod_id;
                    $forecast->user_id =  $commit_type_array[0];
                    $forecast->opp_count= $opp_summary_array['OPPORTUNITYCOUNT'];
                    $forecast->opp_weigh_value=$opp_summary_array['WEIGHTEDVALUENUMBER'];
                    $forecast->best_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + (($multiplier+1) * 100);
                    $forecast->worst_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + ($multiplier * 100);
                    $forecast->likely_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + (($multiplier-1) * 100);
                    $forecast->forecast_type='Direct';
                    $forecast->date_committed = $timedate->asDb($timedate->getNow()->modify("-1 day"));
                    $forecast->calculatePipelineData($opp_summary_array['CLOSED_AMOUNT'], $opp_summary_array['CLOSED_OPP_COUNT']);             
                    $forecast->save();
                    
                    self::createManagerWorksheet($commit_type_array[0], $forecast->toArray());

                    //Create a previous forecast to simulate change
                    /* @var $forecast2 Forecast */
                    $forecast2 = BeanFactory::getBean('Forecasts');
                    $forecast2->timeperiod_id=$timeperiod_id;
                    $forecast2->user_id =  $commit_type_array[0];
                    $forecast2->opp_count= $opp_summary_array['OPPORTUNITYCOUNT'];
                    $forecast2->opp_weigh_value=$opp_summary_array['WEIGHTEDVALUENUMBER'];
                    $forecast2->best_case=$forecast->best_case - 100;
                    $forecast2->worst_case=$forecast->worst_case - 100;
                    $forecast2->likely_case=$forecast->likely_case - 100;
                    $forecast2->forecast_type='Direct';
                    $forecast2->date_committed = $timedate->asDb($timedate->getNow());
                    $forecast2->save();

                    self::createManagerWorksheet($commit_type_array[0], $forecast2->toArray());

                } else {
                    //create where clause....
                    $where  = " users.deleted=0 ";
                    $where .= " AND (users.id = '$commit_type_array[0]'";
                    $where .= " or users.reports_to_id = '$commit_type_array[0]')";
                    //Get the forecasts created by the direct reports.
                    /* @var $DirReportsFocus ForecastDirectReports */
                    $DirReportsFocus = BeanFactory::getbean('ForecastDirectReports');
                    $DirReportsFocus->current_user_id=$commit_type_array[0];
                    $DirReportsFocus->current_timeperiod_id=$timeperiod_id;
                    $DirReportsFocus->compute_rollup_totals('',$where,false);

                    $multiplier = mt_rand(1,6);

                    /* @var $quota Quota */
                    $quota = BeanFactory::getBean('Quotas');
                    $quota->timeperiod_id=$timeperiod_id;
                    $quota->user_id = $commit_type_array[0];
                    $quota->quota_type='Rollup';
                    $quota->currency_id=-99;
                    $quota->amount=$quota->getGroupQuota($timeperiod_id, false, $commit_type_array[0]);
                    if (!isset($quota->amount)) $quota->amount = $multiplier * 1000;
                    $quota->amount_base_currency=$quota->getGroupQuota($timeperiod_id, false, $commit_type_array[0]);
                    if (!isset($quota->amount_base_currency)) $quota->amount_base_currency = $quota->amount;
                    $quota->committed=1;
                    $quota->save();

                    /* @var $forecast Forecast */
                    $forecast = BeanFactory::getBean('Forecasts');
                    $forecast->timeperiod_id=$timeperiod_id;
                    $forecast->user_id =  $commit_type_array[0];
                    $forecast->opp_count= $DirReportsFocus->total_opp_count;
                    $forecast->opp_weigh_value=$DirReportsFocus->total_weigh_value_number;
                    $forecast->likely_case=$DirReportsFocus->total_weigh_value_number + (($multiplier+1) * 100);
                    $forecast->best_case=$DirReportsFocus->total_weigh_value_number + ($multiplier * 100);
                    $forecast->worst_case=$DirReportsFocus->total_weigh_value_number + (($multiplier-1) * 100);
                    $forecast->forecast_type='Rollup';
                    $forecast->pipeline_opp_count = $DirReportsFocus->pipeline_opp_count;
                    $forecast->pipeline_amount = $DirReportsFocus->pipeline_amount;
                    $forecast->date_entered = $timedate->to_display_date_time(date($timedate->get_db_date_time_format(), time()), true);
                    $forecast->save();

                    self::createManagerWorksheet($commit_type_array[0], $forecast->toArray());

                }
            }
        }

        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');

        // TODO-sfa - remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        // this locks the forecasts ranges configs if the apps is installed with demo data and already has commits
        $admin->saveSetting('Forecasts', 'has_commits', 1, 'base');
    }

    protected static function createManagerWorksheet($user_id, $data)
    {
        /* @var $user User */
        $user = BeanFactory::getBean('Users', $user_id);
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->reporteeForecastRollUp($user, $data);
    }
}
