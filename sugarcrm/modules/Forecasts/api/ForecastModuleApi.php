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
            'reportees' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'reportees', '?'),
                'pathVars' => array('','','userId'),
                'method' => 'getReportees',
                'shortHelp' => 'Gets reportees to a user by id',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#reportees',
            )
        );
        return $parentApi;
    }

    public function ping($api, $args) {
        // Just a normal ping request
        return "I'm a duck.";
    }

    public function filters($api, $args) {
        // placeholder for filters
        // todo: really make this work
        global $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);

        return array(
            'timeperiods' => array(
                'label' => 'Forecast Period:',
                'options' => TimePeriod::get_timeperiods_dom(),
            ),
            'stages' => array(
                'label' => 'Sales Stage:',
                'options' => $app_list_strings['sales_stage_dom'],
            ),
            'probabilities' => array(
                'label' => 'Probability (>=):',
                'options' => $app_list_strings['sales_probability_dom'],
            ),
        );
    }

    public function chartOptions($api, $args) {
        // placeholder for filters
        // todo: really make this work
        return array(
            'horizontal' => array(
                'label' => 'Horizontal (x):',
                'options' => array(
                    'x0' => 'Team Members',
                    'x1' => 'Account',
                    'x2' => 'Channel',
                    'x3' => 'Line Items',
                    'x4' => 'Month',
                ),
            ),
            'vertical' => array(
                'label' => 'Vertical (y):',
                'options' => array(
                    'y0' => 'Revenue',
                    'y1' => 'Number of Units',
                ),
            ),
            'groupby' => array(
                'label' => 'Group By:',
                'options' => array(
                    'y0' => 'Sales Stage',
                    'y1' => 'Revenue Type',
                ),
            ),
        );
    }

    /***
     * Returns a hierarchy of users reporting to the current user
     *
     * @param $api
     * @param $args
     * @return string
     */
    public function getReportees($api, $args) {
        $retJSON = array();

        // Grab current user from user ID passed in URL
        $user = BeanFactory::getBean('Users',$args['userId']);
        $user->load_relationship('reportees');
        $children = $user->reportees->getBeans();

        // push curret user node onto tree
        $tmp = array(
            "data" => $user->full_name,
            "metadata" => array(
                // any other metadata needed from the Bean to the JS model should go here
                "id" => $user->id,
                "full_name" => (empty($user->last_name)) ? $user->first_name : $user->first_name . ' ' . $user->last_name,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name
            ),
            "state" => "open",
            "children" => array()
        );
        array_push( $retJSON, $tmp );

        // handle any reportees to the current user
        if(!empty($children))
        {
            foreach($children as $childBean)
            {
                $tmp = array(
                    "data" => $childBean->full_name,
                    "metadata" => array(
                        // any other metadata needed from the Bean to the JS model should go here
                        "id" => $childBean->id,
                        "full_name" => (empty($childBean->last_name)) ? $childBean->first_name : $childBean->first_name . ' ' . $childBean->last_name,
                        "first_name" => $childBean->first_name,
                        "last_name" => $childBean->last_name
                    ),
                    "state" => "closed"
                );
                array_push( $retJSON[0]["children"], $tmp );
            }
        }

        return $retJSON;
    }
}
