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
 * Abstract report layout class. Encapsulates the logic of displaying data
 * depending on count of grouping fields.
 */
abstract class ExcelViewLayout 
{
    /**#@+
     * Named column indexes
     */
    const COLUMN_VERTICAL   = 0;
    const COLUMN_HORIZONTAL = 1;
    /**#@-*/

    /**
     * @var ExcelRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param ExcelRenderer $renderer
     */
    public function __construct(ExcelRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Rendering entry point
     *
     * @param MatrixReportData $report
     * @param PHPExcel_Worksheet $sheet
     * @param int $x
     * @param int $y
     */
    public function render(MatrixReportData $report, PHPExcel_Worksheet $sheet, $x, $y)
    {
        $this->renderer->beforeRender($sheet);
        $this->_render($report, $sheet, $x, $y);
        $this->renderer->afterRender($sheet);
    }

    /**
     * Renders report
     *
     * @param MatrixReportData $report
     * @param PHPExcel_Worksheet $sheet
     * @param int $x
     * @param int $y
     */
    abstract protected function _render(MatrixReportData $report, PHPExcel_Worksheet $sheet, $x, $y);

    /**
     * Renders left header
     *
     * @param PHPExcel_Worksheet $sheet
     * @param MatrixReportData $report
     * @param int $x
     * @param int $y
     */
    protected function renderLeftHeader(PHPExcel_Worksheet $sheet, MatrixReportData $report, $x, $y)
    {
        $label  = $report->getHeaderLabel(self::COLUMN_VERTICAL);
        $values = $report->getHeaderValues(self::COLUMN_VERTICAL);

        // retrieve report parameters
        $grouping_count = $report->getGroupingFieldCount();
        $display_count  = $report->getDisplayFieldCount();

        $total_label = translate('LBL_GRAND_TOTAL');
        $this->renderer->renderVerticalHeader(
            $sheet, $label, $values, $grouping_count, $display_count, $total_label, $x, $y
        );
    }
}
