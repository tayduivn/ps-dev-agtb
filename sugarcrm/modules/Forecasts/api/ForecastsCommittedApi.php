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
                'shortHelp' => 'Most recent committed forecast entry',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastsCommitted',
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

        $query = "SELECT * FROM forecasts WHERE user_id = '{$current_user->id}' AND forecast_type='Direct' AND deleted = 0 ORDER BY date_modified desc";

        //Get the last 6
        $results = $GLOBALS['db']->limitQuery($query, 0, 6);
        $forecasts = array();
        while(($row = $GLOBALS['db']->fetchByAssoc($results)))
        {
            $forecasts[$row['id']] = $row;
        }

        if(!empty($forecasts))
        {
            $latest = array_shift($forecasts);

            //Get the previous item
            if(!empty($forecasts))
            {
                $previous = array_shift($forecasts);
                $previous['text'] = string_format($mod_strings['LBL_PREVIOUS_COMMIT'], array($timedate->asUser($timedate->fromDb($previous['date_entered']))));
            }

            //Get the remaining history items
            if(!empty($forecasts))
            {
                $history = array();

                //Calculate the difference between $latest and $previous
                $history[] = $this->createHistoryLog($latest, $previous);

                $last = $previous;
                //Calculate the rest
                foreach($forecasts as $forecast)
                {
                    $history[] = $this->createHistoryLog($forecast, $last);
                    $last = $forecast;
                }
            }

            return array(
                'latest' => $latest,
                'previous' => $previous,
                'history' => $history
            );
        }

        return array('latest' => array(), 'previous'=>array(), 'history' => array());
    }

    /**
     * createHistoryLog
     *
     * @param $current Array The row entry representing the updated forecast
     * @param $previous Array The row entry representing the forecast entry prior to the updated entry
     * @return Array An array entry containing text and modified keys with text representing the text label and modified the timestamp label
     */
    protected function createHistoryLog($current, $previous)
    {
        global $mod_strings;
        $best_difference = $current['best_case'] - $previous['best_case'];
        $best_changed = $best_difference != 0;
        $best_direction = $best_difference > 0 ? 'LBL_UP' : ($best_difference < 0 ? 'LBL_DOWN' : '');

        $likely_difference = $current['likely_case'] - $previous['likely_case'];
        $likely_changed = $likely_difference != 0;
        $likely_direction = $likely_difference > 0 ? 'LBL_UP' : ($likely_difference < 0 ? 'LBL_DOWN' : '');


        if($best_changed && $likely_changed)
        {
            $args = array();
            $args[] = $mod_strings[$best_direction];
            $args[] = abs($best_difference);
            $args[] = $current['best_case'];
            $args[] = $mod_strings[$likely_direction];
            $args[] = $abs($likely_difference);
            $args[] = $current['likely_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_BOTH_CHANGED'], $args);
        } else if (!$best_changed && $likely_changed) {
            $args = array();
            $args[] = $mod_strings[$likely_direction];
            $args[] = $abs($likely_difference);
            $args[] = $current['likely_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_LIKELY_CHANGED'], $args);
        } else if ($best_changed && !$likely_changed) {
            $args = array();
            $args[] = $mod_strings[$best_direction];
            $args[] = abs($best_difference);
            $args[] = $current['best_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_BEST_CHANGED'], $args);
        } else {
            $text = $mod_strings['LBL_COMMITTED_HISTORY_NONE_CHANGED'];
        }

        $timedate = TimeDate::getInstance();
        $current_date = $timedate->fromDb($current['date_modified']);
        $previous_date = $timedate->fromDb($previous['date_modified']);
        $interval = $current_date->diff($previous_date);

        if($interval->m < 2)
        {
            $modified = string_format($mod_strings['LBL_COMMITTED_THIS_MONTH'], array($timedate->asUser($previous_date)));
        } else {
            $modified = string_format($mod_strings['LBL_COMMITTED_MONTHS_AGO'], array($interval['months'], $timedate->asUser($previous_date)));
        }

        return array('text'=>$text, 'modified'=>$modified);

    }

}
