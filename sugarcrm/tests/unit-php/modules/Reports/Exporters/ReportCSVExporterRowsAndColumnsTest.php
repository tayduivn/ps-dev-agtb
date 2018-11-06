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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports\Exporters;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\modules\Reports\unformat_number;
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterRowsAndColumns;

/**
 * @coversDefaultClass ReportCSVExporterRowsAndColumns
 */
class ReportCSVExporterRowsAndColumnsTest extends TestCase
{
    static protected $IdxToPass = 3;

    public function setUp()
    {
        global $current_user;

        // to setup Delimiter
        $current_user = $this->createPartialMock('User', ['getPreference']);

        $preferenceMap = array(
            array('export_delimiter', ','),
            array('currency', '-99'),
        );

        $current_user->expects($this->any())
            ->method('getPreference')
            ->will($this->returnValueMap($preferenceMap));
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @param array $headerRow The headers of the main table
     * @param array $dataRows Contains rows of data that Report::get_next_row() will return when called
     * @param string $expected The expected csv output
     * @covers Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterRowsAndColumns::export
     * @dataProvider rowsAndColumnsExportProvider
     */
    public function testExportRowsAndColumns(
        array $headerRow,
        array $dataRows,
        string $expected
    ) {
        $reporter = $this->createPartialMock(
            '\Report',
            ['run_summary_query',
            'run_query',
            'run_summary_combo_query',
            'run_total_query',
            '_load_currency',
            'get_summary_header_row',
            'get_total_header_row',
            'get_next_row',
            'get_summary_total_row',
            'get_summary_next_row',
            'get_header_row',
            'getDataTypeForColumnsForMatrix']
        );

        $reporter->report_type = 'tabular';

        $reporter->method('get_header_row')
            ->willReturn($headerRow);

        for ($i = 0; $i < count($dataRows); $i++) {
            $reporter->expects($this->at(self::$IdxToPass + $i))
                ->method('get_next_row')
                ->willReturn($dataRows[$i]);
        }

        $reporter->expects($this->at(self::$IdxToPass + count($dataRows)))
            ->method('get_next_row')
            ->willReturn(0);

        $csvMaker = new ReportCSVExporterRowsAndColumns($reporter);

        // for global function from_html
        include_once 'include/utils/db_utils.php';

        $this->assertEquals($expected, $csvMaker->export());
    }

    public function rowsAndColumnsExportProvider()
    {
        $headerRow1 = array('Name', 'Universe', 'Total Property Owned');
        $dataRows1 = array(
            array(
                'cells' => ['Iron Man', 'Marvel', '$12,400,000,000'],
            ),
            array(
                'cells' => ['Bat Man', 'DC', '$9,200,000,000'],
            ),
            array(
                'cells' => ['Superman', 'DC', '$2,400,000'],
            ),
        );

        $expected1 = "\"Name\",\"Universe\",\"Total Property Owned\"\r\n" .
            "\"Iron Man\",\"Marvel\",\"$12,400,000,000\"\r\n" .
            "\"Bat Man\",\"DC\",\"$9,200,000,000\"\r\n" .
            "\"Superman\",\"DC\",\"$2,400,000\"\r\n";

        return array(
            array($headerRow1, $dataRows1, $expected1),
        );
    }
}
