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

class ForecastsWorksheetApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'worksheet' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','worksheet'),
                'pathVars' => array('',''),
                'method' => 'worksheet',
                'shortHelp' => 'A ping',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#ping',
            ),
            'worksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('Forecasts','worksheet'),
                'pathVars' => array('',''),
                'method' => 'worksheetSave',
                'shortHelp' => 'A ping',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#ping',
            ),
        );
        return $parentApi;
    }


    public function worksheetSave($api, $args)
    {

    }

    /**
     * This method returns the result for a sales rep view/manager's opportunities view
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function worksheet($api, $args)
    {
        require_once('modules/Reports/Report.php');
        require_once('modules/Forecasts/data/ChartAndWorksheetManager.php');

        global $app_list_strings,$current_language, $current_user;

        $app_list_strings = return_app_list_strings_language($current_language);

        $mgr = ChartAndWorksheetManager::getInstance();
        //define worksheet type: 'manager' or 'individual'
        $type =  isset($args['type']) ? $args['type'] : 'individual';

        $report_defs = array();
        $report_defs = $mgr->getWorksheetDefintion($type, 'opportunities');

        $timeperiod_id = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $user_id = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $testFilters = array();
        $testFilters = $mgr->getWorksheetFilters($type, array('user_id' => $user_id, 'timeperiod_id' => $timeperiod_id));
        if (empty($testFilters))
        {
            $testFilters = array(
                'timeperiod_id' => array('$is' => $timeperiod_id),
                'assigned_user_link' => array('id' => $user_id),
            );
        }

        require_once('include/SugarParsers/Filter.php');
        require_once("include/SugarParsers/Converter/Report.php");
        require_once("include/SugarCharts/ReportBuilder.php");

        // create the a report builder instance
        $rb = new ReportBuilder("Opportunities");
        // load the default report into the report builder
        $rb->setDefaultReport($report_defs[2]);

        // parse any filters from above
        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse($testFilters);
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder

        //_pp($reportFilters);
        //die();
        $rb->addFilter($reportFilters);

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        $report = new Report($chart_contents);

        return $mgr->getWorksheetGridData($type, $report);
    }

}
