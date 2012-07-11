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
 * Individual Worksheet Info
 */
class Individual implements IChartAndWorksheet {

    /**
     * @var array
     */
    protected $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"id","label":"ID","table_key":"self"},{"name":"amount","label":"Amount","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"timeperiod_id","label":"TimePeriod ID","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"best_case","label":"Best case","table_key":"self"},{"name":"likely_case","label":"Likely case","table_key":"self"},{"name":"worst_case","label":"Worst case","table_key":"self"},{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link","type":"user_name"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link"},{"name":"best_case","label":"SUM: Best case","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:best_case:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_14"],"module":"Users","label":"Assigned to User"}},"filters_def":[]}', 'detailed_summary', 'vBarF')
    );

    /**
     * @param Report $report
     * @return array
     */
    public function getGridData(Report $report)
    {
        global $current_user;
        $report->run_query();
        $opps = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($report->result))!=null)
        {
            $row['id'] = $row['primaryid'];
            $row['forecast'] = ($row['opportunities_forecast'] == 1) ? true : false;
            $row['name'] = $row['opportunities_name'];
            $row['amount'] = $row['opportunities_amount'];
            $row['date_closed'] = $row['opportunities_date_closed'];
            $row['probability'] = $row['opportunities_probability'];
            $row['sales_stage'] = $row['opportunities_sales_stage'];
            $row['best_case'] = $row['opportunities_best_case'];
            $row['likely_case'] = $row['opportunities_likely_case'];
            $row['worst_case'] = $row['opportunities_worst_case'];
            $row['is_owner'] = $current_user->id == $row['l1_id'];
            //Should we unset the data we don't need here so as to limit data sent back?

            $opps[] = $row;
        }

        return $opps;
    }

    /**
     * @param string $id
     * @return array|mixed
     */
    public function getChartDefinition($id='')
    {
        return $this->getWorksheetDefinition($id);
    }

    public function getChartFilter($args) {

        return array(
            'timeperiod_id' => array('$is' => $args['timeperiod_id']),
            'assigned_user_link' => array('id' => $args['user_id']),
        );
    }

    /**
     * @param string $id
     * @return array|mixed
     */
    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
    
}
