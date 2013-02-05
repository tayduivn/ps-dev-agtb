<?php
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
                'count ASC'
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
