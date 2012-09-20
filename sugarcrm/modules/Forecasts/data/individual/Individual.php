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

require_once('modules/Forecasts/data/IChartAndWorksheet.php');

/**
 * Individual.php
 *
 * This class is the IChartAndWorksheet implementation for individual chart and worksheet data.  It uses a predefined
 * report definition that accepts timeperiod_id and assigned_user_link (assigned_user_id) filters
 *
 */
class Individual implements IChartAndWorksheet {

    /**
     * @var array
     */
    protected $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"id","label":"ID","table_key":"self"},{"name":"amount","label":"Amount","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"commit_stage","label":"Commit Stage","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"timeperiod_id","label":"TimePeriod ID","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"best_case","label":"Best case","table_key":"self"},{"name":"worst_case","label":"Worst case","table_key":"self"},{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link","type":"user_name"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link"},{"name":"best_case","label":"SUM: Best case","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test","chart_type":"vBarF","do_round":0,"chart_description":"","numerical_chart_column":"self:best_case:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14"],"module":"Users","label":"Assigned to User"}},"filters_def":[]}', 'detailed_summary', 'vBarF')
    );

    /**
     * This method returns an Array of individual worksheet Array data based off of data derived from the report bean instance
     *
     * @param $report Report bean instance
     * @return array
     */
    public function getGridData(Report $report)
    {
        global $current_user;
        $report->run_query();
        $opps = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($report->result))!=null)
        {
            $return = array();
            $return['id'] = $row['primaryid'];
            $return['commit_stage'] = $row['opportunities_commit_stage'];
            $return['name'] = $row['opportunities_name'];
            $return['amount'] = $row['opportunities_amount'];
            $return['date_closed'] = $row['opportunities_date_closed'];
            $return['probability'] = $row['opportunities_probability'];
            $return['sales_stage'] = $row['opportunities_sales_stage'];
            $return['best_case'] = intval($row['opportunities_best_case']) == 0 ? $return['amount'] : $row['opportunities_best_case'];
            $return['worst_case'] = intval($row['opportunities_worst_case']) == 0 ? $return['amount'] : $row['opportunities_worst_case'];
            $return['assigned_user_id'] = $row['l1_id'];

            $opps[] = $return;
        }

        return $opps;
    }


    /**
     * This method returns the chart filters used for the data given the filter values
     *
     * @param $args Array of filter values
     * @return array Array of chart filter definition
     */
    public function getChartFilter($args) {
        return array(
            'timeperiod_id' => array('$is' => $args['timeperiod_id']),
            'assigned_user_link' => array('id' => $args['user_id']),
        );
    }

    /**
     * This method returns the worksheet definition for the given id
     *
     * @param $id String of the worksheet definition id
     * @return Array the worksheet definition
     */
    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
    
}
