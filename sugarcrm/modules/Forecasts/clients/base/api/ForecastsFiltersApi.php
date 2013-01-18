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

require_once('clients/base/api/ModuleApi.php');

class ForecastsFiltersApi extends ModuleApi
{

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi = array(
            'timeperiod' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'timeperiod'),
                'pathVars' => array('', ''),
                'method' => 'timeperiod',
                'shortHelp' => 'forecast timeperiod',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastFiltersTimePeriodGet.html',
            ),
            'reportees' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'reportees', '?'),
                'pathVars' => array('', '', 'user_id'),
                'method' => 'getReportees',
                'shortHelp' => 'Gets reportees to a user by id',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastFiltersReporteesGet.html',
            )
        );
        return $parentApi;
    }

    /**
     * Return the dom of the current timeperiods.
     *
     * //TODO, move this logic to store the values in a custom language file that contains the timeperiods for the Forecast module
     *
     * @param array $api
     * @param array $args
     * @return array
     */
    public function timeperiod($api, $args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Filter/TimePeriodFilter.php';
        $klass = 'SugarForecasting_Filter_TimePeriodFilter';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj->process();
    }

    /**
     * Retrieve an array of Users and their tree state that report to the user that was passed in
     *
     * @param $api
     * @param $args
     * @return array|string
     */
    public function getReportees($api, $args)
    {
        $args['user_id'] = isset($args["user_id"]) ? $args["user_id"] : $GLOBALS["current_user"]->id;

        // base file and class name
        $file = 'include/SugarForecasting/ReportingUsers.php';
        $klass = 'SugarForecasting_ReportingUsers';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj->process();
    }

}
