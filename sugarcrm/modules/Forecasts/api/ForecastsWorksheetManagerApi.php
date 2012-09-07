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
require_once('modules/Forecasts/api/ForecastsChartApi.php');

class ForecastsWorksheetManagerApi extends ForecastsChartApi {

    public function registerApiRest()
    {
        //Extend with test method
        $parentApi= array (
            'forecastManagerWorksheet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastManagerWorksheets'),
                'pathVars' => array('',''),
                'method' => 'forecastManagerWorksheet',
                'shortHelp' => 'Returns a collection of ForecastManagerWorksheet models',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManager',
            ),
            'forecastManagerWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastManagerWorksheets','?'),
                'pathVars' => array('module','record'),
                'method' => 'forecastManagerWorksheetSave',
                'shortHelp' => 'Update a ForecastManagerWorksheet model',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManagerSave',
            )
        );
        return $parentApi;
    }

    /**
     * This method returns the result for a sales rep view/manager's opportunities view
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function worksheetManager($api, $args)
    {
        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();

        require_once('include/SugarForecasting/Manager.php');
        $forecast_manager = new SugarForecasting_Manager($args);

        return $forecast_manager->process();

    }


    public function forecastManagerWorksheet($api, $args) {
        // Load up a seed bean
        require_once('modules/Forecasts/ForecastManagerWorksheet.php');
        $seed = new ForecastManagerWorksheet();

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        return $this->worksheetManager($api, $args);
    }


    public function forecastManagerWorksheetSave($api, $args) {
        require_once('modules/Forecasts/ForecastManagerWorksheet.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastManagerWorksheet();
        $seed->loadFromRow($args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties)
        {
            $fieldName = $properties['name'];

            if (!isset($args[$fieldName])) {
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName, 'save')) {
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field ' . $fieldName . ' in module: ' . $args['module']);
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if ($field != null) {
                $field->save($seed, $args, $fieldName, $properties);
            }
        }

        $seed->setWorksheetArgs($args);
        $seed->save();
        return $seed->id;
    }

}
