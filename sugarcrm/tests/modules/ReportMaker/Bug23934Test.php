<?php
//FILE SUGARCRM flav=ent ONLY 
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
 
require_once('modules/Reports/schedule/ReportSchedule.php');

class Bug23934Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $_reportId = "ad832c9b-59be-bf94-9b8d-4cdab4d3f1e8";
   	public function setUp() 
    {
		$query = "INSERT INTO report_maker VALUES('1', '1', '{$this->_reportId}', 0, '2010-11-10 15:05:52', '2010-11-10 15:05:52', '1', '1', '6 month Sales Pipeline Report', '6 month Sales Pipeline Report', 'center', 'Opportunities over the next 6 months broken down by month and type', 0)";
		$GLOBALS['db']->query($query);
		
		$query2 = "INSERT INTO report_schedules VALUES('728f6b40-2f41-01c6-6e13-4cdac466c862', '1', '{$this->_reportId}', '2010-11-09 18:00:00', '2010-11-11 10:00:00', 1, 3600, NULL, 'ent', 0)";
		$GLOBALS['db']->query($query2);
    }

	public function testGetEntReportsToEmail(){
		$rs = new ReportSchedule();
		$results = $rs->get_ent_reports_to_email('1');
		$this->assertArrayHasKey($this->_reportId, $results, "Report Maker Id does not exist in the results to email.");
	}
	
	public function tearDown() 
    {
        $GLOBALS['db']->query("DELETE FROM report_maker WHERE id = '{$this->_reportId}'", "Unable to cleanup Bug 23934 Test");
        $GLOBALS['db']->query("DELETE FROM report_schedules WHERE id = '728f6b40-2f41-01c6-6e13-4cdac466c862'", "Unable to cleanup Bug 23934 Test");
	}

}