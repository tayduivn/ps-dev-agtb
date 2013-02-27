<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once('include/generic/LayoutManager.php');
require_once('include/generic/SugarWidgets/SugarWidgetReportField.php');

/**
 * Bug #57902
 * click Count on Calls report see message: Database failure. Please refer to sugarcrm.log for details.
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57902
 */
class Bug57902Test extends Sugar_PHPUnit_Framework_TestCase
{

    public static function dataProvider()
    {
        return array(
            array(
                array(
                    'column_key' => 'self',
                    'group_function' => 'count',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self'
                ),
                'calls__count ASC'
            ),
            array(
                array(
                    'column_function' => 'avg',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'avg',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_avg_duration_hours ASC'
            ),
            array(
                array(
                    'column_function' => 'max',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'max',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_max_duration_hours ASC'
            ),
            array(
                array(
                    'column_function' => 'min',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'min',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_min_duration_hours ASC'
            ),
            array(
                array(
                    'column_function' => 'sum',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'sum',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_sum_duration_hours ASC'
            )
        );

    }

    /**
     * Test asserts that for group functions order by is alias instead of table.field
     *
     * @dataProvider dataProvider
     * @group 57902
     * @return void
     */
    public function testQueryOrderBy($layout_def, $expected)
    {
        $layoutManager = new LayoutManager();
        $sugarWidgetReportField = new SugarWidgetReportField($layoutManager);

        $actual = $sugarWidgetReportField->queryOrderBy($layout_def);

        $this->assertEquals($expected, $actual, 'ORDER BY string is incorrect');
    }
}
