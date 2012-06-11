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
 * Renderer class.
 *
 * Implements rendering of common report parts.
 */
class ExcelRenderer
{
    /**
     * Common document style
     *
     * @var array
     */
    protected $document_style = array(
        'font' => array(
            'name' => 'Arial',
            'size' => 11,
        ),
    );

    /**
     * Data table document style
     *
     * @var array
     */
    protected $table_style = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array(
                    'rgb' => '3c3c3c',
                ),
            ),
        ),
    );

    /**
     * Common header style
     *
     * @var array
     */
    protected $header_style = array(
        'font' => array(
            'bold' => true,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'rgb' => 'bfbfbf',
            ),
        ),
    );

    /**
     * Style of labels in horizontal headers
     *
     * @var array
     */
    protected $horizontal_label_style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );

    /**
     * Style of labels in vertical headers
     *
     * @var array
     */
    protected $vertical_label_style = array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
    );

    /**
     * Field formats
     *
     * @var array
     */
    protected $field_formats = array();

    /**
     * Constructor
     *
     * @param string $currency_symbol
     * @param array $currency_fields
     */
    public function __construct($currency_symbol, array $currency_fields)
    {
        $this->setFieldFormats($currency_symbol, $currency_fields);
    }

    /**
     * Prepares Excel worksheet to render
     *
     * @param PHPExcel_Worksheet $sheet
     */
    public function beforeRender(PHPExcel_Worksheet $sheet)
    {
        $sheet->getParent()->getDefaultStyle()
            ->applyFromArray($this->document_style);
    }

    /**
     * Applies formatting rules to the Excel worksheet after render
     *
     * @param PHPExcel_Worksheet $sheet
     */
    public function afterRender(PHPExcel_Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        // apply table style to resulting table
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
            ->applyFromArray($this->table_style);

        // apply auto-width to all columns
        $maxColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        foreach (range(1, $maxColumnIndex) as $columnIndex) {
            $column = PHPExcel_Cell::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)
                ->setAutoSize(true);
        }
    }
    
    /**
     * Renders vertical header.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param string $label
     * @param array $values
     * @param int $group_count
     * @param int $display_count
     * @param string $total_label
     * @param int $x
     * @param int $y
     */
    public function renderVerticalHeader(PHPExcel_Worksheet $sheet, $label, array $values, $group_count, $display_count, $total_label, $x, $y)
    {
        $y0 = $y;

        // display header label
        $sheet->mergeCellsByColumnAndRow($x, $y, $x, $y + $group_count * 2 - 3);
        $sheet->setCellValueByColumnAndRow($x, $y, $label);
        $sheet->getStyleByColumnAndRow($x, $y)
            ->applyFromArray($this->horizontal_label_style)
            ->applyFromArray($this->vertical_label_style);
        $y += ($group_count - 1) * 2;

        // display header values
        foreach ($values as $value) {
            $sheet->setCellValueByColumnAndRow($x, $y, $value);
            if ($display_count > 1) {
                $sheet->mergeCellsByColumnAndRow($x, $y, $x, $y += $display_count - 1);
            }
            $y++;
        }

        // render header "Total" label
        $sheet->setCellValueByColumnAndRow($x, $y, $total_label);
        if ($display_count > 1) {
            $sheet->mergeCellsByColumnAndRow($x, $y, $x, $y + $display_count - 1);
        }

        // apply header styling
        $this->getStyleByCoordinates($sheet, $x, $y0, $x, $y)
            ->applyFromArray($this->header_style)
            ->applyFromArray($this->vertical_label_style);
    }

    /**
     * Renders horizontal header.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param $label
     * @param array $values
     * @param int $x_scale
     * @param int $y_scale
     * @param boolean $wide_label
     * @param string $total_label
     * @param int $x
     * @param int $y
     */
    public function renderHorizontalHeader(PHPExcel_Worksheet $sheet, $label, array $values, $x_scale, $y_scale, $wide_label, $total_label, $x, $y)
    {
        $x0 = $x; $y0 = $y;

        $label_size = count($values) * $x_scale;
        if ($wide_label) {
            $label_size++;
        }

        // render header label
        $sheet->mergeCellsByColumnAndRow($x, $y, $x + $label_size - 1, $y);
        $sheet->setCellValueByColumnAndRow($x, $y, $label);
        $sheet->getStyleByColumnAndRow($x, $y)
            ->applyFromArray($this->horizontal_label_style);

        $y++;

        // render header values
        foreach ($values as $value) {
            if ($x_scale > 1) {
                $sheet->mergeCellsByColumnAndRow($x, $y, $x + $x_scale - 1, $y);
            }
            $sheet->setCellValueByColumnAndRow($x, $y, $value);
            $sheet->getStyleByColumnAndRow($x, $y)
                ->applyFromArray($this->horizontal_label_style);
            $x += $x_scale;
        }

        // render header "Total"
        if (!$wide_label) {
            $y--;
        }

        $sheet->setCellValueByColumnAndRow($x, $y, $total_label);
        $sheet->getStyleByColumnAndRow($x, $y)
            ->applyFromArray($this->horizontal_label_style)
            ->applyFromArray($this->vertical_label_style);

        if (!$wide_label) {
            $sheet->mergeCellsByColumnAndRow($x, $y, $x, $y + $y_scale * 2 - 1);
            $y++;
        }

        // apply header styling
        $this->getStyleByCoordinates($sheet, $x0, $y0, $x, $y)
            ->applyFromArray($this->header_style);
    }

    /**
     * Renders vertical footer.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param array $values
     * @param int $display_count
     * @param int $x
     * @param int $y
     */
    public function renderVerticalFooter(PHPExcel_Worksheet $sheet, array $values, $display_count, $x, $y)
    {
        foreach ($values as $i => $group) {
            $this->renderCellSet($sheet, $group, $x, $y + $i * $display_count);
        }
    }

    /**
     * Renders horizontal footer.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param array $values
     * @param int $x
     * @param int $y
     */
    public function renderHorizontalFooter(PHPExcel_Worksheet $sheet, array $values, $x, $y)
    {
        foreach ($values as $i => $group) {
            $this->renderCellSet($sheet, $group, $x + $i, $y);
        }
    }

    /**
     * Renders report body.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param array $data
     * @param int $x
     * @param int $y
     */
    public function renderBody(PHPExcel_Worksheet $sheet, array $data, $x, $y)
    {
        foreach ($data as $dy => $sy) {
            foreach ($sy as $dx => $values) {
                $this->renderCellSet(
                    $sheet, $values, $x + $dx, $y + $dy
                );
            }
        }
    }

    /**
     * Renders a set of cells representing a set of display values.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param array $values
     * @param int $x
     * @param int $y
     */
    public function renderCellSet(PHPExcel_Worksheet $sheet, array $values, $x, $y)
    {
        foreach ($values as $i => $value) {
            $sheet->setCellValueByColumnAndRow($x, $y + $i, $value);
            if (isset($this->field_formats[$i])) {
                $format = $this->field_formats[$i];
                $sheet->getStyleByColumnAndRow($x, $y + $i)
                    ->getNumberFormat()->setFormatCode($format);
            }
        }
    }

    /**
     * Helper function. Returns style of a selection specified by numeric
     * coordinates.
     *
     * @param PHPExcel_Worksheet $sheet
     * @param int $column1
     * @param int $row1
     * @param int $column2
     * @param int $row2
     * @return PHPExcel_Style
     */
    public function getStyleByCoordinates(PHPExcel_Worksheet $sheet, $column1, $row1, $column2, $row2)
    {
        $column1 = PHPExcel_Cell::stringFromColumnIndex($column1);
        $column2 = PHPExcel_Cell::stringFromColumnIndex($column2);
        return $sheet->getStyle($column1 . $row1 . ':' . $column2 . $row2);
    }

    /**
     * Sets field formats based on report definition
     *
     * @param string $currency_symbol
     * @param array $currency_fields
     */
    protected function setFieldFormats($currency_symbol, array $currency_fields)
    {
        /** @var Localization $locale */
        global $locale;
        
        $grouping_separator = $locale->getNumberGroupingSeparator();
        $precision          = $locale->getPrecision();

        if (!empty($grouping_separator)) {
            $number_format = '# ###';
        }
        else {
            $number_format = '#';
        }

        if ($precision > 0) {
            $number_format .= '.' . str_repeat('0', $precision);
        }

        $currency_prefix = !empty($currency_symbol)
            ? '"' . $currency_symbol . '"' : '';
        foreach ($currency_fields as $i => $is_system) {
            $format = $number_format;
            if ($is_system) {
                $format = $currency_prefix . $format;
            }
            $this->field_formats[$i] = $format;
        }
    }
}
