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

require_once('data/BeanFactory.php');
require_once('include/SugarFields/SugarFieldHandler.php');
require_once('include/api/ModuleApi.php');

class ForecastModuleApi extends ModuleApi {

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'test' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','test'),
                'pathVars' => array('',''),
                'method' => 'test',
                'shortHelp' => 'A test',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#test',
            ),
            'filters' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','filters'),
                'pathVars' => array('',''),
                'method' => 'filters',
                'shortHelp' => 'forecast filters',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#filters',
            ),
            'chartOptions' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','chartOptions'),
                'pathVars' => array('',''),
                'method' => 'chartOptions',
                'shortHelp' => 'forecasting chart options',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#chartOptions',
            ),
            'teams' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','teams'),
                'pathVars' => array('',''),
                'method' => 'ping',
                'shortHelp' => 'A ping',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#ping',
            ),
            'worksheet' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','filters'),
                'pathVars' => array('',''),
                'method' => 'ping',
                'shortHelp' => 'A ping',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#ping',
            ),
        );
        return $parentApi;
    }

    public function ping($api, $args) {
        // Just a normal ping request
        return "I'm a duck.";
    }

    public function test($api, $args) {
        // Just a normal ping request
        return array(
            array(
                'to' => 'joo',
                'message'=>'you are awesome!',
            ),
            array(
                'to' => 'gabe',
                'message'=>'no, you are awesome!'
            ),
        );
    }

    public function filters($api, $args) {
        // placeholder for filters
        // todo: really make this work
        global $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);

        return array(
            'timeperiods' => array(
                'label' => 'Forecast Period',
                'options' => TimePeriod::get_timeperiods_dom(),
            ),
            'stages' => array(
                'label' => 'Stages',
                'options' => $app_list_strings['sales_stage_dom'],
            ),
            'probabilities' => array(
                'label' => 'Probabilities',
                'options' => $app_list_strings['sales_probability_dom'],
            ),
        );
    }

    public function chartOptions($api, $args) {
        // placeholder for filters
        // todo: really make this work
        return array(
            'x' => array(
                'x0' => 'Team Members',
                'x1' => 'Account',
                'x2' => 'Channel',
                'x3' => 'Line Items',
                'x4' => 'Month',
            ),
            'y' => array(
                'y0' => 'Revenue',
                'y1' => 'Number of Units',
            ),
            'groupBy' => array(
                'y0' => 'Sales Stage',
                'y1' => 'Revenue Type',
            ),
        );
    }

}
