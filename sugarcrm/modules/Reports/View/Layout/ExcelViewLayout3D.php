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
 * @see ExcelViewLayout
 */
require_once 'modules/Reports/View/Layout/ExcelViewLayout.php';

/**
 * Three-dimensional matrix report layout
 */
class ExcelViewLayout3D extends ExcelViewLayout
{
    /**#@+
     * Named column indexes
     */
    const COLUMN_INNER = 2;
    /**#@-*/

    /**
     * Renders three-dimensional matrix report
     *
     * @param MatrixReportData $report
     * @param PHPExcel_Worksheet $sheet
     * @param int $x
     * @param int $y
     */
    protected function _render(MatrixReportData $report, PHPExcel_Worksheet $sheet, $x, $y)
    {
        $this->renderLeftHeader($sheet, $report, $x, $y);
        $this->renderTopHeader($sheet, $report, $x, $y);

        // retrieve report parameters
        $grouping_count = $report->getGroupingFieldCount();

        $y += $grouping_count * 2 - 2;

        // render body
        $this->renderBody($sheet, $report, $x, $y);

        $this->renderRightFooter($sheet, $report, $x, $y);
        $this->renderBottomFooter($sheet, $report, $x, $y);
        $this->renderTotal($sheet, $report, $x, $y);
    }

    /**
     * Renders top header
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderTopHeader(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        // render top header
        $label_top = $report->getHeaderLabel(self::COLUMN_HORIZONTAL);
        $top_header_values = $report->getHeaderValues(self::COLUMN_HORIZONTAL);

        $label_inner = $report->getHeaderLabel(self::COLUMN_INNER);
        $inner_header_values = $report->getHeaderValues(self::COLUMN_INNER);
        $inner_size = $report->getColumnSize(self::COLUMN_INNER);

        $grand_total_label = translate('LBL_GRAND_TOTAL');
        $this->renderer->renderHorizontalHeader(
            $sheet, $label_top, $top_header_values, $inner_size + 1, 2, false, $grand_total_label, ++$x, $y
        );

        // render inner headers
        $total_label = translate('LBL_TOTAL');
        foreach (array_keys($top_header_values) as $i) {
            $this->renderer->renderHorizontalHeader(
                $sheet, $label_inner, $inner_header_values, 1, 1, true, $total_label, $x + $i * ($inner_size + 1), $y + 2
            );
        }
    }

    /**
     * Renders body
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderBody(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        $data = $report->getData();
        $data = $this->transpose($data);
        $inner_size = $report->getColumnSize(self::COLUMN_INNER);
        foreach ($data as $i => $set) {
            $this->renderer->renderBody($sheet, $set, $x + $i * $inner_size + 1, $y);
        }
    }

    /**
     * Renders right footer
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderRightFooter(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        $totals_vh = $report->getTotals(array(
            self::COLUMN_VERTICAL,
            self::COLUMN_HORIZONTAL,
        ));
        $totals_vh = $this->transpose($totals_vh);

        $inner_size = $report->getColumnSize(self::COLUMN_INNER) + 1;
        $display_count = $report->getDisplayFieldCount();

        foreach ($totals_vh as $i => $set) {
            $this->renderer->renderVerticalFooter(
                $sheet, $set, $display_count, $x + ($i + 1) * $inner_size, $y
            );
        }

        $horizontal_size = $report->getColumnSize(self::COLUMN_HORIZONTAL);

        $totals_v = $report->getTotals(array(
            self::COLUMN_VERTICAL,
        ));
        $this->renderer->renderVerticalFooter(
            $sheet, $totals_v, $display_count,
            $x + $horizontal_size * $inner_size + 1, $y
        );
    }

    /**
     * Renders bottom footer
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderBottomFooter(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        $y += $report->getColumnSize(self::COLUMN_VERTICAL) * $report->getDisplayFieldCount();

        $totals_hi = $report->getTotals(array(
            self::COLUMN_HORIZONTAL,
            self::COLUMN_INNER,
        ));

        $inner_size = $report->getColumnSize(self::COLUMN_INNER) + 1;

        foreach ($totals_hi as $i => $set) {
            $this->renderer->renderHorizontalFooter(
                $sheet, $set, $x + $i * $inner_size + 1, $y
            );
        }
    }

    /**
     * Renders report total
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param $x
     * @param $y
     */
    protected function renderTotal(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        // retrieve report parameters
        $vertical_size   = $report->getColumnSize(self::COLUMN_VERTICAL);
        $horizontal_size = $report->getColumnSize(self::COLUMN_HORIZONTAL);
        $inner_size      = $report->getColumnSize(self::COLUMN_INNER) + 1;
        $display_count   = $report->getDisplayFieldCount();

        // apply vertical offset
        $y += $vertical_size * $display_count;
        $subtotals = $report->getTotals(array(
            self::COLUMN_HORIZONTAL,
        ));

        // render subtotals
        foreach ($subtotals as $i => $set) {
            $this->renderer->renderCellSet(
                $sheet, $set, $x + ($i + 1) * $inner_size, $y
            );
        }

        // apply horizontal offset
        $x += 1 + $horizontal_size * $inner_size;

        // render grand total
        $grand_total = $report->getGrandTotal();
        $this->renderer->renderCellSet($sheet, $grand_total, $x, $y);
    }

    /**
     * A helper function. Transposes data matrix.
     *
     * We could get rid of it by refactoring the implementation of data
     * and totals retrieval.
     *
     * @param array $data
     * @return array
     */
    protected function transpose(array $data)
    {
        $transposed = array();
        foreach ($data as $i1 => $data1) {
            foreach ($data1 as $i2 => $data2) {
                $transposed[$i2][$i1] = $data2;
            }
        }
        return $transposed;
    }
}
