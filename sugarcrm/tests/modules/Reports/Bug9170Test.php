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
 
require_once('modules/Reports/schedule/ReportSchedule.php');

class Bug9170Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $rs;
    
	public function setUp() 
    {
		$this->rs = new ReportSchedule();
	}

	function _testCreateScheduleProvider()
	{
	    global $timedate;
	    $start_date = gmdate($timedate->get_db_date_time_format(), mktime());
	    $tomm = gmdate($timedate->get_db_date_time_format(), mktime() + 3600 * 24);
	    return array(
	       array($start_date, 3600),
	       array($start_date, 21600),
	       array($start_date, 43200),
	       array($start_date, 86400),
	       array($start_date, 2419200),
	       array($tomm, 3600),
	       array($tomm, 21600),
	       array($tomm, 43200),
	       array($tomm, 86400),
	       array($tomm, 2419200),
	    );
	}
	/**
     * @dataProvider _testCreateScheduleProvider
     */
	public function testCreateSchedule($start_date, $interval)
	{
        global $timedate;
        $reportID = uniqid();
	    $id = $this->rs->save_schedule("","1",$reportID,$start_date, $interval,1,'pro');
	    
	    $expectedRunDateTs = strtotime($start_date . " GMT") + $interval; 
	    $expectedRunDate = gmdate($timedate->get_db_date_time_format(),$expectedRunDateTs);
	    $results = $this->rs->get_report_schedule($reportID);
        $next_run = '';
        foreach($results as $ur){
            $next_run = $ur['next_run'];
        }
	    $this->assertEquals($expectedRunDate, $next_run, "Unable to schedule report.");
	}
	
	public function testUpdateNextRun()
	{
	    global $timedate;
	    
	    $reportID = uniqid();
	    $start_ts = mktime();
	    $start_date = gmdate($timedate->get_db_date_time_format(), $start_ts);
	    $interval = 3600;
	    $id = $this->rs->save_schedule("","1",$reportID,$start_date, $interval,1,'pro');
	    
	    //Update the report schedule
	    $results = $this->rs->get_report_schedule($reportID);
        $next_run = '';
        foreach($results as $ur){
            $next_run = $ur['next_run'];
        }
	    $this->rs->update_next_run_time($id,$next_run,$results[0]['time_interval'] );
	    
	    //Get the update
	    $expectedRunDate = gmdate($timedate->get_db_date_time_format(), $start_ts + $interval);
	    $updatedResults = $this->rs->get_report_schedule($reportID);
        $next_run = '';
        foreach($updatedResults as $ur){
            $next_run = $ur['next_run'];
        }
	    $this->assertEquals($expectedRunDate, $next_run, "Unable to update scheduled report.");
	}
	
	public function tearDown() 
    {
        $GLOBALS['db']->truncateTableSQL($this->rs->table_name);
	}

}
