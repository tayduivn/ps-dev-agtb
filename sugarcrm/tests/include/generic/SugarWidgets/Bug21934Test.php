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

require_once "modules/Reports/Report.php";
require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFielddatetime.php";
require_once "include/SugarDateTime.php";
/**
 * Bug 21934:
 *  Report filters are applying time offsets to date fields
 * @ticket 21934
 * @author arymarchik@sugarcrm.com
 **/
class Bug21934Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('timedate');
        global $timedate;
        $timedate->allow_cache = false;
        $timedate->clearCache();
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        global $timedate;
        $timedate->allow_cache = true;
    }

    /**
    * Testing correct time offset in month queries
    * @group 21934
    */
    public function testQueryMonth()
    {
        global $current_user;
        $rep_defs =array (
            'assigned_user_id' => $current_user->id,
            'filters_def' =>
            array (
            ),
        );
        $_layoutDef = array(
            'name'          => 'custom_date_c',
            'table_key'     => 'self',
            'qualifier_name'=> 'tp_this_month',
            'input_name0'   => 'tp_this_month',
            'input_name1'   => 'on',
            'table_alias'   => 'accounts_cstm',
            'column_key'    => 'self:custom_date_c',
            'type'          => 'date',
        );
        $json = getJSONobj();
        $tmp = $json->encode($rep_defs);
        $report = new Report($tmp);

        $lm = new LayoutManager();
        $lm->setAttribute('reporter', $report);

        $widget  = $this->getMock('SugarWidgetFieldDate', array('get_start_end_date_filter'), array($lm));
        $widget->expects($this->any())
            ->method('get_start_end_date_filter')
            ->will($this->returnCallback(array($this, '_mockCallBack')));

        $current_user->setPreference('timezone', 'Pacific/Tongatapu');

        $start = new SugarDateTime();
        $start->setTimezone(new DateTimeZone('UTC'));
        $start->setDate($start->year, $start->month-1, 1);

        $expect = array();
        array_push($expect, $start->asDbDate());
        $start->setDate($start->year, $start->month, $start->days_in_month);
        array_push($expect, $start->asDbDate());

        $result = $widget->queryFilterTP_last_month($_layoutDef);
        $this->assertEquals($expect, $result);

        $current_user->setPreference('timezone', 'Pacific/Midway');
        $result = $widget->queryFilterTP_last_month($_layoutDef);
        $this->assertEquals($expect, $result);
    }

    public function _mockCallBack()
    {
        $argv = func_get_args();
        return array($argv[1], $argv[2]);
    }
}
