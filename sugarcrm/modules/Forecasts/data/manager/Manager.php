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
 * Manager Worksheet Data Class
 */
class Manager implements IChartAndWorksheet {

    /**
     * @var array
     */
    protected $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedManagerReport', '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link","type":"user_name","force_label":"User Name"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum","force_label":"Sales Stage"}],"summary_columns":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"test forecast manager","chart_type":"vBarF","do_round":0,"chart_description":"","numerical_chart_column":"self:amount_usdollar:sum","numerical_chart_column_type":"","assigned_user_id":"seed_jim_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":[]}', 'Matrix', 'vBarF')
    );


    /**
     * @param Report $report
     * @return array
     */
    public function getGridData(Report $report)
    {
        $report->run_summary_query();

        $data_grid = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($report->summary_result))!=null)
        {
            if(!isset($data_grid[$row['l1_user_name']]['amount']))
            {
                $data_grid[$row['l1_user_name']]['amount'] = 0;
            }

            $data_grid[$row['l1_user_name']]['amount'] += $row['opportunities_sum_amount'];
        }

        //get quota + best/likely (forecast) + best/likely (worksheet)
        return $data_grid;
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
