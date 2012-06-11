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
require_once('include/api/ListApi.php');

class ForecastModuleApi extends ModuleApi {

    public function __construct()
    {

    }

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
            'chartoptions' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','chartoptions'),
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
                'shortHelp' => 'teams for tree view',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#teams',
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
            ),
            'grid' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','grid'),
                'pathVars' => array('',''),
                'method' => 'grid',
                'shortHelp' => 'A grid',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#grid',
            ),
            'forecastsCommitted' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','forecastsCommitted'),
                'pathVars' => array('',''),
                'method' => 'forecastsCommitted',
                'shortHelp' => 'Most recent committed forecast entry',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastModuleApi.html#forecastsCommitted',
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
        $id = $args['userId'];

        $sql = $GLOBALS['db']->getRecursiveSelectSQL('users', 'id', 'reports_to_id','id, user_name, first_name, last_name, reports_to_id, _level',
            false, "id = '{$id}' AND status = 'Active' AND deleted = 0"
        );

        $result = $GLOBALS['db']->query($sql);

        // Final array to be returned
        $treeData = '';

        $flatUsers = array();
        while($row = $GLOBALS['db']->fetchByAssoc($result))
        {
            if(empty($users[$row['_level']]))  {
                $users[$row['_level']] = array();
            }

            $openClosed = ($row['_level'] == 1) ? 'open' : 'closed';

            $fullName = (empty($row['last_name'])) ? $row['first_name'] : $row['first_name'] . ' ' . $row['last_name'];

            $user = array(
                'data' => $fullName,
                'children' => array(),
                'metadata' => array(
                    "id" => $row['id'],
                    "full_name" => $fullName,
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "reports_to_id" => $row['reports_to_id']
                ),
                'state' => $openClosed
            );

            // Set the main user id as the root for treeData
            if($user['metadata']['id'] == $id)
            {
                $treeData = $user;
            } else {
                $flatUsers[] = $user;
            }
        }

        $treeData['children'] = $this->getChildren( $treeData['metadata']['id'], $flatUsers );

        return $treeData;
    }

    /***
     * Recursive function to get all children of a specific parent $id
     * given a list of $users
     * @param $id {int} ID value of the parent user
     * @param $users {Array} of users
     * @return array of child users
     */
    public function getChildren( $id, $users ) {
        $retChildren = array();
        foreach( $users as $user ) {
            if( $user['metadata']['reports_to_id'] == $id ) {
                $user['children'] = $this->getChildren( $user['metadata']['id'] , $users );
                $retChildren[] = $user;
            }
        }
        return $retChildren;
    }

    /**
     * This method returns the result for a sales rep view/manager's opportunities view
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function grid($api, $args)
    {
        require_once('modules/Reports/Report.php');
        global $current_user, $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $saved_report = new SavedReport();
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case","label":"Best case","table_key":"self"},{"name":"likely_case","label":"Likely case","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"}],"report_name":"Another Test","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"seed_jim_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_1","Filter.1_table_filter_row_1","Filter.1_table_filter_row_1","Filter.1_table_filter_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"id","table_key":"Opportunities:assigned_user_link","qualifier_name":"is","runtime":1,"input_name0":["Current User"]},"1":{"name":"timeperiod_id","table_key":"self","qualifier_name":"is","runtime":1,"input_name0":["2cb25447-b5c3-c71e-78bc-4fd2262190fa"]},"2":{"name":"probability","table_key":"self","qualifier_name":"between","runtime":1,"input_name0":"0","input_name1":"70"},"3":{"name":"sales_stage","table_key":"self","qualifier_name":"one_of","runtime":1,"input_name0":["Prospecting","Qualification","Needs Analysis","Value Proposition","Id. Decision Makers","Perception Analysis","Proposal\/Price Quote","Negotiation\/Review","Closed Won"]}}}}', 'detailed_summary', 'vBarF');        //$result = $saved_report->save_report(-1, $current_user->id, $report_defs['ForecastSeedReport1'][1], $report_defs['ForecastSeedReport1'][0], $report_defs['ForecastSeedReport1'][3], $report_defs['ForecastSeedReport1'][2], 1, '1', $report_defs['ForecastSeedReport1'][4]);
        $report = new Report($report_defs['ForecastSeedReport1'][2]);

        //Change the timeperiod to the current timperiod or whatever is given...

        //Timeperiod
        $report->report_def['filters_def']['Filter_1'][1]['input_name0'] = array(TimePeriod::getCurrentId());

        //Probability between
        $report->report_def['filters_def']['Filter_1'][2]['input_name0'] = 0;
        $report->report_def['filters_def']['Filter_1'][2]['input_name1'] = 70;

        //Sales Stage
        //$report->report_def['fitlers_def']['Filter_1'][3]['input_name0'] = array();

        /*
        $testFilters = array(
            'timeperiod_id' => TimePeriod::getCurrentId(),
            'probability' => array('between', array(0, 70)),
            'sales_stage' => array('in', array('Prospecting', 'Qualification', 'Needs Analysis')),
        );

        require_once('include/SugarParsers/Filter.php');
        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse(array('and'=>$testFilters));
        $converter = new SugarParsers_Converter_Report(new ReportBuilder("Opportunities"));
        $reportFilters = $filter->convert($converter);
        $report->report_def['filters_def'] = $reportFilters;
        */

        $report->clear_group_by();
        $report->create_order_by();
        $report->create_select();
        $report->create_where();
        $report->create_group_by(false);
        $report->create_from();
        $report->create_query();
        $limit = false;
        if ($report->report_type == 'tabular' && $report->enable_paging) {
            $report->total_count = $report->execute_count_query();
            $limit = true;
        }
        $result = $GLOBALS['db']->query($report->query);

        $opps = array();
        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $row['id'] = $row['primaryid'];
            $row['name'] = $row['opportunities_name'];
            $row['amount'] = $row['opportunities_amount'];
            $row['date_closed'] = $row['opportunities_date_closed'];
            $row['probability'] = $row['opportunities_probability'];
            $row['sales_stage'] = $row['opportunities_sales_stage'];
            $row['best_case_worksheet'] = $row['opportunities_best_case'];
            $row['likely_case_worksheet'] = $row['opportunities_likely_case'];
            $opps[] = $row;
        }
        return $opps;
    }

    public function forecastsCommitted($api, $args)
    {
        global $current_user;
        $query = "SELECT * FROM forecasts WHERE user_id = '{$current_user->id}' AND deleted = 0 ORDER BY date_entered desc";
        $results = $GLOBALS['db']->query($query);
        $forecasts = array();
        while(($row = $GLOBALS['db']->fetchByAssoc($results)))
        {
            $forecasts[] = $row;
        }
        return array('committed' => $forecasts);
    }

}
