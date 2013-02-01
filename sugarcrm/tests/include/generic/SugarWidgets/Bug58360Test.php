<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'modules/Reports/Report.php';
require_once 'include/generic/LayoutManager.php';

/**
 * Bug #58360
 * Lead Report Inconsistencies
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58360
 */
class Bug58360Test extends Sugar_PHPUnit_Framework_TestCase
{
    function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'1999-12-31 00:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'1999-12-31 23:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'2000-01-01 00:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'2000-01-01 23:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));
    }

    function tearDown()
    {
        $GLOBALS['timedate']->setUser();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dataProvider
     */
    function testDayMonthYear($timezone, $qualifier, $expected)
    {
        $GLOBALS['current_user']->setPreference('timezone', $timezone);
        $GLOBALS['timedate']->setUser($GLOBALS['current_user']);

        $layout_def = array(
            'column_function' => $qualifier,
            'column_key' => 'self:date_modified',
            'force_label' => 'Day: Date Modified',
            'name' => 'date_modified',
            'qualifier' => $qualifier,
            'table_alias' => 'leads',
            'table_key' => 'self',
            'type' => 'datetime'
        );

        $layoutManager = new LayoutManager();
        $layoutManager->default_widget_name = 'ReportField';
        $layoutManager->setAttributePtr('reporter', new Report());

        $layoutManager->setAttribute('context', 'GroupBy');
        $group_by = $layoutManager->widgetQuery($layout_def);
        $layoutManager->setAttribute('context', 'Select');
        $select = $layoutManager->widgetQuery($layout_def);

        $actual = array();
        $result = $GLOBALS['db']->query("SELECT {$select}, COUNT(*) count FROM leads WHERE id IN ('" . implode("', '", SugarTestLeadUtilities::getCreatedLeadIds()) . "') GROUP BY " . $group_by);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $date = reset($row);
            $count = next($row);
            $this->assertArrayNotHasKey($date, $actual, $date . ' already present in result');
            $actual[$date] = $count;
        }

        $this->assertEquals($expected, $actual, 'Group by sent incorrect query');
    }

    function dataProvider()
    {
        return array(
            array('GMT', 'day', array(
                '1999-12-31' => '2',
                '2000-01-01' => '2'
            )),
            array('Africa/Algiers', 'day', array(
                '1999-12-31' => '1',
                '2000-01-01' => '2',
                '2000-01-02' => '1'
            )),
            array('Atlantic/Azores', 'day', array(
                '1999-12-30' => '1',
                '1999-12-31' => '2',
                '2000-01-01' => '1'
            )),

            array('GMT', 'month', array(
                '1999-12' => '2',
                '2000-01' => '2'
            )),
            array('Africa/Algiers', 'month', array(
                '1999-12' => '1',
                '2000-01' => '3'
            )),
            array('Atlantic/Azores', 'month', array(
                '1999-12' => '3',
                '2000-01' => '1'
            )),

            array('GMT', 'year', array(
                '1999' => '2',
                '2000' => '2'
            )),
            array('Africa/Algiers', 'year', array(
                '1999' => '1',
                '2000' => '3'
            )),
            array('Atlantic/Azores', 'year', array(
                '1999' => '3',
                '2000' => '1'
            ))
        );
    }
}
