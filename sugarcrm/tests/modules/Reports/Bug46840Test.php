<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Reports/Report.php');

class Bug46840Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $reportInstance;

	public function setUp()
    {
    	$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
	}

	function testQuarter()
	{
	    // Summary report for Opportunities grouped by quarter
	    $report = new Report('{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Quarter: Expected Close Date","column_function":"quarter","qualifier":"quarter","table_key":"self","type":"date"}],"summary_columns":[{"name":"date_closed","label":"Quarter: Expected Close Date","column_function":"quarter","qualifier":"quarter","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"test report","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"operator":"AND"}}}');
	    $report->run_summary_query();
	    $this->assertNotEmpty($report->summary_result);
	}
}
