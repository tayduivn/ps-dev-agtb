<?php

require_once 'include/TimeDate.php';
require_once('modules/Calendar/DateTimeUtil.php');
require_once 'modules/Calendar/Calendar.php';
require_once 'modules/Tasks/Task.php';

/**
 * @group bug40908
 */
class Bug40908Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $task ='';
    public function setUp()
    {
    	$GLOBALS['reload_vardefs'] = true;
        global $current_user;
		
        $current_user = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $GLOBALS['reload_vardefs'] = false;
        //remove task
        $GLOBALS['db']->query('DELETE FROM tasks WHERE id = \''.$this->task->id.'\' ');
			
    }
    
    public function testDateAndTimeShownInCalendarActivity()
    {
        global $timedate,$sugar_config,$DO_USER_TIME_OFFSET , $current_user;
		
		$start_time = gmdate('Y-m-d H:i:s', strtotime('7:15 am'));
		$end_time = gmdate('Y-m-d H:i:s', strtotime('5:15 pm'));

		//create the new task with todays date
        $task = new Task();
        $task->object_name == 'Task';
        $task->date_start = $start_time;
        $task->date_due   = $end_time;
        $task->name 	  = "Task for Bug40908Test.php on ".date('Y-m-d H:i:s');
        $task->status 	  = "In Progress";
        $task->priority   = "High";
        $task->save(); //save task
        $this->task = $task->retrieve($task->id); //retrieve task so we can get dates properly formatted 
        
        //create a calendar activity out of this task
        $cal_act = new CalendarActivity($this->task);
        
        //retrieve the date through DateTimeUtil as Calendar.php does to render activities
        //format should be Y-m-d:G  For example, Jan 1 od 2011 at 8am would be 2011-01-01:8
        $act_hash_list  =DateTimeUtil::getHashList('day', $cal_act->start_time, $cal_act->end_time);

        //make sure times match up
        $formattedTaskDate = date('Y-m-d:G',strtotime($cal_act->sugar_bean->date_start));
        $error = 'The time retrieved from Calendar Activity and DateTimeUtil (\''.$act_hash_list[0].'\') did not match up with formatted date (\''.$formattedTaskDate.'\')';
        $this->assertEquals($act_hash_list[0] , $formattedTaskDate,$error);
    }
}