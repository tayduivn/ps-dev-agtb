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

    public function forecastsCommitted($api, $args)
    {
        global $current_user, $mod_strings, $current_language;
        $mod_strings = return_module_language($current_language, 'Forecasts');

        $query = "SELECT * FROM forecasts WHERE user_id = '{$current_user->id}' AND deleted = 0 AND forecast_type='Direct' ORDER BY date_entered desc";
        $results = $GLOBALS['db']->query($query);
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
            }

            //Get the remaining history items
            if(!empty($forecasts))
            {
                $history = array();

                //Calculate the difference between $latest and $previous
                $history[] = $this->createHistoryLog($latest, $previous);

                //Calculate the rest
                foreach($forecasts as $forecast)
                {
                    $history[] = $this->createHistoryLog($forecast, $previous);
                    $previous = $forecast;
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
     * @param $before
     * @param $after
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

        $args = array();
        if($best_changed && $likely_changed)
        {
            $args[] = $mod_strings[$best_direction];
            $args[] = abs($best_difference);
            $args[] = $current['best_case'];
            $args[] = $mod_strings[$likely_direction];
            $args[] = $abs($likely_difference);
            $args[] = $current['likely_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_BOTH_CHANGED'], $args);
        } else if (!$best_changed && $likely_changed) {
            $args[] = $mod_strings[$likely_direction];
            $args[] = $abs($likely_difference);
            $args[] = $current['likely_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_LIKELY_CHANGED'], $args);
        } else if ($best_changed && !$likely_changed) {
            $args[] = $mod_strings[$best_direction];
            $args[] = abs($best_difference);
            $args[] = $current['best_case'];
            $text = string_format($mod_strings['LBL_COMMITTED_HISTORY_BEST_CHANGED'], $args);
        } else {
            $text = $mod_strings['LBL_COMMITTED_HISTORY_NONE_CHANGED'];
        }

        $timedate = TimeDate::getInstance();
        $current_date = $timedate->fromDb($current['date_entered']);
        $previous_date = $timedate->fromDb($previous['date_entered']);
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
