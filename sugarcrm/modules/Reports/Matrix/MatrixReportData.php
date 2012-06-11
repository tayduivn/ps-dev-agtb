<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * Matrix report data model
 */
class MatrixReportData
{
    /**
     * The underlying SugarCRM report object
     *
     * @var Report
     */
    protected $report;

    /**
     * Count of report grouping fields
     *
     * @var int
     */
    protected $grouping_field_count;

    /**
     * Count of report display columns
     *
     * @var int
     */
    protected $display_field_count;

    /**
     * Header labels
     *
     * @var array
     */
    protected $header_labels = array();

    /**
     * Header values
     *
     * @var array
     */
    protected $header_values = array();

    /**
     * Index of header values (a flipped copy of $header_values)
     *
     * @var array
     */
    private $header_value_index = array();

    /**
     * Report data tree
     *
     * @var array
     */
    protected $data = array();

    /**
     * Array of report currency fields. Key is field index, value is whether the
     * currency is the system one.
     *
     * @var array
     */
    protected $currency_fields = array();

    /**
     * Currency sumbol
     *
     * @var string
     */
    protected $currency_symbol;

    /**
     * Constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->grouping_field_count = count($report->report_def['group_defs']);

        $display_fields = $report->report_def['summary_columns'];
        $display_fields = array_slice($display_fields, $this->grouping_field_count);
        $this->display_field_count = count($display_fields);

        foreach ($display_fields as $i => $definition) {
            if (isset($definition['field_type']) && 'currency' == $definition['field_type']) {
                $this->currency_fields[$i] = SugarWidgetFieldCurrency::isSystemCurrency($definition);
            }
        }

        foreach ($report->report_def['group_defs'] as $definition)
        {
            $this->header_labels[] = $definition['label'];
        }

        $this->currency_symbol = $report->currency_symbol;

        $this->data = $this->getReportSummary($report, true);

        $this->report = $report;
    }

    /**
     * Returns label corresponding to specified column header
     *
     * @param int $column
     * @return string|boolean
     */
    public function getHeaderLabel($column)
    {
        if (isset($this->header_labels[$column])) {
            return $this->header_labels[$column];
        }
        return false;
    }

    /**
     * Returns values corresponding to specified column header
     *
     * @param int $column
     * @return string|boolean
     */
    public function getHeaderValues($column)
    {
        if (isset($this->header_values[$column])) {
            return $this->header_values[$column];
        }
        return false;
    }

    /**
     * Returns size (as a number if cells) of specified column
     *
     * @param int $column
     * @return int|boolean
     */
    public function getColumnSize($column)
    {
        $values = $this->getHeaderValues($column);
        if (is_array($values)) {
            return count($values);
        }
        return false;
    }

    /**
     * Returns count of grouping fields in the report
     *
     * @return int
     */
    public function getGroupingFieldCount()
    {
        return $this->grouping_field_count;
    }

    /**
     * Returns count of summary columns of the report
     *
     * @return int
     */
    public function getDisplayFieldCount()
    {
        return $this->display_field_count;
    }

    /**
     * Returns report data tree
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns totals for specified report column or set of columns.
     *
     * @param int|array $columns
     * @return array
     */
    public function getTotals($columns)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $report = $this->getSubReport($columns);
        $totals = $this->getReportSummary($report, false, $columns);
        return $totals;
    }

    /**
     * Returns report Grand Total
     *
     * @return array
     */
    public function getGrandTotal()
    {
        return $this->getTotals(array());
    }

    /**
     * Return display field formats
     *
     * @return array
     */
    public function getCurrencySymbol()
    {
        return $this->currency_symbol;
    }

    /**
     * Return display field formats
     *
     * @return array
     */
    public function getCurrencyFields()
    {
        return $this->currency_fields;
    }

    /**
     * Returns Report object with the same definition as the original one
     * but containing only specified grouping columns. This report is then used
     * for totals calculation on database side.
     *
     * @param array $columns
     * @return Report
     */
    protected function getSubReport(array $columns)
    {
        // retrieve original report definition
        $report_def = $this->report->report_def;

        // remove unneeded columns from "GROUP BY" specification
        $original_group_defs = $report_def['group_defs'];
        $report_def['group_defs'] = array_intersect_key(
            $report_def['group_defs'], array_flip($columns)
        );
        $removed_columns = array_diff_key($original_group_defs, $report_def['group_defs']);

        // remove the same columns from "SELECT" definition
        $report_def['summary_columns'] = array_diff_key(
            $report_def['summary_columns'], $removed_columns
        );

        // remove the same columns from "ORDER BY" definition
        $report_def['summary_order_by'] = array_diff_key(
            $report_def['summary_order_by'], $removed_columns
        );

        $json = getJSONobj();

        // create new Report with modified definition
        $report = new Report($json->encode($report_def));

        // inherit security settings from original report
        $report->focus->disable_row_level_security
            = $this->report->focus->disable_row_level_security;

        return $report;
    }

    /**
     * Collects summary data provided by specified report into a tree structure.
     *
     * @param Report $report
     * @param boolean $collectHeaders
     * @param array|boolean $columns
     * @return array
     */
    protected function getReportSummary(Report $report, $collectHeaders, $columns = false)
    {
        // if columns are not provided consider that it's summary data and
        // all grouping columns are used
        if (false === $columns) {
            $columns = range(0, $this->grouping_field_count - 1);
        }

        $report->run_summary_query();

        $data = array();
        while ($row = $report->get_summary_next_row()) {
            $cells = $row['cells'];

            // create a reference to data tree. initially it points to the root
            $node =& $data;

            foreach ($columns as $i) {
                $value = array_shift($cells);
                if ($collectHeaders) {
                    $position = $this->registerHeaderValue($i, $value);
                }
                else {
                    $position = $this->getHeaderValuePosition($i, $value);
                    if (false === $position) {

                        // this shouldn't be possible during sub-report data
                        // fetching
                        continue 2;
                    }
                }

                // move the reference to the subtree corresponding to header value
                $node =& $node[$position];
            }

            // once the pointer is set, fill the tree node with summary data
            $values = array();
            for ($i = 0; $i < $this->display_field_count; $i++) {
                $value = array_shift($cells);

                // values are to be formatted by Excel itself
                $value = unformat_number($value);
                $values[] = $value;
            }
            $node = $values;

            // clean the reference
            unset($node);
        }

        return $data;
    }

    /**
     * Returns position of cell corresponding to specified column and value
     *
     * @param $column
     * @param $value
     * @return bool
     */
    protected function getHeaderValuePosition($column, $value)
    {
        if (isset($this->header_value_index[$column][$value])) {
            return $this->header_value_index[$column][$value];
        }
        return false;
    }

    /**
     * Registers value of header in specified column and returns it's position
     *
     * @param int $column
     * @param mixed $value
     * @return int
     */
    protected function registerHeaderValue($column, $value)
    {
        if (!isset($this->header_values[$column])) {
            $this->header_values[$column]
                = $this->header_value_index[$column] = array();
        }

        $key = $this->getHeaderValuePosition($column, $value);
        if (false === $key) {
            $this->header_values[$column][] = $value;
            $this->header_value_index[$column]
                = array_flip($this->header_values[$column]);
            $key = count($this->header_values[$column]) - 1;
        }

        return $key;
    }
}
