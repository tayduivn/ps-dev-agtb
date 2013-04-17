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
require_once 'modules/Opportunities/Opportunity.php';

class SugarTestForecastUtilities
{
    private static $_createdForecasts = array();
    private static $timeperiod;

    /**
     * Utility method to setup Forecasts Data
     *
     * @param array $additional_config              A Key Value pair to set Forecast Config Settings
     */
    public static function setUpForecastConfig(array $additional_config = array())
    {
        SugarTestConfigUtilities::setConfig('Forecasts', 'is_setup', 1);
        SugarTestConfigUtilities::setConfig('Forecasts', 'forecast_by', 'products');
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_worksheet_likely', 1);
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_worksheet_best', 1);
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_worksheet_worst', 0);

        foreach($additional_config as $key => $value) {
            SugarTestConfigUtilities::setConfig('Forecasts', $key, $value);
        }
    }

    /**
     * Utility Method to Rest Forecast Config Data
     */
    public static function tearDownForecastConfig()
    {
        SugarTestConfigUtilities::resetConfig();
    }

    /**
     * @static
     * This is a static function to create a test Forecast instance
     * @param $timeperiod TimePeriod instance for Forecast
     * @param $user User assigned to Forecast
     * @return Forecast Mixed Forecast test instance
     */
    public static function createForecast($timeperiod, $user)
    {
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = false;
       
        $forecast = new Forecast();
        $forecast->timeperiod_id = $timeperiod->id;
        $forecast->best_case = 100;
        $forecast->likely_case = 100;
        $forecast->worst_case = 100;
        $forecast->forecast_type = 'Direct';
        $forecast->user_id = $user->id;
        $forecast->date_modified = db_convert("'" . $timedate->nowDb() . "'", 'datetime');
        $forecast->date_entered = $forecast->date_modified;
        $forecast->pipeline_opp_count = 0;
        $forecast->pipeline_amount = 0;
        $forecast->save();
        
        self::$_createdForecasts[$forecast->id] = $forecast;
        return $forecast;
    }

    /**
     * @static
     * This is a static function to remove all created test Forecast instance
     *
     */
    public static function removeAllCreatedForecasts()
    {
        $forecast_ids = array_keys(self::$_createdForecasts);
        $GLOBALS['db']->query('DELETE FROM forecasts WHERE id IN (\'' . implode("', '", $forecast_ids) . '\')');
        self::$_createdForecasts = array();
    }

    /**
     * @return TimePeriod
     */
    public static function getCreatedTimePeriod()
    {
        if (empty(self::$timeperiod)) {
            self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        }

        return self::$timeperiod;
    }

    public static function setTimePeriod($timeperiod)
    {
        self::$timeperiod = $timeperiod;
    }

    /**
     * This method will create a new user with opportunities with a variable number of items based on an array passed in
     */
    public static function createForecastUser(array $config = array())
    {

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
            ),
            'currency_id' => '-99'
        );

        $config = array_merge($default_config, $config);

        $return = array(
            'opportunities' => array(),
            'opportunities_total' => 0,
            'included_opps_totals' => array(
                'likely' => 0,
                'best' => 0,
                'worst' => 0,
            ),
            'opp_worksheets' => array()
        );


        if (empty($config['timeperiod_id'])) {
            $config['timeperiod_id'] = self::getCreatedTimePeriod()->id;
        }

        $user = SugarTestUserUtilities::createAnonymousUser();
        if (!empty($config['user']['reports_to'])) {
            $user->reports_to_id = $config['user']['reports_to'];
            $user->save();
        }

        $return['user'] = $user;

        if ($config['createOpportunities'] === true) {
            // create opportunities
            $included = 0;
            $opportunities = array();

            $forecast_likely_total = 0;
            $forecast_best_total = 0;
            $forecast_worst_total = 0;
            for ($x = 0; $config['opportunities']['total'] > $x; $x++) {
                $opp_amount = rand(1000, 2500);

                $include = 0;
                if ($included < $config['opportunities']['include_in_forecast']) {
                    $included++;
                    $include = 1;
                }

                // random date
                $int_date_closed = rand(strtotime(self::$timeperiod->start_date), strtotime(self::$timeperiod->end_date));
                $date_closed = date('Y-m-d', $int_date_closed);

                $opp = SugarTestOpportunityUtilities::createOpportunity();
                $opp->assigned_user_id = $user->id;
                $opp->amount = $opp_amount;
                $opp->best_case = ($opp_amount + 200);
                $opp->worst_case = ($opp_amount - 400);
                $opp->probability = rand(50, 90);
                $opp->sales_stage = 'Prospecting';
                $opp->commit_stage = ($include == 1) ? 'include' : 'exclude';
                $opp->date_closed = $date_closed;
                $opp->team_id = '1';
                $opp->team_set_id = '1';
                $opp->currency_id = $config['currency_id'];
                $opp->save();
                
                /*
                 * Since the products for opps are being autogenerated, they are being done when the $opp is 
                 * initially created, and thus missing stuff. We need to grab the product that is incomplete and finish
                 * setting it up.
                 */
                /* @var $product Product */
                $product = BeanFactory::getBean('Products');
                $product->retrieve_by_string_fields(array('opportunity_id'=>$opp->id));
                $product->name = $opp->name;
                $product->best_case = $opp->best_case;
                $product->likely_case = $opp->amount;
                $product->worst_case = $opp->worst_case;
                $product->cost_price = $opp->amount;
                $product->quantity = 1;
                $product->currency_id = $opp->currency_id;
                $product->base_rate = $opp->base_rate;
                $product->probability = $opp->probability;
                $product->date_closed = $opp->date_closed;
                $product->date_closed_timestamp = $opp->date_closed_timestamp;
                $product->assigned_user_id = $opp->assigned_user_id;
                $product->opportunity_id = $opp->id;
                $product->commit_stage = $opp->commit_stage;
                $product->save();

                if ($include == 1) {
                    $forecast_likely_total += $opp->amount;
                    $forecast_best_total += $opp->best_case;
                    $forecast_worst_total += $opp->worst_case;

                    $return['included_opps_totals']['likely'] += $opp->amount;
                    $return['included_opps_totals']['best'] += $opp->best_case;
                    $return['included_opps_totals']['worst'] += $opp->worst_case;
                    $return['included_opps_totals']['base_rate'] = $opp->base_rate;

                    if ($config['createWorksheet'] === true) {
                        $worksheet = BeanFactory::getBean('ForecastWorksheets');
                        $worksheet->retrieve_by_string_fields(
                            array(
                                'parent_id' => $opp->id,
                                'parent_type' => $opp->module_name,
                                'deleted' => 0,
                                'draft' => 1
                            )
                        );

                        $return['opp_worksheets'][] = $worksheet;
                    }
                }

                $return['opportunities_total'] += $opp_amount;

                $opportunities[] = $opp;

                $return['opportunities'][] = $opp;
            }

            if ($config['createQuota'] === true) {
                //create rollup quota too
                if(!empty($user->reports_to_id))
                {
                    $quota = SugarTestQuotaUtilities::createQuota($config['quota']['amount']);
                    $quota->user_id = $user->id;
                    $quota->created_by = 1;
                    $quota->modified_user_id = 1;
                    $quota->quota_type = 'Rollup';
                    $quota->timeperiod_id = $config['timeperiod_id'];
                    $quota->team_set_id = 1;
                    $quota->currency_id = $config['currency_id'];
                    $quota->committed = 1;
                    $quota->save();
                }
                $quota = SugarTestQuotaUtilities::createQuota($config['quota']['amount']);
                $quota->user_id = $user->id;
                $quota->created_by = 1;
                $quota->modified_user_id = 1;
                $quota->quota_type = 'Direct';
                $quota->timeperiod_id = $config['timeperiod_id'];
                $quota->team_set_id = 1;
                $quota->currency_id = $config['currency_id'];
                $quota->committed = 1;
                $quota->save();

                $return['quota'] = $quota;
            }

            if ($config['createForecast'] === true) {
                $forecast = self::createForecast(self::$timeperiod, $user);

                $forecast->best_case = $forecast_best_total;
                $forecast->worst_case = $forecast_worst_total;
                $forecast->likely_case = $forecast_likely_total;
                $forecast->forecast_type = "Direct";
                $forecast->opp_count = $config['opportunities']['include_in_forecast'];
                $forecast->pipeline_amount = $forecast->likely_case;
                $forecast->pipeline_opp_count = $forecast->opp_count;
                $forecast->currency_id = $config['currency_id'];
                $forecast->save();

                // roll forecast up to manager
                /* @var $mgr_worksheet ForecastManagerWorksheet */
                $mgr_worksheet = BeanFactory::getBean("ForecastManagerWorksheets");
                $mgr_worksheet->reporteeForecastRollUp($user, $forecast->toArray());

                $return['forecast'] = $forecast;
            }
            if ($config['createWorksheet'] === true) {
                $worksheet = SugarTestManagerWorksheetUtilities::createWorksheet();
                $worksheet->assigned_user_id = (empty($user->reports_to_id)) ? $user->id : $user->reports_to_id;
                $worksheet->timeperiod_id = $config['timeperiod_id'];
                $worksheet->best_case = $forecast_best_total;
                $worksheet->likely_case = $forecast_likely_total;
                $worksheet->worst_case = $forecast_worst_total;
                $worksheet->best_case_adjusted = $forecast_best_total + 100;
                $worksheet->likely_case_adjusted = $forecast_likely_total + 100;
                $worksheet->worst_case_adjusted = $forecast_worst_total + 100;
                $worksheet->currency_id = $config['currency_id'];
                $worksheet->quota = $config['quota']['amount'];
                $worksheet->save();

                $return['worksheet'] = $worksheet;
            }
        }

        return $return;
    }

    /**
     *
     * @param array $manager        A manager created from createForecastUser
     * @param $user                 N+ number of users that report to $manager to create in the forecast
     * @return Forecast
     */
    public static function createManagerRollupForecast($manager, $user)
    {
        $users = array($user);
                
        $numargs = func_num_args();
        if ($numargs > 2) {
            for ($i = 2; $i < $numargs; $i++) {
                $users[] = func_get_arg($i);
            }
        }
        $tmpForecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, $manager['user']);
        $tmpForecast->best_case = $manager['forecast']->best_case;
        $tmpForecast->worst_case = $manager['forecast']->worst_case;
        $tmpForecast->likely_case = $manager['forecast']->likely_case;
        $tmpForecast->forecast_type = 'Rollup';
        $pipelineAmount = 0;
        $pipelineCount = 0;

        //grab the users
        foreach($users as $user) {
            if($user['user']->reports_to_id == $manager['user']->id) {
                $tmpForecast->best_case += $user['forecast']->best_case;
                $tmpForecast->worst_case += $user['forecast']->worst_case;
                $tmpForecast->likely_case += $user['forecast']->likely_case;
                $tmpForecast->opp_count += $user['forecast']->opp_count;
                $pipelineAmount += $user['forecast']->pipeline_amount;
                $pipelineCount += $user['forecast']->pipeline_opp_count;
            }
        }
        //finish off with the manager
        $tmpForecast->best_case += $manager['forecast']->best_case;
        $tmpForecast->worst_case += $manager['forecast']->worst_case;
        $tmpForecast->likely_case += $manager['forecast']->likely_case;
        $tmpForecast->opp_count += $manager['forecast']->opp_count;
        $GLOBALS["log"]->log("Pipeline Amounts: " . $pipelineAmount . ", " . $pipelineCount);
        $tmpForecast->pipeline_amount = $pipelineAmount + $manager['forecast']->pipeline_amount;
        $tmpForecast->pipeline_opp_count = $pipelineCount + $manager['forecast']->pipeline_opp_count;
        
        $tmpForecast->save();

        // roll forecast up to manager
        /* @var $mgr_worksheet ForecastManagerWorksheet */
        $mgr_worksheet = BeanFactory::getBean("ForecastManagerWorksheets");
        $mgr_worksheet->reporteeForecastRollUp($manager['user'], $tmpForecast->toArray());

        return $tmpForecast;
    }
    
    /**
     * Creates direct rep forecasts given a user that has opportunities
     * 
     * @param array User array defined above.
     * @return object Updated forecast.
     */
    public static function createRepDirectForecast($user)
    {
        $tmpForecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, $user["user"]);
        $tmpForecast->best_case = 0;
        $tmpForecast->worst_case = 0;
        $tmpForecast->likely_case = 0;
        $tmpForecast->opp_count = 0;
        $closedAmount = 0;
        $closedCount = 0;
        
        //loop over opportunities
        foreach($user["opportunities"] as $opp){
            $tmpForecast->best_case += $opp->best_case;
            $tmpForecast->worst_case += $opp->worst_case;
            $tmpForecast->likely_case += $opp->amount;
            $tmpForecast->opp_count++;
            
            if($opp->sales_stage == Opportunity::STAGE_CLOSED_WON || $opp->sales_stage == Opportunity::STAGE_CLOSED_LOST){
                $closedCount++;
                $closedAmount += $opp->amount;
            }
        }
        
        $tmpForecast->calculatePipelineData($closedAmount, $closedCount);
        $tmpForecast->save();
        return $tmpForecast;
    }

    public static function cleanUpCreatedForecastUsers()
    {
        if (!empty(self::$timeperiod)) {
            SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
            self::$timeperiod = null;
        }
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestQuotaUtilities::setCreatedUserIds(SugarTestUserUtilities::getCreatedUserIds());
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestManagerWorksheetUtilities::removeAllCreatedWorksheets();
    }


    /**
     * This is a helper function for tests so that we convert values to the expected amount returned from the API
     *
     * @static
     * @param $amount The amount to format to the test format
     * @param null $user The user to use for currency id and formatting; defaults to using system locale settings
     */
    public static function formatTestNumber($amount, $user=null)
    {
         if(is_null($user))
         {
             return number_format($amount, 6, '.', '');
         }

         return SugarCurrency::formatAmount($amount,
            $user->getPreference('currency'),
            $user->getPreference('default_currency_significant_digits'),
            $user->getPreference('default_number_grouping_seperator')
         );
    }
}
