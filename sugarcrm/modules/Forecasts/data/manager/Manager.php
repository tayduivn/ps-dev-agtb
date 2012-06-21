<?php

require_once('modules/Forecasts/data/IChartAndWorksheet.php');

class Manager implements IChartAndWorksheet {

    private $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link","type":"user_name","force_label":"ID"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum","force_label":"Sales Stage"}],"summary_columns":[{"name":"id","label":"ID","table_key":"Opportunities:assigned_user_link"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"","assigned_user_id":"seed_jim_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":{}', 'detailed_summary', 'vBarF')
    );

    public function getChartDefinition($id='')
    {
        return $this->getWorksheetDefinition($id);
    }

    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
}