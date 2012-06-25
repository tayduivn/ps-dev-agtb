<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/api/ModuleApi.php');

class ForecastsCommittedApi extends ModuleApi {

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        $parentApi= array (
            'forecastsCommitted' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','committed'),
                'pathVars' => array('',''),
                'method' => 'forecastsCommitted',
                'shortHelp' => 'A list of forecasts entries matching filter criteria',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastsCommitted',
            ),
            'forecastsCommit' => array(
                'reqType' => 'POST',
                'path' => array('Forecasts','committed'),
                'pathVars' => array('',''),
                'method' => 'forecastsCommit',
                'shortHelp' => 'Commit a forecast',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastsCommit',
            )
        );
        return $parentApi;
    }

    /**
     * forecastsCommitted
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function forecastsCommitted($api, $args)
    {
        global $current_user, $mod_strings, $current_language;
        $mod_strings = return_module_language($current_language, 'Forecasts');

        $timedate = TimeDate::getInstance();

        $forecast_type = 'Direct';
        if(isset($args['forecast_type']))
        {
           $forecast_type = clean_string($args['forecast_type']);
        }

        $user_id = $current_user->id;
        if(isset($args['user_id']) && $args['user_id'] != $current_user->id)
        {
           $user_id = clean_string($args['user_id']);
           if(!User::isManager($current_user->id))
           {
               $GLOBALS['log']->error(string_format($mod_strings['LBL_ERROR_NOT_MANAGER'], array($current_user->id, $user_id)));
               return array();
           }
        }

        $timeperiod_id = TimePeriod::getCurrentId();
        if(isset($args['timeperiod_id']))
        {
           $timeperiod_id = clean_string($args['timeperiod_id']);
        }

        $include_deleted = false;
        if(isset($args['show_deleted']) && $args['show_deleted'] === true)
        {
           $included_deleted = true;
        }

        $where = "forecasts.user_id = '{$user_id}' AND forecasts.forecast_type='{$forecast_type}' AND forecasts.timeperiod_id = '{$timeperiod_id}'";

        //$where =  "forecasts.forecast_type='{$forecast_type}' AND forecasts.timeperiod_id = '{$timeperiod_id}'";

        $order_by = 'forecasts.date_entered DESC';
        if(isset($args['order_by']))
        {
            $order_by = clean_string($args['order_by']);
        }

        $bean = new Forecast();
        $query = $bean->create_new_list_query($order_by, $where, array(), array(), $include_deleted);
        $results = $GLOBALS['db']->query($query);

        $forecasts = array();
        while(($row = $GLOBALS['db']->fetchByAssoc($results)))
        {
            $forecasts[] = $row;
        }

        return $forecasts;
    }


    public function forecastsCommit($api, $args)
    {
        global $current_user;
        $forecast = new Forecast();
        $forecast->user_id = $current_user->id;
        $forecast->timeperiod_id = $args['timeperiod_id'];
        $forecast->best_case = $args['best_case'];
        $forecast->likely_case = $args['likely_case'];
        $forecast->forecast_type = $args['forecast_type'];
        $forecast->opp_count = $args['opp_count'];
        if($args['amount'] != 0 && $args['opp_count'] != 0)
        {
            $forecast->opp_weigh_value = $args['amount'] / $args['opp_count'];
        }
        $forecast->save();
    }

}
