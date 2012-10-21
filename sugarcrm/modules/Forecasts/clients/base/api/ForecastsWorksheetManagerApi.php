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
require_once('modules/Forecasts/clients/base/api/ForecastsChartApi.php');

class ForecastsWorksheetManagerApi extends ForecastsChartApi
{

    public function registerApiRest()
    {
        //Extend with test method
        $parentApi = array(
            'forecastManagerWorksheet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastManagerWorksheets'),
                'pathVars' => array('', ''),
                'method' => 'forecastManagerWorksheet',
                'shortHelp' => 'Returns a collection of ForecastManagerWorksheet models',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManager',
            ),
            'forecastManagerWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastManagerWorksheets', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'forecastManagerWorksheetSave',
                'shortHelp' => 'Update a ForecastManagerWorksheet model',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManagerSave',
            ),
            'forecastManagerWorksheetExport' => array(
                'reqType' => 'GET',
                'path' => array('ForecastManagerWorksheets', 'export'),
                'pathVars' => array('', ''),
                'method' => 'exportForecastManagerWorksheet',
                'shortHelp' => 'Exports a forecast manager worksheet',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastManagerWorksheetExport',
            )
        );
        return $parentApi;
    }

    public function forecastManagerWorksheet($api, $args)
    {
        // Load up a seed bean
        require_once('modules/Forecasts/ForecastManagerWorksheet.php');
        $seed = new ForecastManagerWorksheet();

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();

        $obj = $this->getClass($args);
        return $obj->process();
    }


    public function forecastManagerWorksheetSave($api, $args)
    {
        $obj = $this->getClass($args);
        return $obj->save();
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
        $include_file = get_custom_file_if_exists($file);

        // if a custom file exists then we need to rename the class name to be Custom_
        if($include_file != $file) {
            $klass = "Custom_" . $klass;
        }

        // include the class in since we don't have a auto loader
        require_once($include_file);
        // create the lass

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj;
    }


    /**
      * This method handles exporting data for the /ForecastManagerWorksheet REST endpoint
      *
      * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
      * @param $args array The arguments array passed in from the API
      *
      * @return binary file
      * @throws SugarApiExceptionNotAuthorized
      */
     public function exportForecastManagerWorksheet($api, $args)
     {
        // Load up a seed bean
        $seed = BeanFactory::getBean('ForecastManagerWorksheets');

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        global $current_user;

        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $obj = $this->getClass($args);
        $data = $obj->process();

        //Filter out the data based on the configuration settings
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $hide = array();

        if(!$settings['show_worksheet_likely'])
        {
            $hide['likely_case'] = 'likely_case';
            $hide['likely_adjusted'] = 'likely_adjusted';
        }

        if(!$settings['show_worksheet_best'])
        {
            $hide['best_case'] = 'best_case';
            $hide['best_adjusted'] = 'best_adjusted';
        }

        if(!$settings['show_worksheet_worst'])
        {
            $hide['worst_case'] = 'worst_case';
            $hide['worst_adjusted'] = 'worst_adjusted';
        }

        global $locale;
        $timePeriod = BeanFactory::getBean('TimePeriods');
        $timePeriod->retrieve($args['timeperiod_id']);
        $filename = sprintf("%s_to_%s_%s.csv", $timePeriod->start_date, $timePeriod->end_date, $args['user_id']);

        require_once('include/export_utils.php');
        $fields_array = array('amount'=>'amount',
            'quota'=>'quota',
            'quota_id'=>'quota_id',
            'best_case'=>'best_case',
            'likely_case'=>'likely_case',
            'worst_case'=>'worst_case',
            'label'=>'label',
            'date_modified'=>'date_modified',
            'best_adjusted'=>'best_adjusted',
            'likely_adjusted'=>'likely_adjusted',
            'worst_adjusted'=>'worst_adjusted',
            'forecast'=>'forecast',
            'forecast_id'=>'forecast_id',
            'worksheet_id'=>'worksheet_id',
            'commit_stage'=>'commit_stage',
            'currency_id'=>'currency_id',
            'base_rate'=>'base_rate',
            'show_opps'=>'show_opps',
            'timeperiod_id'=>'timeperiod_id',
            'id'=>'id',
            'label'=>'label',
            'name'=>'name',
            'user_id'=>'user_id',
            'version'=>'version'
        );

        if(!empty($hide))
        {
            foreach($fields_array as $key=>$value)
            {
                if(isset($hide[$value]))
                {
                    unset($fields_array[$key]);
                }
            }
        }

        //set up the order on the header row
        //$fields_array = get_field_order_mapping($seed->module_dir, $fields_array);
        //set up labels to be used for the header row
        $field_labels = array();
        foreach($fields_array as $key=>$label)
        {
             $field_labels[$key] = translateForExport($label, $seed);
        }

        // setup the "header" line with proper delimiters
        $content = "\"".implode("\"".getDelimiter()."\"", array_values($field_labels))."\"\r\n";

        if(!empty($data))
        {
            //process retrieved record
            //BEGIN SUGARCRM flav=pro ONLY
            $isAdminUser = is_admin($current_user);
            //END SUGARCRM flav=pro ONLY

            foreach($data as $val)
            {
                foreach($hide as $hideKey)
                {
                    unset($val[$hideKey]);
                }
                $content .= getExportContentForRow($val, $seed, $isAdminUser, $fields_array);
            }
        }

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

}
