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
 * Two-dimensional matrix report layout
 */
class ExcelViewLayout2D extends ExcelViewLayout
{
    /**
     * Renders two-dimensional matrix report
     *
     * @param MatrixReportData $report
     * @param PHPExcel_Worksheet $sheet
     * @param int $x
     * @param int $y
     */
    protected function _render(MatrixReportData $report, PHPExcel_Worksheet $sheet, $x, $y)
    {
        $this->renderLeftHeader($sheet, $report, $x, $y);
        $this->renderTopHeader($sheet, $report, $x + 1, $y);

        // retrieve report parameters
        $grouping_count = $report->getGroupingFieldCount();
        $display_count  = $report->getDisplayFieldCount();

        // render body
        $this->renderBody($sheet, $report, $x + 1, $y += $grouping_count * 2 - 2);

        // retrieve of values corresponding to each header
        $vertical_size = $report->getColumnSize(self::COLUMN_VERTICAL);
        $horizontal_size = $report->getColumnSize(self::COLUMN_HORIZONTAL);

        $this->renderRightFooter($sheet, $report, $x + $horizontal_size + 1, $y);
        $this->renderBottomFooter($sheet, $report, $x + 1, $y + $vertical_size * $display_count);
        $this->renderTotal($sheet, $report, $x + 1, $y);
    }

    /**
     * Renders top header
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderTopHeader(PHPExcel_Worksheet $sheet,
                                       MatrixReportData $report, $x, $y)
    {
        $label = $report->getHeaderLabel(self::COLUMN_HORIZONTAL);
        $values = $report->getHeaderValues(self::COLUMN_HORIZONTAL);

        $total_label = translate('LBL_GRAND_TOTAL');
        $this->renderer->renderHorizontalHeader(
            $sheet, $label, $values, 1, 1, false, $total_label, $x, $y
        );
    }

    /**
     * Renders right footer
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderRightFooter(PHPExcel_Worksheet $sheet,
                                         MatrixReportData $report, $x, $y)
    {
        $values = $report->getTotals(self::COLUMN_VERTICAL);
        $display_count = $report->getDisplayFieldCount();

        $this->renderer->renderVerticalFooter(
            $sheet, $values, $display_count, $x, $y
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
    protected function renderBottomFooter(PHPExcel_Worksheet $sheet,
                                          MatrixReportData $report, $x, $y)
    {
        $values = $report->getTotals(self::COLUMN_HORIZONTAL);
        $this->renderer->renderHorizontalFooter(
            $sheet, $values, $x, $y
        );
    }

    /**
     * Renders body
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderBody(PHPExcel_Worksheet $sheet,
                                  MatrixReportData $report, $x, $y)
    {
        $data = $report->getData();
        $this->renderer->renderBody($sheet, $data, $x, $y);
    }

    /**
     * Renders report total
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderTotal($sheet, MatrixReportData $report, $x, $y)
    {
        // retrieve report parameters
        $vertical_size   = $report->getColumnSize(self::COLUMN_VERTICAL);
        $horizontal_size = $report->getColumnSize(self::COLUMN_HORIZONTAL);
        $display_count   = $report->getDisplayFieldCount();

        // render grand total
        $total = $report->getGrandTotal();
        $this->renderer->renderCellSet(
            $sheet, $total, $x + $horizontal_size, $y + $vertical_size * $display_count
        );
    }
}
