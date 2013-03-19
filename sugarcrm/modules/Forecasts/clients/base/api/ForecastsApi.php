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

require_once('include/api/SugarApi.php');

class ForecastsApi extends SugarApi
{
    public function registerApiRest()
    {
        $parentApi = array(
            'init' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','init'),
                'pathVars' => array(),
                'method' => 'forecastsInitialization',
                'shortHelp' => 'Returns forecasts initialization data and additional user data',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastsApiInitGet.html',
            ),
            'selecteUserObject' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'user', '?'),
                'pathVars' => array('', '', 'user_id'),
                'method' => 'retrieveSelectedUser',
                'shortHelp' => 'Returns selectedUser object for given user',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastsApiUserGet.html',
            ),
            'timeperiod' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'enum', 'selectedTimePeriod'),
                'pathVars' => array('', '', ''),
                'method' => 'timeperiod',
                'shortHelp' => 'forecast timeperiod',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastApiTimePeriodGet.html',
            ),
            'reportees' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'reportees', '?'),
                'pathVars' => array('', '', 'user_id'),
                'method' => 'getReportees',
                'shortHelp' => 'Gets reportees to a user by id',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastApiReporteesGet.html',
            ),
            'list' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts',),
                'pathVars' => array('module'),
                'method' => 'returnEmptySet',
                'shortHelp' => 'Forecast list endpoint returns an empty set',
                'longHelp' => 'include/api/help/module_record_favorite_put_help.html',
            ),
        );
        return $parentApi;
    }

    /**
     * Returns an empty set for favorites and filter because those operations on forecasts are impossible
     * @param $api
     * @param $args
     * @return array
     */
    public function returnEmptySet($api, $args) {
        return array('next_offset' => -1, 'records' => array());
    }
    /**
     * Returns the initialization data for the module including currently logged-in user data,
     * timeperiods, and admin config settings
     *
     * @param $api
     * @param $args
     * @return array
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastsInitialization($api, $args) {
        global $current_user;

        if(!SugarACL::checkAccess('Forecasts', 'access')) {
            throw new SugarApiExceptionNotAuthorized();
        }

        $returnInitData = array();
        $defaultSelections = array();

        // Add Forecasts-specific items to returned data
        $returnInitData["initData"]["userData"]['isManager'] = User::isManager($current_user->id);
        $returnInitData["initData"]["userData"]['showOpps'] = false;
        $returnInitData["initData"]["userData"]['first_name'] = $current_user->first_name;
        $returnInitData["initData"]["userData"]['last_name'] = $current_user->last_name;

        // TODO: These should probably get moved in with the config/admin settings, or by themselves since this file will probably going away.
        $id = TimePeriod::getCurrentId();
        if(!empty($id)) {
            $timePeriod = new TimePeriod();
            $timePeriod->retrieve($id);
            $defaultSelections["timeperiod_id"] = array(
                'id' => $id,
                'label' => $timePeriod->name
            );
        } else {
            $defaultSelections["timeperiod_id"]["id"] = '';
            $defaultSelections["timeperiod_id"]["label"] = '';
        }

        // INVESTIGATE: these need to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
        $admin = BeanFactory::getBean("Administration");
        $forecastsSettings = $admin->getConfigForModule("Forecasts", "base");

        $returnInitData["initData"]['forecasts_setup'] = (isset($forecastsSettings['is_setup'])) ? $forecastsSettings['is_setup'] : 0;

        $defaultSelections["ranges"] = array("include");
        $defaultSelections["group_by"] = 'forecast';
        $defaultSelections["dataset"] = 'likely';

        // push in defaultSelections
        $returnInitData["defaultSelections"] = $defaultSelections;

        $returnInitData["forecastsJavascript"] = getVersionedPath(sugar_cached('include/javascript/sidecar_forecasts.js'));

        return $returnInitData;
    }

    /**
     * Retrieves user data for a given user id
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveSelectedUser($api, $args) {
        global $locale;
        $uid = $args['user_id'];
        $user = BeanFactory::getBean('Users', $uid);
        $data = array();
        $data['id'] = $user->id;
        $data['user_name'] = $user->user_name;
        $data['full_name'] = $locale->getLocaleFormattedName($user->first_name,$user->last_name);
        $data['first_name'] = $user->first_name;
        $data['last_name'] = $user->last_name;
        $data['isManager'] = User::isManager($user->id);
        return $data;
    }

    /**
     * Return the dom of the current timeperiods.
     *
     * //TODO, move this logic to store the values in a custom language file that contains the timeperiods for the Forecast module
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return array of timeperiods
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
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return array|string of users that reported to specified/current user
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
