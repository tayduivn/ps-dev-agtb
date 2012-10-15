<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/**
 * Charting Engine API
 *
 * This is used to generate the chart data for the JS Front End.
 */
class ChartApi extends SugarApi
{
    /**
     * Register the Rest End Points
     *
     * @return array
     */
    public function registerApiRest()
    {
        return array(
            'chart' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'chart', '?'),
                'pathVars' => array('module', '', 'chart_type'),
                'method' => 'chartData',
                'shortHelp' => 'Return Chart Data for a given module',
                'longHelp' => 'include/api/help/getModuleChart.html',
                'jsonParams' => array('filter')
            ),
            'savedreport' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'chart', '?', '?'),
                'pathVars' => array('module', '', 'chart_type', 'id'),
                'method' => 'chartData',
                'shortHelp' => 'Return Chart Data for a given module',
                'longHelp' => 'include/api/help/getModuleChart.html',
                'jsonParams' => array('filter')
            )
        );
    }

    /**
     * Handle the Service Class.
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function chartData($api, $args)
    {
        $this->requireArgs($args, array('module', 'chart_type'));

        return $this->generateChart($args);
    }

    /**
     * Generate the ReportBuilder Class from the arguments.
     *
     * @param array $args           Service Call Arguments
     * @return ReportBuilder
     */
    protected function getReportBuilder($args)
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language('en_us');
        require_once("include/SugarCharts/ReportBuilder.php");
        $ReportBuilder = new ReportBuilder($args['module']);

        if(isset($args['id'])) {
            // try and load a new id
            $ReportBuilder->loadSavedReport($args['id']);
        }

        // handle any group by's
        if (isset($args['group_by'])) {

            $arrGroupBy = preg_split("#,#", $args['group_by']);

            foreach ($arrGroupBy as $gb) {
                $ReportBuilder->addGroupBy($gb);
            }
        }

        // handle any links
        if (isset($args['link'])) {
            $arrLinks = preg_split("#,#", $args['link']);

            foreach ($arrLinks as $link) {
                $arrLink = preg_split("#:#", $link);

                call_user_func_array(array($ReportBuilder, "addLink"), $arrLink);
            }
        }

        // handle any filters
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

    /**
     * Map the url typ to the chart engine type
     *
     * @param string $type      Type from the arguments we are trying to map too
     * @return string
     */
    protected function mapChartType($type)
    {
        switch ($type) {
            case 'vbar':
                $chartType = 'vBarF';
                break;
            case 'funnel':
                $chartType = 'funnelF';
                break;
            case 'pie':
                $chartType = 'pieF';
                break;
            case 'line':
                $chartType = 'lineF';
                break;
            case 'bar':
            case 'hbar':
            default:
                $chartType = 'hBarF';
                break;
        }

        return $chartType;
    }

    /**
     * Generate the actual chart data
     *
     * @param array $args
     * @return array|string
     */
    protected function generateChart($args)
    {
        $ReportBuilder = $this->getReportBuilder($args);
        // now we have the chart data in the format for the reporting engine.
        $ReportBuilder->setChartType($this->mapChartType($args['chart_type']));
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

        $json = $chartDisplay->generateJson();

        $dataArray = json_decode($json, true);

        return $dataArray;
    }
}