<?php

require_once('modules/Forecasts/data/IChartAndWorksheet.php');

class Manager implements IChartAndWorksheet {

    private $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedManagerReport', '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link","type":"user_name","force_label":"User Name"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum","force_label":"Sales Stage"}],"summary_columns":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"test forecast manager","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:amount_usdollar:sum","numerical_chart_column_type":"","assigned_user_id":"seed_jim_id","report_type":"summary","layout_options":"2x2","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":[]}', 'Matrix', 'vBarF')

    );


    public function getGridData($report)
    {
        $report->run_summary_query();

        $data_grid = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($report->summary_result))!=null)
        {
            if(!isset( $data_grid[$row['l1_user_name']]['amount']))
            {
                $data_grid[$row['l1_user_name']]['amount'] = 0;
            }

            $data_grid[$row['l1_user_name']]['amount'] += $row['opportunities_sum_amount'];
        }

        //get quota + best/likely (forecast) + best/likely (worksheet)
        return $data_grid;
    }



    public function getChartDefinition($id='')
    {
        return $this->getWorksheetDefinition($id);
    }

    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
}
