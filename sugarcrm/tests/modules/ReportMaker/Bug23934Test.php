<?php
//FILE SUGARCRM flav=ent ONLY 
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