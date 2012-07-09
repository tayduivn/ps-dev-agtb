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

    private $user_id;
    private $timeperiod_id;

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'worksheetmanager' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','worksheetmanager'),
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

        if(!User::isManager($current_user->id))
        {
           return array();
        }

        if(isset($args['user_id']))
        {
            $user = new User();
            $user->retrieve($args['user_id']);
            if(!User::isManager($user->id))
            {
                return array();
            }
        } else {
            $user = $current_user;
        }

        $app_list_strings = return_app_list_strings_language($current_language);

        $this->timeperiod_id =  isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $this->user_id = isset($args['user_id']) ? $args['user_id'] : $user->id;
		
        $mgr = ChartAndWorksheetManager::getInstance();
        $report_defs = $mgr->getWorksheetDefintion('manager', 'opportunities');

        $testFilters = array(
            'timeperiod_id' => array('$is' => $this->timeperiod_id),
            'assigned_user_link' => array('id' => array('$or' => array('$is' => $this->user_id, '$reports' => $this->user_id))),
        );

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

        $rb->addFilter($reportFilters);

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        $report = new Report($chart_contents);

        //populate output with default data
        $default_data = array("amount" => 0,
                              "quota" => 0,
                              "best_case" => 0,
                              "likely_case" => 0,
                              "best_adjusted" => 0,
                              "likely_adjusted" => 0,
                              "forecast" => 0);

        $default_data['name'] = $user->first_name . " " . $user->last_name;
        $default_data['user_id'] = $user->id;
        $data[$user->user_name] = $default_data;


        require_once("modules/Forecasts/Common.php");
        $common = new Common();
        $common->retrieve_direct_downline($this->user_id);

        foreach($common->my_direct_downline as $reportee_id)
        {
            $reportee = new User();
            $reportee->retrieve($reportee_id);
            $default_data['name'] = $reportee->first_name . " " . $reportee->last_name;
            $default_data['user_id'] = $reportee_id;
            $data[$reportee->user_name] = $default_data;
        }

        $data_grid = array_replace_recursive($data, $mgr->getWorksheetGridData('manager', $report));

        $quota = $this->getQuota();
        $forecast = $this->getForecastBestLikely();
        $worksheet = $this->getWorksheetBestLikelyAdjusted();
        $data_grid = array_replace_recursive($data_grid, $quota, $forecast, $worksheet);
        return array_values($data_grid);
    }


    protected function getQuota()
    {
        //getting quotas from quotas table
        $quota_query = "SELECT u.user_name user_name,
                              q.amount quota
                        FROM quotas q, users u
                        WHERE q.user_id = u.id
                        AND (q.user_id = '{$this->user_id}' OR q.user_id IN (SELECT id FROM users WHERE reports_to_id = '{$this->user_id}'))
                        AND q.timeperiod_id = '{$this->timeperiod_id}'
                        AND q.quota_type = 'Direct'";

        $result = $GLOBALS['db']->query($quota_query);
        $data = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['quota'] = $row['quota'];
        }

        return $data;
    }
    
    protected function getForecastBestLikely()
    {
        //getting best/likely values from forecast table
        $forecast_query = "SELECT
        u.user_name,
        f.date_modified,
        f.best_case,
        f.likely_case,
        f.worst_case worst
        FROM users u
        INNER JOIN (
        SELECT user_id, max(date_modified) date_modified, best_case, likely_case, worst_case
        FROM forecasts WHERE timeperiod_id = '{$this->timeperiod_id}'
        AND forecast_type = 'Direct'
        group by user_id
        ) as f
        ON f.user_id = u.id
        WHERE u.id = '{$this->user_id}' OR u.reports_to_id = '{$this->user_id}'
        AND u.deleted=0 AND u.status = 'Active'";

        $result = $GLOBALS['db']->query($forecast_query);

        $data = array();
        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best_case'] = $row['best_case'];
            $data[$row['user_name']]['likely_case'] = $row['likely_case'];
        } 

        return $data;
    }

    protected function getWorksheetBestLikelyAdjusted()
    {
        //getting data from worksheet table for reportees
        $reportees_query = "SELECT u.user_name user_name,
                            w.forecast,
                            w.best_case best_adjusted,
                            w.likely_case likely_adjusted,
                            w.worst_case worst_adjusted
                            FROM worksheet w, users u
                            WHERE w.related_id = u.id
                            AND w.timeperiod_id = '{$this->timeperiod_id}'
                            AND w.user_id = '{$this->user_id}'
                            AND ((w.related_id in (SELECT id from users WHERE reports_to_id = '{$this->user_id}') AND w.forecast_type = 'Rollup') OR (w.related_id = '{$this->user_id}' AND w.forecast_type = 'Direct'))";

        $result = $GLOBALS['db']->query($reportees_query);

        $data = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best_adjusted'] = $row['best_adjusted'];
            $data[$row['user_name']]['likely_adjusted'] = $row['likely_adjusted'];
            $data[$row['user_name']]['forecast'] = $row['forecast'];
        }             

        return $data;
    }


}
