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

        if (isset($args['group_by'])) {

            $arrGroupBy = preg_split("#,#", $args['group_by']);

            foreach ($arrGroupBy as $gb) {
                $ReportBuilder->addGroupBy($gb);
            }
        }

        if (isset($args['link'])) {
            $arrLinks = preg_split("#,#", $args['link']);

            foreach ($arrLinks as $link) {
                $arrLink = preg_split("#:#", $link);

                call_user_func_array(array($ReportBuilder, "addLink"), $arrLink);
            }
        }

        if (isset($args['filter'])) {
            require_once('include/SugarParsers/Filter.php');
            require_once('include/SugarParsers/Converter/Report.php');
            $filter = new SugarParsers_Filter();
            // fix the filter args
            $filter->parseJson(html_entity_decode($args['filter'], ENT_QUOTES));
            $filters = $filter->convert(new SugarParsers_Converter_Report());

            $ReportBuilder->addFilter($filters);

        }

        $chart_contents = $ReportBuilder->addSummaryCount()->toJson();

        require_once("modules/Reports/templates/templates_chart.php");


        /* @var $reporter Report */
        require_once('modules/Reports/Report.php');
        $reporter = new Report($chart_contents);
        $reporter->is_saved_report = true;
        $reporter->get_total_header_row();
        $reporter->run_chart_queries();

        $chart_type = $reporter->chart_type;

        $group_key = (isset($reporter->report_def['group_defs'][0]['table_key']) ? $reporter->report_def['group_defs'][0]['table_key'] : '') .
            ':' .
            (isset($reporter->report_def['group_defs'][0]['name']) ? $reporter->report_def['group_defs'][0]['name'] : '');

        if (!empty ($reporter->report_def['group_defs'][0]['qualifier'])) {
            $group_key .= ':' . $reporter->report_def['group_defs'][0]['qualifier'];
        }
        $i = 0;
        foreach ($reporter->chart_header_row as $header_cell) {
            if ($header_cell['column_key'] == 'count') {
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

            $json = str_replace(array("\t", "\n"), "", $json);
            $json = str_replace("'", '"', $json);

            $dataArray = json_decode($json);

            return $dataArray;
        }
        else {
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }
    }
}