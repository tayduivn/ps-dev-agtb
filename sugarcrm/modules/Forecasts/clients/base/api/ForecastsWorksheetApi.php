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

class ForecastsWorksheetApi extends ModuleApi
{

    public function registerApiRest()
    {
        //Extend with test method
        $parentApi = array(
            'forecastWorksheet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets'),
                'pathVars' => array('', ''),
                'method' => 'forecastWorksheet',
                'shortHelp' => 'Returns a collection of ForecastWorksheet models',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastWorksheet',
            ),
            'forecastWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastWorksheets', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'forecastWorksheetSave',
                'shortHelp' => 'Updates a ForecastWorksheet model',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastWorksheet',
            ),
            'forecastWorksheetExport' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets', 'export'),
                'pathVars' => array('', ''),
                'method' => 'exportForecastWorksheet',
                'shortHelp' => 'Exports a forecast worksheet',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastWorksheetExport',
            )
        );
        return $parentApi;
    }


    /**
     * This method handles the /ForecastsWorksheet REST endpoint and returns an Array of worksheet data Array entries
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastWorksheet($api, $args)
    {
        // Load up a seed bean
        require_once('modules/Forecasts/ForecastWorksheet.php');
        $seed = new ForecastWorksheet();

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        global $current_user;

        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $obj = $this->getClass($args);
        return $obj->process();
    }

    /**
     * This method handles saving data for the /ForecastsWorksheet REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastWorksheetSave($api, $args)
    {
        $obj = $this->getClass($args);
        return $obj->save();
    }


    /**
     * This method handles exporting data for the /ForecastWorksheet REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     *
     * @return binary file
     * @throws SugarApiExceptionNotAuthorized
     */
    public function exportForecastWorksheet($api, $args)
    {
        // Load up a seed bean
        $seed = BeanFactory::getBean('ForecastWorksheets');

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        global $current_user;

        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $obj = $this->getClass($args);
        $query = $obj->process(false);

        //Filter out the data based on the configuration settings
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $hide = array();

        if(!$settings['show_worksheet_likely'])
        {
            $hide[] = 'likely_case';
            $hide[] = 'w_likely_case';
        }

        if(!$settings['show_worksheet_best'])
        {
            $hide[] = 'best_case';
            $hide[] = 'w_best_case';
        }

        if(!$settings['show_worksheet_worst'])
        {
            $hide[] = 'worst_case';
            $hide[] = 'w_worst_case';
        }

        global $locale;
        $timePeriod = BeanFactory::getBean('TimePeriods');
        $timePeriod->retrieve($args['timeperiod_id']);
        $filename = sprintf("%s_to_%s_%s.csv", $timePeriod->start_date, $timePeriod->end_date, $args['user_id']);

        $db = DBManagerFactory::getInstance();
        $result = $db->query($query);

        require_once('include/export_utils.php');
        $content = getExportContentFromResult($seed, $result, !empty($hide), $hide);

        ob_clean();
        header("Pragma: cache");
        header("Content-type: application/octet-stream; charset=".$locale->getExportCharset());
        header("Content-Disposition: attachment; filename={$filename}.csv");
        header("Content-transfer-encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . TimeDate::httpTime() );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".mb_strlen($locale->translateCharset($content, 'UTF-8', $locale->getExportCharset())));
        print $locale->translateCharset($content, 'UTF-8', $locale->getExportCharset());
        @sugar_cleanup();
    }

    /**
     * @param $args
     * @return SugarForecasting_Individual
     */
    protected function getClass($args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Individual.php';
        $klass = 'SugarForecasting_Individual';

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
