<?php
// FILE SUGARCRM flav=pro ONLY
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
        $GLOBALS['db']->query("TRUNCATE TABLE {$this->rs->table_name}", "Unable to cleanup Bug 9170 Test");
	}

}
