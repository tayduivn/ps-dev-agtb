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

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'worksheetManager' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','worksheetManager'),
                'pathVars' => array('',''),
                'method' => 'worksheetManager',
                'shortHelp' => 'Worksheet for manager view',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#ping',
            ),
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
        require_once('modules/Reports/Report.php');
        require_once('modules/Forecasts/data/ChartAndWorksheetManager.php');

        global $current_user, $mod_strings, $app_list_strings, $app_strings, $current_language;


        if(isset($args['user_id']))
        {
            $user = new User();
            $user->retrieve($args['user_id']);
        } else {
            $user = $current_user;
        }


        if(!User::isManager($user->id))
        {
           //Error
           return array();
        }

        $app_list_strings = return_app_list_strings_language($current_language);

        $mgr = ChartAndWorksheetManager::getInstance();
        $report_defs = $mgr->getWorksheetDefintion('manager', 'opportunities');

        $timeperiod_id =  isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $user_id = isset($args['user_id']) ? $args['user_id'] : $current_user->id;

        $testFilters = array(
            'timeperiod_id' => array('$is' => $timeperiod_id),
            'assigned_user_link' => array('id' => array('$or' => array('$is' => $user_id, '$reports' => $user_id))),

        );

        // generate the report builder instance
        $rb = $this->generateReportBuilder('Opportunities', $report_defs[2], $testFilters);

        if (isset($args['ct']) && !empty($args['ct'])) {
            $rb->setChartType($this->mapChartType($args['ct']));
        }

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        //Get the goal marker values
        require_once("include/SugarCharts/ChartDisplay.php");
        // create the chart display engine
        $chartDisplay = new ChartDisplay();
        // set the reporter with the chart contents from the report builder
        $chartDisplay->setReporter(new Report($chart_contents));

        $chart_data = $chartDisplay->getReporter()->chart_rows;


        // lets get some json!
        $json = $chartDisplay->generateJson();

        // if we have no data return an empty string
        if ($json == "No Data") {
            return '';
        }

        $query = $chartDisplay->getReporter()->summary_query;
        $result = $GLOBALS['db']->query($query);

        $data_grid = array();
        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data_grid[$row['l1_user_name']]['amount'] += $row['OPPORTUNITIES_SUM_AMOUBFBD41'];
        }

        //get quota + best/likely (forecast) + best/likely (worksheet)

        $quota = $mgr->getQuota($user_id, $timeperiod_id);
        $forecast = $mgr->getForecastBestLikely($user_id, $timeperiod_id);
        $worksheet = $mgr->getWorksheetBestLikelyAdjusted($user_id, $timeperiod_id);

        $data_grid = array_merge_recursive($data_grid, $quota, $forecast, $worksheet);

        $data = array('grid' => $data_grid, 'chart' => $chart_data);

        return $data;
    }

}
