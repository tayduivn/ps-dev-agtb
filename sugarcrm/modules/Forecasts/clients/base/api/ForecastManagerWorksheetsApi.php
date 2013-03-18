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

require_once('include/api/SugarApi.php');

class ForecastManagerWorksheetsApi extends SugarApi
{
    public function registerApiRest()
    {
        //Extend with test method
        return array(
            'forecastManagerWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastManagerWorksheets', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'forecastManagerWorksheetSave',
                'shortHelp' => 'Update a ForecastManagerWorksheet model',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetManagerPut.html',
            )
        );
    }

    /**
     * This method handles saving data for the /ForecastManagerWorksheet REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastManagerWorksheetSave($api, $args)
    {
        if (!SugarACL::checkAccess('Forecasts', 'edit')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: Forecasts');
        }
        $obj = $this->getClass($args);
        $obj->save();

        return array();
    }

    /**
     * @param $args
     * @return SugarForecasting_Manager
     */
    protected function getClass($args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Manager.php';
        $klass = 'SugarForecasting_Manager';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj;
    }

}
