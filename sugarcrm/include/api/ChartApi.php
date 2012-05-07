<?php


class ChartApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'chart' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'chart', '?'),
                'pathVars' => array('module', '', 'chart_type'),
                'method' => 'chartData',
                'shortHelp' => 'Return Chart Data for a given module',
                'longHelp' => 'include/api/help/getChartModule.html',
            ),
        );
    }

    public function chartData($api, $args)
    {
        $this->requireArgs($args, array('module', 'chart_type'));

        switch ($args['chart_type']) {
            case 'bar':
                return $this->generateBar($api, $args);
                break;
        }

        return array();
    }

    protected function generateBar($api, $args)
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language('en_us');
        require_once("include/SugarCharts/ReportBuilder.php");
        $ReportBuilder = new ReportBuilder($args['module']);

        if(isset($args['group_by'])) {

            $arrGroupBy = preg_split("#,#", $args['group_by']);

            foreach($arrGroupBy as $gb) {
                $ReportBuilder->addGroupBy($gb);
            }
        }

        $chart_contents = $ReportBuilder->addSummaryCount()->toJson();

        require_once("modules/Reports/templates/templates_chart.php");

        //$chart_contents = '{"report_type":"summary","display_columns":[],"summary_columns":[{"name":"count","label":"Count","group_function":"count","table_key":"self"},{"name":"lead_source","label":"Leads: Lead Source","table_key":"self","is_group_by":"visible"}],"filters_def":[],"filters_combiner":"AND","group_defs":[{"name":"lead_source","label":"Lead Source","table_key":"self"}],"full_table_list":{"self":{"parent":"","value":"Leads","module":"Leads","label":"Leads","children":{"self_link_0":"self_link_0"}},"self_link_0":{"parent":"self","children":[],"value":"assigned_user_link","label":"Assigned To User","link_def":{"name":"assigned_user_link","relationship_name":"leads_assigned_user","bean_is_lhs":"","link_type":"one","label":"Assigned To User","table_key":"self_link_0"},"module":"Users"}},"module":"Leads","report_name":"Leads By Lead Source","chart_type":"vBarF","chart_description":"","numerical_chart_column":"count","assigned_user_id":"1"}';
        //$chart_contents = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"count","label":"Count","table_key":"self","group_function":"count","field_type":""}],"report_name":"Jon Test","chart_type":"hBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"operator":"AND"}}}';
        //$chart_contents = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"count","label":"Count","table_key":"self","group_function":"count","field_type":""},{"name":"sales_stage","label":"Sales Stage","table_key":"self"}],"report_name":"Jon Test","chart_type":"hBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"operator":"AND"}}}';
        //$chart_contents = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"},{"name":"opportunity_type","label":"Type","table_key":"self","type":"enum"}],"summary_columns":[{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"opportunity_type","label":"Type","table_key":"self"}],"report_name":"Jon Test","chart_type":"hBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"operator":"AND"}}}';


        /* @var $reporter Report */
        require_once('modules/Reports/Report.php');
        $reporter = new Report($chart_contents);
        $reporter->is_saved_report = true;
        $reporter->get_total_header_row();
        $reporter->run_chart_queries();

        $chart_type = $reporter->chart_type;


        $group_key = (isset($reporter->report_def['group_defs'][0]['table_key']) ? $reporter->report_def['group_defs'][0]['table_key'] : '') .
        ':' .
        (isset($reporter->report_def['group_defs'][0]['name']) ?  $reporter->report_def['group_defs'][0]['name'] : '');

        if (!empty ($reporter->report_def['group_defs'][0]['qualifier'])) {
            $group_key .= ':' . $reporter->report_def['group_defs'][0]['qualifier'];
        }
        $i = 0;
        foreach ($reporter->chart_header_row as $header_cell) {
            if($header_cell['column_key'] == 'count') {
              $header_cell['column_key'] = 'self:count';
            }
            if ($header_cell['column_key'] == $reporter->report_def['numerical_chart_column']) {
                $reporter->chart_numerical_position = $i;
            }
            if ($header_cell['column_key'] == $group_key) {
                $reporter->chart_group_position = $i;
            }
            $i++;
        }


        if (isset($reporter->report_def['layout_options'])) {
            // This is for matrix report
            $reporter->run_total_query();
            // start template_total_table code
            $total_row = $reporter->get_summary_total_row();
            for ($i = 0; $i < count($reporter->chart_header_row); $i++) {
                if ($reporter->chart_header_row[$i]['column_key'] == 'count') {
                    $reporter->chart_header_row[$i]['column_key'] = 'self:count';
                } // if
                if ($reporter->chart_header_row[$i]['column_key'] == $reporter->report_def['numerical_chart_column']) {
                    $total = $i;
                    //break;
                }
                if ($reporter->chart_header_row[$i]['column_key'] == 'self:count') {
                    $reporter->chart_header_row[$i]['column_key'] = 'count';
                } // if
            } // for
            if (empty($total)) {
                $total = 0;
            }
            $total = $total_row['cells'][$total];
            global $do_thousands;
            if (unformat_number($total) > 100000) {
                $do_thousands = true;
                $total = round(unformat_number($total) / 1000);
            } else {
                $do_thousands = false;
                $total = unformat_number($total);
            }
            array_pop($reporter->chart_rows);
        } else {
            $total_row = array_pop($reporter->chart_rows);
            $total = get_total($reporter, $total_row);
        }

        $symbol = print_currency_symbol($reporter->report_def);
        global $current_language, $do_thousands;

        $mod_strings = return_module_language($current_language, 'Reports');

        $chartTitle = $mod_strings['LBL_TOTAL_IS'] . ' ' . $symbol . format_number($total, 0, 0) . get_k();

        $chart_rows = array();
        $chart_totals = array();
        $chart_groupings = array();
        foreach ($reporter->chart_rows as $row) {
            $row_remap = get_row_remap($row, $reporter);
            $chart_groupings[$row_remap['group_base_text']] = true; // store all the groupingstem
            if (empty($chart_rows[$row_remap['group_text']][$row_remap['group_base_text']])) {
                $chart_rows[$row_remap['group_text']][$row_remap['group_base_text']] = $row_remap;
            }
            else {
                $chart_rows[$row_remap['group_text']][$row_remap['group_base_text']]['numerical_value'] += $row_remap['numerical_value'];
            }
        }
        $drawChart = true;
        $stack = false;

        foreach ($chart_rows as $element) {
            if (count($element) > 1) {
                $stack = true;
                break;
            }
        }
        switch ($chart_type) {
            case 'hBarF':
                if ($stack) {
                    $chartType = 'horizontal group by chart';
                }
                else {
                    $chartType = 'horizontal bar chart';
                }
                break;
            case 'vBarF':
                if ($stack) {
                    $chartType = 'stacked group by chart';
                }
                else {
                    $chartType = 'bar chart';
                }
                break;
            case 'pieF':
                $chartType = 'pie chart';
                break;
            case 'lineF':
                if ($stack) {
                    $chartType = 'line chart';
                }
                else {
                    $drawChart = false;
                }
                break;
            case 'funnelF':
                $chartType = 'funnel chart 3D';
                break;
            default:
                break;
        }

        if ($drawChart) {
            require_once('include/SugarCharts/SugarChartFactory.php');

            /* @var $sugarChart JitReports */
            $sugarChart = SugarChartFactory::getInstance('', 'Reports');

            $sugarChart->setData($chart_rows);
            $sugarChart->setProperties($chartTitle, '', $chartType);

            $xml = $sugarChart->generateXML();
            $json = $sugarChart->buildJson($xml);

            $json = str_replace(array("\t","\n"), "", $json);
            $json = str_replace("'", '"', $json);

            $dataArray = json_decode($json);

            return $dataArray;
        }
        else {
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }
    }
}