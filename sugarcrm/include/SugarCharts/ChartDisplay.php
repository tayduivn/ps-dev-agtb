<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once("modules/Reports/templates/templates_chart.php");

/**
 * Handle setting up the Charting for Display
 */
class ChartDisplay
{
    /**
     * Report Class
     *
     * @var Report
     */
    protected $reporter;

    /**
     * What type of chart are we displaying
     *
     * @var string
     */
    protected $chartType;

    /**
     * Can we draw a chart?
     *
     * @var bool
     */
    protected $canDrawChart = true;

    /**
     * Is this a stackable chart?
     *
     * @var bool
     */
    protected $stackChart = false;

    /**
     * What's the title for this chart
     *
     * @var string
     */
    protected $chartTitle = '';

    /**
     * Rows to display in the chart
     *
     * @var array
     */
    protected $chartRows = array();

    /**
     * Set the Reporter to use
     *
     * @param Report $reporter
     */
    public function setReporter(Report $reporter)
    {
        $this->reporter = $reporter;

        // set the default stuff we need on the reporter
        // and run the queries
        $this->reporter->is_saved_report = true;
        $this->reporter->get_total_header_row();
        $this->reporter->run_chart_queries();

        // set the chart type
        $this->chartType = $this->reporter->chart_type;

        $this->parseReportHeaders();
        $this->parseChartTitle();
        $this->parseChartRows();
    }

    /**
     * Get the Reporter
     *
     * @return Report
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Return the SugarChart's object with all the values set and ready for display/consumption.
     *
     * @return JitReports|string
     */
    public function getSugarChart()
    {
        if ($this->canDrawChart()) {
            require_once('include/SugarCharts/SugarChartFactory.php');

            /* @var $sugarChart JitReports */
            $sugarChart = SugarChartFactory::getInstance('', 'Reports');

            $sugarChart->setData($this->chartRows);
            $sugarChart->setProperties($this->chartTitle, '', $this->chartType);

            return $sugarChart;
        } else {
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }
    }

    /**
     * Parse the Report Headers and such set the values we need for items later in the string
     */
    protected function parseReportHeaders()
    {
        $group_key = (isset($this->reporter->report_def['group_defs'][0]['table_key']) ? $this->reporter->report_def['group_defs'][0]['table_key'] : '') .
            ':' .
            (isset($this->reporter->report_def['group_defs'][0]['name']) ? $this->reporter->report_def['group_defs'][0]['name'] : '');

        if (!empty ($this->reporter->report_def['group_defs'][0]['qualifier'])) {
            $group_key .= ':' . $this->reporter->report_def['group_defs'][0]['qualifier'];
        }

        $i = 0;
        foreach ($this->reporter->chart_header_row as $header_cell) {
            if ($header_cell['column_key'] == 'count') {
                $header_cell['column_key'] = 'self:count';
            }
            if ($header_cell['column_key'] == $this->reporter->report_def['numerical_chart_column']) {
                $this->reporter->chart_numerical_position = $i;
            }
            if ($header_cell['column_key'] == $group_key) {
                $this->reporter->chart_group_position = $i;
            }
            $i++;
        }
    }

    /**
     * Generate the Title for the Chart
     */
    protected function parseChartTitle()
    {
        global $current_language, $do_thousands;
        if (isset($this->reporter->report_def['layout_options'])) {
            // This is for matrix report
            $this->reporter->run_total_query();
            // start template_total_table code
            $total_row = $this->reporter->get_summary_total_row();
            for ($i = 0; $i < count($this->reporter->chart_header_row); $i++) {
                if ($this->reporter->chart_header_row[$i]['column_key'] == 'count') {
                    $this->reporter->chart_header_row[$i]['column_key'] = 'self:count';
                } // if
                if ($this->reporter->chart_header_row[$i]['column_key'] == $this->reporter->report_def['numerical_chart_column']) {
                    $total = $i;
                    //break;
                }
                if ($this->reporter->chart_header_row[$i]['column_key'] == 'self:count') {
                    $this->reporter->chart_header_row[$i]['column_key'] = 'count';
                } // if
            } // for
            if (empty($total)) {
                $total = 0;
            }
            $total = $total_row['cells'][$total];
            if (unformat_number($total) > 100000) {
                $do_thousands = true;
                $total = round(unformat_number($total) / 1000);
            } else {
                $do_thousands = false;
                $total = unformat_number($total);
            }
            array_pop($this->reporter->chart_rows);
        } else {
            $total_row = array_pop($this->reporter->chart_rows);
            $total = get_total($this->reporter, $total_row);
        }

        $symbol = print_currency_symbol($this->reporter->report_def);

        $mod_strings = return_module_language($current_language, 'Reports');

        $this->chartTitle = $mod_strings['LBL_TOTAL_IS'] . ' ' . $symbol . format_number($total, 0, 0) . get_k();
    }

    /**
     * Format the ChartRows from the Reporting engine
     */
    protected function parseChartRows()
    {
        $chart_rows = array();
        $chart_groupings = array();
        foreach ($this->reporter->chart_rows as $row) {
            $row_remap = get_row_remap($row, $this->reporter);
            $chart_groupings[$row_remap['group_base_text']] = true; // store all the groupingstem
            if (empty($chart_rows[$row_remap['group_text']][$row_remap['group_base_text']])) {
                $chart_rows[$row_remap['group_text']][$row_remap['group_base_text']] = $row_remap;
            }
            else {
                $chart_rows[$row_remap['group_text']][$row_remap['group_base_text']]['numerical_value'] += $row_remap['numerical_value'];
            }
        }

        // check to see if the chart can be stackable
        foreach ($chart_rows as $element) {
            if (count($element) > 1) {
                $this->stackChart = true;
                break;
            }
        }
        switch ($this->chartType) {
            case 'hBarF':
                if ($this->isStackable()) {
                    $this->chartType = 'horizontal group by chart';
                } else {
                    $this->chartType = 'horizontal bar chart';
                }
                break;
            case 'vBarF':
                if ($this->isStackable()) {
                    $this->chartType = 'stacked group by chart';
                } else {
                    $this->chartType = 'bar chart';
                }
                break;
            case 'pieF':
                $this->chartType = 'pie chart';
                break;
            case 'lineF':
                if ($this->isStackable()) {
                    $this->chartType = 'line chart';
                } else {
                    $this->canDrawChart = false;
                }
                break;
            case 'funnelF':
                $this->chartType = 'funnel chart 3D';
                break;
            default:
                break;
        }

        $this->chartRows = $chart_rows;
    }

    /**
     * Can we draw the chart?
     *
     * @return bool
     */
    public function canDrawChart()
    {
        return $this->canDrawChart;
    }

    /**
     * Is the chart Stackable
     *
     * @return bool
     */
    public function isStackable()
    {
        return $this->stackChart;
    }
}