<?php
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

require_once "modules/Tasks/Task.php";

class TasksTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function setUp()
    {
        $_REQUEST['module'] = 'Tasks';
    }

    public function tearDown()
    {
        unset($_REQUEST['module']);
        if(!empty($this->taskid)) {
            $GLOBALS['db']->query("DELETE FROM tasks WHERE id='{$this->taskid}'");
        }
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
        $this->assertEquals($GLOBALS['timedate']->to_display_time("15:00:00"), $listViewFields['TIME_DUE']);
    }

    /**
     * @group bug40999
     */
    public function testTaskStatus()
    {
         $task = new Task();
         $this->taskid = $task->id = create_guid();
         $task->new_with_id = 1;
         $task->status = 'Done';
         $task->save();
         // then retrieve
         $task = new Task();
         $task->retrieve($this->taskid);
         $this->assertEquals('Done', $task->status);
    }

    /**
     * @group bug40999
     */
    public function testTaskEmptyStatus()
    {
         $task = new Task();
         $this->taskid = $task->id = create_guid();
         $task->new_with_id = 1;
         $task->save();
         // then retrieve
         $task = new Task();
         $task->retrieve($this->taskid);
         $this->assertEquals('Not Started', $task->status);
    }

}
