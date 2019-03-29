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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Report
 */
class ReportTest extends TestCase
{
    public function providerTestGetRecordWhere()
    {
        return array(
            array('getRecordCount'),
            array('getRecordIds'),
        );
    }

    /**
     * @covers ::getRecordWhere
     * @dataProvider providerTestGetRecordWhere
     */
    public function testGetRecordWhere($method)
    {
        $mockDb = TestMockHelper::getMockForAbstractClass($this, '\\DBManager', ['query', 'supports']);
        $mockDb->method('supports')->willReturn(true);
        $report = $this->createPartialMock('\Report', [
            'create_where',
            'create_from',
        ]);
        $report->db = $mockDb;
        $report->where = '';
        $report->from = 'accounts';
        $account = $this->createPartialMock('\\Account', ['addVisibilityWhere']);
        // test if visibility check is called
        $account->expects($this->once())->method('addVisibilityWhere');
        $account->table_name = 'accounts';
        $account->db = $mockDb;
        $report->focus = $account;
        $report->$method();
    }

    /**
     * test data provider
     * @return array
     */
    public function providerTestFixGroupLabels()
    {
        return array(
            array(
                array(
                    'group_defs' => array(0 => array('name' => 'phone_alternate', 'label' => 'Alternate Phone')),
                    'summary_columns' => array(0 => array('name' => 'phone_alternate', 'label' => 'XYZ')),
                ),
                'XYZ',
            ),
            array(
                array(
                    'group_defs' => array(0 => array('name' => 'phone_alternate', 'label' => 'Alternate Phone', 'qualifier' => 'a')),
                    'summary_columns' => array(0 => array('name' => 'phone_alternate', 'label' => 'XYZ')),
                ),
                'Alternate Phone',
            ),
            array(
                array(
                    'group_defs' => array(0 => array('name' => 'phone_alternate', 'label' => 'Alternate Phone', 'qualifier' => 'a')),
                    'summary_columns' => array(0 => array('name' => 'phone_alternate', 'label' => 'XYZ', 'qualifier' => 'a')),
                ),
                'XYZ',
            ),
            array(
                array(
                    'group_defs' => array(0 => array('name' => 'phone_alternate', 'label' => 'Alternate Phone', 'qualifier' => 'a')),
                    'summary_columns' => array(0 => array('name' => 'phone_alternate', 'label' => 'XYZ', 'qualifier' => 'b')),
                ),
                'Alternate Phone',
            ),
        );
    }

    /**
     * @covers ::fixGroupLabels
     * @param array $reportDef report definitions
     * @param string $expected The expected label
     * @dataProvider providerTestFixGroupLabels
     */
    public function testFixGroupLabels($reportDef, $expected)
    {
        $report = $this->createPartialMock('\Report', ['create_where']);
        $report->report_def = $reportDef;
        $report->fixGroupLabels();
        $this->assertSame($expected, $report->report_def['group_defs'][0]['label']);
    }
}
