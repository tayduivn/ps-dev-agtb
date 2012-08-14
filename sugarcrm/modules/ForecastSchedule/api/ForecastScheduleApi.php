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

class ForecastScheduleApi extends ModuleApi {

    /**
     *
     * @return array Array of api definitions for ForecastSchedule module
     */
    public function registerApiRest()
    {

        $parentApi= array (
            'forecastSchedule' => array(
                'reqType' => 'GET',
                'path' => array('ForecastSchedule'),
                'pathVars' => array('',''),
                'method' => 'forecastSchedule',
                'shortHelp' => 'Returns a collection of ForecastSchedule models',
                'longHelp' => 'include/api/html/modules/ForecastSchedule/ForecaseScheduleApi.html#forecastSchedule',
            ),
            'forecastScheduleSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastSchedule','?'),
                'pathVars' => array('module','record'),
                'method' => 'forecastScheduleSave',
                'shortHelp' => 'Updates a ForecastSchedule model',
                'longHelp' => 'include/api/html/modules/ForecastSchedule/ForecastScheduleApi.html#forecastScheduleSave',
            )
        );
        return $parentApi;

        return parent::registerApiRest();
    }

    /**
     * This method handles the /ForecastsSchedule REST endpoint and returns an Array of ForecastSchedule entries
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of ForecastSchedule data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastSchedule($api, $args)
    {
        // Load up a seed bean
        require_once('modules/ForecastSchedule/ForecastSchedule.php');
        $seed = new ForecastSchedule();

        if (!$seed->ACLAccess('list') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        global $app_list_strings, $current_language, $current_user;
        $app_list_strings = return_app_list_strings_language($current_language);

        $timeperiod_id = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $user_id = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $where = "timeperiod_id = '{$timeperiod_id}' AND user_id = '{$user_id}'";
        $query = $seed->create_export_query('forecast_schedule.date_modified DESC', $where);

        $result = $seed->db->limitQuery($query, 0, 1);

        $data = array();
        
        while($row = $seed->db->fetchByAssoc($result))
        {
            $data[] = $row;
        }

		if(empty($data)){
		   $data[] = array("expected_best_case" => "0.0",
        				 "expected_likely_case" => "0.0",
        				 "expected_worst_case" => "0.0",
        				 "expected_amount" => "0.0",
        				 "cascade_hierarchy" => 0,
        				 "status" => "Active",
        				 "user_id" => $user_id,
        				 "timeperiod_id" => $timeperiod_id);
		}

        return $data;
    }

    /**
     * This method handles the /ForecastsSchedule REST endpoint to update the ForecastSchedule entry
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String id of the ForecastSchedule entry updated
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastScheduleSave($api, $args)
    {
        require_once('modules/ForecastSchedule/ForecastSchedule.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastSchedule();
        $seed->loadFromRow($args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties)
        {
            $fieldName = $properties['name'];

            if(!isset($args[$fieldName]))
            {
               continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName,'save'))
            {
                global $app_strings;
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized(string_format($app_strings['SUGAR_API_EXCEPTION_NOT_AUTHORIZED'], array($fieldName, $seed->object_name)));
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if($field != null)
            {
               $field->save($seed, $args, $fieldName, $properties);
            }
        }

        $seed->save();
        return $seed->id;
    }

}
