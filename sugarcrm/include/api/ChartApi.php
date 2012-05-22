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

    protected function getReportBuilder($args)
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
            $filter = new SugarParsers_Filter($ReportBuilder->getDefaultModule(true));
            // fix the filter args
            $filter->parseJson(html_entity_decode($args['filter'], ENT_QUOTES));
            $ReportBuilder->addFilter($filter->convert(new SugarParsers_Converter_Report($ReportBuilder)));
        }

        return $ReportBuilder;
    }

    protected function generateBar($api, $args)
    {
        $ReportBuilder = $this->getReportBuilder($args);
        // now we have the chart data in the format for the reporting engine.
        $chart_contents = $ReportBuilder->addSummaryCount()->toJson();

        /* @var $reporter Report */
        require_once('modules/Reports/Report.php');
        require_once('include/SugarCharts/ChartDisplay.php');
        $chartDisplay = new ChartDisplay();
        $chartDisplay->setReporter(new Report($chart_contents));

        if ($chartDisplay->canDrawChart() === false) {
            // no chart to display, so lets just kick back the error message
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }

        $chart = $chartDisplay->getSugarChart();
        $json = $chart->buildJson($chart->generateXML());
        // fix-up the json
        $json = str_replace(array("\t", "\n"), "", $json);
        $json = str_replace("'", '"', $json);

        $dataArray = json_decode($json, true);

        return $dataArray;
    }
}