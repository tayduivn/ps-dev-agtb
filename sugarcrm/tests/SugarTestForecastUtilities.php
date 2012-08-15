<?php
//FILE SUGARCRM flav=pro ONLY
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
 * SugarTestForecastUtilities.php
 *
 * This is a test class to create test Forecast instances
 */

require_once 'modules/Forecasts/Forecast.php';

class SugarTestForecastUtilities
{
    private static $_createdForecasts = array();

    /**
     * @static
     * This is a static function to create a test Forecast instance
     * @param $timeperiod TimePeriod instance for Forecast
     * @param $user User assigned to Forecast
     * @return Forecast Mixed Forecast test instance
     */
    public static function createForecast($timeperiod, $user)
    {
        $forecast = new Forecast();
        $forecast->timeperiod_id = $timeperiod->id;
        $forecast->best_case = 100;
        $forecast->likely_case = 100;
        $forecast->worst_case = 100;
        $forecast->forecast_type = 'DIRECT';
        $forecast->user_id = $user->id;
        $forecast->date_modified = db_convert("'".TimeDate::getInstance()->nowDb()."'", 'datetime');
        $forecast->date_entered = $forecast->date_modified;
        $forecast->save();
        self::$_createdForecasts[] = $forecast;
        return $forecast;
    }

    /**
     * @static
     * This is a static function to remove all created test Forecast instance
     *
     */
    public static function removeAllCreatedForecasts()
    {
        $forecast_ids = self::getCreatedForecastIds();
        $GLOBALS['db']->query('DELETE FROM forecasts WHERE id IN (\'' . implode("', '", $forecast_ids) . '\')');
    }

    /**
     * @static
     * This is a static function to return all ids of created Forecast instances
     *
     * @return array of ids of the Forecast instances created
     */
    public static function getCreatedForecastIds()
    {
        $forecast_ids = array();
        foreach (self::$_createdForecasts as $fs)
        {
            $forecast_ids[] = $fs->id;
        }
        return $forecast_ids;
    }

    protected static $timeperiod;

    /**
     * @return TimePeriod
     */
    public static function getCreatedTimePeriod()
    {
        if(empty(self::$timeperiod)) {
            self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        }

        return self::$timeperiod;
    }

    /**
     * This method will create a new user with opportunities with a variable number of items based on an array passed in
     */
    public static function createForecastUser(array $config = array()) {

        $default_config = array(
            'timeperiod_id' => null,
            'user' => array(
                'reports_to' => null,
            ),
            'createOpportunities' => true,
            'opportunities' => array(
                'total' => 5,
                'include_in_forecast' => 3
            ),
            'createForecast' => true,
            'createWorksheet' => true,
            'createQuota' => true,
            'quota' => array(
                'amount' => 2000
            )
        );

        $config = array_merge($default_config, $config);


        $return = array(
            'opportunities' => array(),
            'opportunities_total' => 0
        );


        if(empty($config['timeperiod_id'])) {
            $config['timeperiod_id'] = self::getCreatedTimePeriod()->id;
        }

        $user = SugarTestUserUtilities::createAnonymousUser();
        if(!empty($config['user']['reports_to'])) {
            $user->reports_to_id = $config['user']['reports_to'];
            $user->save();
        }

        $return['user'] = $user;

        if($config['createOpportunities'] === true) {
            // create opportunities
            $included = 0;
            $opportunities = array();

            $forecast_likely_total = 0;
            $forecast_best_total = 0;
            $forecast_worst_total = 0;
            for($x=0;$config['opportunities']['total']>$x;$x++) {
                $opp_amount = rand(1000, 2500);

                $include = 0;
                if($included < $config['opportunities']['include_in_forecast']) {
                    $included++;
                    $include = 1;
                }

                // random date
                $int_date_closed = rand(strtotime(self::$timeperiod->start_date),strtotime(self::$timeperiod->end_date));
                $date_closed = date('Y-m-d', $int_date_closed);


                $opp = SugarTestOpportunityUtilities::createOpportunity();
                $opp->assigned_user_id = $user->id;
                $opp->timeperiod_id = $config['timeperiod_id'];
                $opp->amount = $opp_amount;
                $opp->likely_case = ($opp_amount-200);
                $opp->best_case = ($opp_amount+200);
                $opp->worst_case = ($opp_amount-400);
                $opp->forecast = $include;
                $opp->probability = rand(50, 90);
                $opp->date_closed = $date_closed;
                $opp->team_id = '1';
                $opp->team_set_id = '1';
                $opp->save();

                if($include) {
                    $forecast_likely_total += $opp->likely_case;
                    $forecast_best_total += $opp->best_case;
                    $forecast_worst_total += $opp->worst_case;
                    $return['opportunities_total'] += $opp_amount;
                }

                $opportunities[] = $opp;

                $return['opportunities'][] = $opp;
            }

            if($config['createForecast'] === true) {
                $forecast = self::createForecast(self::$timeperiod, $user);

                $forecast->best_case = $forecast_best_total;
                $forecast->worst_case = $forecast_worst_total;
                $forecast->likely_case = $forecast_likely_total;
                $forecast->save();

                $return['forecast'] = $forecast;
            }

            if($config['createQuota'] === true) {
                $quota = SugarTestQuotaUtilities::createQuota($config['quota']['amount']);
                $quota->user_id = $user->id;
                $quota->quota_type = (empty($user->reports_to_id)) ? "Direct" : "Rollup";
                $quota->timeperiod_id = $config['timeperiod_id'];
                $quota->team_set_id = 1;
                $quota->save();

                $return['quota'] = $quota;
            }

            if($config['createWorksheet'] === true) {
                $worksheet = SugarTestWorksheetUtilities::createWorksheet();
                $worksheet->user_id = (empty($user->reports_to_id)) ? $user->id : $user->reports_to_id;
                $worksheet->related_id = $user->id;
                $worksheet->forecast_type = (empty($user->reports_to_id)) ? "Direct" : "Rollup";
                $worksheet->timeperiod_id = $config['timeperiod_id'];
                $worksheet->best_case = $forecast_best_total+100;
                $worksheet->likely_case = $forecast_likely_total+100;
                $worksheet->worst_case = $forecast_likely_total+100;
                $worksheet->forecast = 1;
                $worksheet->save();

                $return['worksheet'] = $worksheet;
            }
        }

        return $return;
    }

    public static function cleanUpCreatedForecastUsers()
    {
        if(!empty(self::$timeperiod)) {
            SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        }
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

}