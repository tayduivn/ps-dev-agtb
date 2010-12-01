<?php
require_once "modules/Tasks/Task.php";

class TasksTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_REQUEST['module'] = 'Tasks';
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if(!empty($this->taskid)) {
            $GLOBALS['db']->query("DELETE FROM tasks WHERE id='{$this->taskid}'");
        }
        unset($GLOBALS['current_user']);
        unset($_REQUEST['module']);
    }

    /**
     * @ticket 39259
     */
    public function testListviewTimeDueFieldProperlyHandlesDst()
    {
        $task = new Task();
        $task->name = "New Task";
        $task->date_due = $GLOBALS['timedate']->to_display_date_time("2010-08-30 15:00:00");
        $listViewFields = $task->get_list_view_data();

        $this->assertEquals($listViewFields['TIME_DUE'], $GLOBALS['timedate']->to_display_time("15:00:00"));
    }

    public function testTaskEmptyStatus()
    {
         $task = new Task();
         $this->taskid = $task->id = create_guid();
         $task->new_with_id = 1;
         $task->save();
         // then retrieve
         $task = new Task();
         $task->retrieve($this->taskid);
         $this->assertEquals('Planned', $task->status);
    }

}
