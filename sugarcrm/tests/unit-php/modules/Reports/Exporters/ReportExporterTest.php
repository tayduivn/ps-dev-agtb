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
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass ReportExporter
 */
class ReportExporterTest extends TestCase
{
    protected function setUp() : void
    {
        global $current_user;

        // to setup Delimiter
        $current_user = $this->createPartialMock('User', ['getPreference']);

        $preference_map = array(
            array('export_delimiter', ','),
            array('currency', '-99'),
        );

        $current_user->expects($this->any())
            ->method('getPreference')
            ->will($this->returnValueMap($preference_map));
    }

    protected function tearDown() : void
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @param string $reportType Report Type
     * @param string $layoutOptions Reporter's layout options
     * @param string $format output format
     * @param string $exporterClass expected exporter class
     * @covers Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter::__construct
     * @dataProvider reportExporterProvider
     */
    public function testReportExporter(
        string $reportType,
        string $layoutOptions,
        string $format,
        string $exporterClass
    ) {
        $reporter = $this->createPartialMock('\Report', array('getReportType'));
        $reporter->method('getReportType')
            ->willReturn($reportType);

        $reporter->report_def = array('layout_options' => $layoutOptions);
        $reporter->report_type = $reportType;

        $exporter = $this->getMockBuilder('Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter')
            ->setConstructorArgs(array($reporter, $format))
            ->getMock();

        // member variable $exporter should be an instance of the correct exporter class
        $this->assertInstanceOf(
            $exporterClass,
            TestReflection::getProtectedValue($exporter, 'exporter')
        );
    }

    public function reportExporterProvider()
    {
        return array(
            array(
                'summary',
                '',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummation',
            ),
            array(
                'detailed_summary',
                '',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummationWithDetails',
            ),
            array(
                'tabular',
                '',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterRowsAndColumns',
                '',
            ),
            array(
                'Matrix',
                '2x2',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix1x1',
            ),
            array(
                'Matrix',
                '1x2',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix1x2',
            ),
            array(
                'Matrix',
                '2x1',
                'CSV',
                'Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix2x1',
            ),
        );
    }
}
