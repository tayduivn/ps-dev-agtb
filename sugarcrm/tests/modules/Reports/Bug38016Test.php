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
 
require_once 'modules/Reports/Report.php';
require_once 'modules/Reports/sugarpdf/sugarpdf.summary.php';

class Bug38016Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_report;
    private $_summary_view;
    
	public function setUp() 
    {
        $this->markTestIncomplete('Skipping for now as this leads to a DB Failure error');
    	
		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
		$this->_report = new Report('{"display_columns":[],"module":"Accounts","group_defs":[{"name":"id","label":"ID","table_key":"self","type":"id"}],"summary_columns":[{"name":"id","label":"ID","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"Accounts:opportunities"}],"report_name":"Bug38016Test","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"Accounts:opportunities:amount:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"},"Accounts:opportunities":{"name":"Accounts  >  Opportunity","parent":"self","link_def":{"name":"opportunities","relationship_name":"accounts_opportunities","bean_is_lhs":true,"link_type":"many","label":"Opportunity","table_key":"Accounts:opportunities"},"dependents":["display_summaries_row_2"],"module":"Opportunities","label":"Opportunity"}},"filters_def":{"Filter_1":{"operator":"AND"}}}');
		$GLOBALS['module'] = 'Reports';
		$this->_summary_view = new ReportsSugarpdfSummary();
		$this->_summary_view->bean = &$this->_report;
	}

	public function tearDown() 
    {
        unset($GLOBALS['module']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
	}
	
	public function testSummationQueryMadeWithoutCountColumn()
	{
        @$this->_summary_view->display();
        $this->assertTrue(!empty($this->_report->total_query));    
	}
}