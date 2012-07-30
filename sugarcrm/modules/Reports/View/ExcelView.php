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
 *
 */
class ExcelView
{
    /**
     * Renders specified report at the worksheet using $x and $y offset.
     *
     * @param MatrixReportData $report
     * @param PHPExcel_Worksheet $sheet
     * @param int $x
     * @param int $y
     */
    public function render(MatrixReportData $report, PHPExcel_Worksheet $sheet, $x = 0, $y = 1)
    {
        $currency_symbol = $report->getCurrencySymbol();
        $currency_fields = $report->getCurrencyFields();

        require_once 'modules/Reports/View/ExcelRenderer.php';
        $renderer = new ExcelRenderer($currency_symbol, $currency_fields);
        $layout = $this->getLayout($report, $renderer);
        if (null === $layout) {
            $grouping_count = $report->getGroupingFieldCount();
            $message = translate('ERR_MATRIX_REPORT_WRONG_GROUPING_FIELDS');
            $message = string_format($message, array($grouping_count));
            sugar_die($message);
        }
        $layout->render($report, $sheet, $x, $y);
    }

    /**
     * Returns renderer object corresponding to specified report
     *
     * @param MatrixReportData $report
     * @param ExcelRenderer $renderer
     * @return ExcelViewLayout|null
     */
    protected static function getLayout(MatrixReportData $report, ExcelRenderer $renderer)
    {
        $grouping_count = $report->getGroupingFieldCount();
        switch ($grouping_count) {
            case 2:
                require_once 'modules/Reports/View/Layout/ExcelViewLayout2D.php';
                $layout = new ExcelViewLayout2D($renderer);
                break;
            case 3:
                require_once 'modules/Reports/View/Layout/ExcelViewLayout3D.php';
                $layout = new ExcelViewLayout3D($renderer);
                break;
            default:
                $layout = null;
                break;
        }
        return $layout;
    }
}
