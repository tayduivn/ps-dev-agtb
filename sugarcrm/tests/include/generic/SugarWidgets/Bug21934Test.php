<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
        array_push($expect, clone($start));
        $start->setDate($start->year, $start->month, $start->days_in_month);
        array_push($expect, clone($start));

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
