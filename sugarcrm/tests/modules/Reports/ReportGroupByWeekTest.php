<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Reports/Report.php';

/**
 * @covers Report
 */
class ReportGroupByWeekTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Check if Group By Week on date/datetime fields works as expected
     *
     * @dataProvider reportGroupByWeekData
     */
    public function testReportGroupByWeek($date, $expected)
    {
        $bean = SugarTestAccountUtilities::createAccount();
        $bean->update_date_entered = true;
        $bean->date_entered = TimeDate::getInstance()->asDb(new DateTime($date));
        $bean->save();

        $definition = array (
            'display_columns' =>
                array (
                ),
            'module' => 'Accounts',
            'group_defs' =>
                array (
                    0 =>
                        array (
                            'name' => 'date_entered',
                            'label' => 'Week: Date Created',
                            'column_function' => 'week',
                            'qualifier' => 'week',
                            'table_key' => 'self',
                            'type' => 'datetime',
                        ),
                ),
            'summary_columns' =>
                array (
                    0 =>
                        array (
                            'name' => 'date_entered',
                            'label' => 'Week: Date Created',
                            'column_function' => 'week',
                            'qualifier' => 'week',
                            'table_key' => 'self',
                        ),
                ),
            'report_name' => 'Group By Week',
            'chart_type' => 'none',
            'do_round' => 1,
            'chart_description' => '',
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'summary',
            'full_table_list' =>
                array (
                    'self' =>
                        array (
                            'value' => 'Accounts',
                            'module' => 'Accounts',
                            'label' => 'Accounts',
                        ),
                ),
            'filters_def' =>
                array (
                    'Filter_1' =>
                        array (
                            'operator' => 'AND',
                            0 =>
                                array (
                                    'name' => 'id',
                                    'table_key' => 'self',
                                    'qualifier_name' => 'is',
                                    'input_name0' => $bean->id,
                                ),
                        ),
                ),
        );



        $report = new Report(json_encode($definition));
        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertInternalType('array', $row);
        $this->assertEquals($expected, $row['cells'][0], 'Week not retrieved properly');
    }

    public static function reportGroupByWeekData()
    {
        return array(
            array(
                '2010-05-05',
                'W18 2010'
            ),
            array(
                '2011-07-05',
                'W27 2011'
            ),
            array(
                '2013-05-08',
                'W19 2013'
            ),
        );
    }
}
