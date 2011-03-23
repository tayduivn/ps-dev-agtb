<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Reports/Report.php';
require_once 'modules/Reports/sugarpdf/sugarpdf.summary.php';

class Bug38016Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_report;
    private $_summary_view;
    
	public function setUp() 
    {
        $this->markTestSkipped('Skipping for now as this leads to a DB Failure error');
    	
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