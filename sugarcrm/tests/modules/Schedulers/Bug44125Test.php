<?php
require_once 'modules/Schedulers/Scheduler.php';

class Bug44215Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $testScheduler;
	
	public function setUp()
    {
	    unset($GLOBALS['disable_date_format']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h:ia");
		$GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");    	
        $this->testScheduler = new Bug44215MockTestScheduler();
        $this->testScheduler->save(); 
    }

    public function tearDown()
    {
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);    
        $GLOBALS['db']->query("DELETE FROM schedulers WHERE id = '" . $this->testScheduler->id . "'");
        $GLOBALS['db']->query("DELETE FROM schedulers_times WHERE scheduler_id = '" . $this->testScheduler->id . "'");
    }

    
    public function testFlushDeadJobs()
    {
		$this->testScheduler->fire();
		$this->testScheduler->status = 'In Progress';
		$this->testScheduler->save();
 		$this->assertEquals('In Progress', $this->testScheduler->status, "Assert that the test scheduler instance has status of 'In Progress'");
 		
 		$result = $GLOBALS['db']->query("SELECT id FROM schedulers_times WHERE scheduler_id ='{$this->testScheduler->id}'");
 		$jobCount = 0;
 		
 		while($row = $GLOBALS['db']->fetchByAssoc($result)) 
 		{
 			$job = new SchedulersJob();
 			$job->retrieve($row['id']);
 			$job->execute_time = $this->testScheduler->date_time_start; //Set this to the start time of the scheduler which is in year 2005
 			$job->save();
 			$jobCount++;
 		}
 		
 		$this->assertTrue($jobCount > 0, "Assert that we created schedulers_times entries");
		$this->testScheduler->flushDeadJobs();
		
		$this->testScheduler->retrieve($this->testScheduler->id);
		$this->assertEquals('Active', $this->testScheduler->status, "Assert that the status for scheduler is set to 'Active'");
    }


}

function Bug44215TestFunction()
{
	//Could do something here, but don't need to
	return false;
}

//Mock Scheduler bean for the test scheduler
class Bug44215MockTestScheduler extends Scheduler
{
    public $fired = false;
    public $name = "Bug44215MockTestScheduler";
    public $date_time_start = '2005-01-01 19:00:00';
    public $job_interval = '*::*::*::*::*';
    public $job = 'function::Bug44215TestFunction';
}