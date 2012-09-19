<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class ForecastsCommittedApi extends ModuleApi
{

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        $parentApi = array(
            'forecastsCommitted' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'committed'),
                'pathVars' => array('', ''),
                'method' => 'forecastsCommitted',
                'shortHelp' => 'A list of forecasts entries matching filter criteria',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastsCommitted',
            ),
            'forecastsCommit' => array(
                'reqType' => 'POST',
                'path' => array('Forecasts', 'committed'),
                'pathVars' => array('', ''),
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

        $db = DBManagerFactory::getInstance();

        $user_id = $current_user->id;
        if (isset($args['user_id']) && $args['user_id'] != $current_user->id) {
            $user_id = $args['user_id'];
            if (!User::isManager($current_user->id)) {
                $GLOBALS['log']->error(string_format($mod_strings['LBL_ERROR_NOT_MANAGER'], array($current_user->id, $user_id)));
                return array();
            }
        }

        $args['user_id'] = $user_id;

        $args['forecast_type'] = (isset($args['forecast_type'])) ? $args['forecast_type'] : (User::isManager($user_id) ? 'Rollup' : 'Direct');
        $args['timeperiod_id'] = (isset($args['timeperiod_id'])) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['include_deleted'] = (isset($args['show_deleted']) && $args['show_deleted'] === true);

        $obj = $this->getClass($args);
        return $obj->process();
    }


    public function forecastsCommit($api, $args)
    {
        $obj = $this->getClass($args);
        return $obj->save();
    }

    /**
     * Get the Committed Class
     *
     * @param array $args
     * @return SugarForecasting_Committed
     */
    protected function getClass($args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Committed.php';
        $klass = 'SugarForecasting_Committed';

        // check for a custom file exists
        $include_file = get_custom_file_if_exists($file);

        // if a custom file exists then we need to rename the class name to be Custom_
        if ($include_file != $file) {
            $klass = "Custom_" . $klass;
        }

        // include the class in since we don't have a auto loader
        require_once($include_file);
        // create the lass

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);

        return $obj;
    }

}
