<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter;

class ReportCSVExporterMatrix1x1Test extends TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param string $layoutOption The layout option of the matrix, can be 2x2, 1x2 or 2x1
     * @param array $detailHeaders The detailed header information of the data
     * @param array $groupDefs The group definition of this matrix
     * @param array $dataRows The data, in rows
     * @param string $expected The expected csv output
     * @covers ReportCSVExporterMatrix2x1::export
     * @covers ReportCSVExporterBase::getReportType
     * @dataProvider matrixProvider
     */
    public function testExportMatrix(
        string $layoutOption,
        array $detailHeaders,
        array $groupDefs,
        array $dataRows,
        string $expected
    ) {
        // Fake a Report class so it won't run un-wanted php script
        $reporter = $this->getMockBuilder('nonexistant')
            ->setMockClassName('Report')
            ->setMethods(['run_summary_query',
                'run_summary_combo_query',
                'run_total_query',
                '_load_currency',
                'get_summary_header_row',
                'get_total_header_row',
                'get_next_row',
                'get_summary_total_row',
                'get_summary_next_row',
                'get_header_row',
                'getReportType',
                'getDataTypeForColumnsForMatrix'])
            ->getMock();

        $reporter->report_type = 'summary';
        $reporter->report_def = array(
            'layout_options' => $layoutOption,
            'group_defs' => $groupDefs,
        );

        $reporter->method('getDataTypeForColumnsForMatrix')
            ->willReturn($detailHeaders);

        $headers = array();
        foreach ($detailHeaders as $detail_header) {
            $headers[] = $detail_header['label'];
        }

        $reporter->method('get_summary_header_row')
            ->willReturn($headers);

        $reporter->method('getReportType')
            ->willReturn('Matrix');


        $dataCount = count($dataRows);
        $reporter->expects($this->any())
            ->method('get_summary_next_row')
            ->willReturnCallback(function () use (&$dataCount, $dataRows) {
                if ($dataCount > 0) {
                    $dataCount--;
                    return $dataRows[count($dataRows) - $dataCount - 1];
                }

                return 0;
            });

        include_once 'modules/Currencies/Currency.php';
        $csvMaker = new ReportExporter($reporter);

        $this->assertEquals($expected, $csvMaker->export());
    }

    public function matrixProvider()
    {
        // 2x1 matrix
        $layoutOption1 = '2x1';

        $detailHeaders1 = array(
            'Area' => array(
                'label' => 'Area',
                'type' => 'nothing',
            ),
            'Time' => array(
                'label' => 'Time',
                'type' => 'nothing',
            ),
            'Games' => array(
                'label' => 'Games',
                'type' => 'nothing',
            ),
            'Min' => array(
                'group_function' => 'min',
                'label' => 'Min',
                'type' => 'nothing',
            ),
            'Count' => array(
                'group_function' => 'count',
                'label' => 'Count',
                'type' => 'nothing',
            ),
            'Sum' => array(
                'group_function' => 'sum',
                'label' => 'Sum',
                'type' => 'nothing',
            ),
            'AVG' => array(
                'group_function' => 'avg',
                'label' => 'AVG',
                'type' => 'nothing',
            ),
            'Max' => array(
                'group_function' => 'max',
                'label' => 'Max',
                'type' => 'nothing',
            ),
        );

        $groupDefs1 = array(
            array(
                'label' => 'Area',
            ),
            array(
                'label' => 'Time',
            ),
            array(
                'label' => 'Games',
            ),
        );

        $dataRows1 = array(
            array(
                'cells' => ['Asia', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['Asia', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['Europe', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['Europe', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['America', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ),
            array(
                'cells' => ['America', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ),
        );

        $expected1 = "\"\",\"\",\"Games\",\"\"\r\n"
            . "\"Area\",\"Time\",\"CS:GO\",\"Grand Total\"\r\n"
            . "\"Asia\",\"Day\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Night\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Total\",\"1000\",\"1000\"\r\n"
            . "\"\",\"\",\"200\",\"200\"\r\n"
            . "\"\",\"\",\"2000\",\"2000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10000\",\"10000\"\r\n"
            . "\"Europe\",\"Day\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Night\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Total\",\"1000\",\"1000\"\r\n"
            . "\"\",\"\",\"200\",\"200\"\r\n"
            . "\"\",\"\",\"2000\",\"2000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10000\",\"10000\"\r\n"
            . "\"America\",\"Day\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Night\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"100\",\"100\"\r\n"
            . "\"\",\"\",\"1,000\",\"1000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10,000\",\"10000\"\r\n"
            . "\"\",\"Total\",\"1000\",\"1000\"\r\n"
            . "\"\",\"\",\"200\",\"200\"\r\n"
            . "\"\",\"\",\"2000\",\"2000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10000\",\"10000\"\r\n"
            . "\"\",\"Grand Total\",\"1000\",\"1000\"\r\n"
            . "\"\",\"\",\"600\",\"600\"\r\n"
            . "\"\",\"\",\"6000\",\"6000\"\r\n"
            . "\"\",\"\",\"10\",\"10\"\r\n"
            . "\"\",\"\",\"10000\",\"10000\"\r\n";

        return array(
            array($layoutOption1, $detailHeaders1, $groupDefs1, $dataRows1, $expected1),
        );
    }
}