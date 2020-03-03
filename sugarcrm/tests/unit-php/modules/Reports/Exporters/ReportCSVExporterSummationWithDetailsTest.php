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
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummationWithDetails;

/**
 * @coversDefaultClass ReportCSVExporterSummationWithDetails
 */
class ReportCSVExporterSummationWithDetailsTest extends TestCase
{
    static protected $IdxToPass = 3;

    protected function setUp() : void
    {
        global $current_user;

        // to set up Delimiter
        $current_user = $this->createPartialMock('User', ['getPreference']);

        $preferenceMap = array(
            array('export_delimiter', ','),
            array('currency', '-99'),
        );

        $current_user->expects($this->any())
            ->method('getPreference')
            ->will($this->returnValueMap($preferenceMap));
    }

    protected function tearDown() : void
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummationWithDetails::export
     * @dataProvider summationWithDetailsExportProvider
     */
    public function testExport(
        array $headerRow1,
        array $dataRows1,
        array $headerRow2,
        array $dataRows2,
        string $expected
    ) {
        $reporter = $this->createPartialMock(
            '\Report',
            array(
                'run_summary_query',
                'run_summary_combo_query',
                'run_total_query',
                '_load_currency',
                'get_summary_total_row',
                'get_header_row',
                'get_next_row',
                'get_summary_header_row',
                'get_summary_next_row',
                'get_total_header_row',
            )
        );

        $reporter->report_type = 'detailed_summary';
        $reporter->report_def = array(
            'summary_columns' => array(
                array(
                    'name' => 'name',
                    'label' => 'Superhero Name',
                    'table_key' => 'self',
                ),
                array(
                    'name' => 'property_value',
                    'label' => 'Value of Total Property Owned',
                    'field_type' => 'currency',
                    'group_function' => 'sum',
                    'table_key' => 'Superheroes:property',
                ),
            ),
        );

        // SUMMATION HEADER
        $reporter->method('get_summary_header_row')
            ->willReturn($headerRow1);

        // BEGIN buildTree
        $callsThusFar = 2;
        for ($i = 0; $i < count($dataRows1); $i++) {
            /* Clarification about the at method: it counts the number of calls to member functions of a mock object,
               regardless of which member function it is */

            // SUMMATION HEADER
            $reporter->expects($this->at($callsThusFar + 1))
                ->method('get_summary_next_row')
                ->willReturn($dataRows1[$i]);

            $callsThusFar += 1;
        }

        $reporter->expects($this->at(self::$IdxToPass + count($dataRows1)))
            ->method('get_summary_next_row')
            ->willReturn(0);

        $callsThusFar++;
        // END buildTree

        // BEGIN DETAILS
        for ($i = 0; $i < count($dataRows1); $i++) {
            $reporter->expects($this->at($callsThusFar + 1))
                ->method('get_header_row')
                ->willReturn($headerRow2);

            $reporter->expects($this->at($callsThusFar + 2))
                ->method('get_next_row')
                ->willReturn($dataRows2[$i]);

            $reporter->expects($this->at($callsThusFar + 3))
                ->method('get_summary_header_row')
                ->willReturn($headerRow1);

            $callsThusFar += 3;
        }
        // END DETAILS

        $csvMaker = $this->createMockExporter($reporter);
        $csvMaker->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn("Grand Total\r\nThe Grand Total Goes Here");

        $actual = $csvMaker->export();
        $this->assertEquals($expected, $actual);
    }

    public function summationWithDetailsExportProvider(): array
    {
        // this is the summation information
        $headerRow1 = array('Superhero Name', 'Value of Total Property Owned');
        $dataRows1 = array(
            array(
                'cells' => ['Iron Man', '$12,400,000,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['Batman', '$9,200,000,000'],
                'count' => 1,
            ),
            array(
                'cells' => ['Superman', '$2,400,000'],
                'count' => 1,
            ),
        );

        // this is the "details" information
        $headerRow2 = array('Name', 'Property Value');
        $dataRows2 = array(
            // Iron Man
            array('cells' => array('Avengers Tower', '$12,400,000,000')),
            // Batman
            array('cells' => array('Wayne Manor', '$9,200,000,000')),
            // Superman
            array('cells' => array('Fortress of Solitude', '$2,400,000')),
        );

        $expected1 = "\"Superhero Name = Iron Man, Value of Total Property Owned = $12,400,000,000\"\r\n" .
            "\"Name\",\"Property Value\"\r\n" .
            "\"Avengers Tower\",\"$12,400,000,000\"\r\n" .
            "\r\n" .
            "\"Superhero Name = Batman, Value of Total Property Owned = $9,200,000,000\"\r\n" .
            "\"Name\",\"Property Value\"\r\n" .
            "\"Wayne Manor\",\"$9,200,000,000\"\r\n" .
            "\r\n" .
            "\"Superhero Name = Superman, Value of Total Property Owned = $2,400,000\"\r\n" .
            "\"Name\",\"Property Value\"\r\n" .
            "\"Fortress of Solitude\",\"$2,400,000\"\r\n" .
            "\r\n" .
            "\r\n" .
            "Grand Total\r\n" .
            "The Grand Total Goes Here";

        return array(
            array($headerRow1, $dataRows1, $headerRow2, $dataRows2, $expected1),
        );
    }

    public function createMockExporter(\Report $reporter)
    {
        $mockExporter = $this->createPartialMock(ReportCSVExporterSummationWithDetails::class, array('getGrandTotal'));
        TestReflection::setProtectedValue($mockExporter, 'reporter', $reporter);
        return $mockExporter;
    }
}
