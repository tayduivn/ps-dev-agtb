<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once "modules/ProjectTask/ProjectTask.php";
require_once "modules/Project/Project.php";

class Bug49310Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $project;
	public $projectTasks = array ();

	/**
	 * Different values, nevermind, 0-100
	 */
	public $oldPercentValue = 34;
	public $newPercentValue = 33;
	public $defaultStaticSecondPercent = 56;
	/**
	 *
	 */

    private $_user;

	public function setUp()
	{

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
		$this->project = SugarTestProjectUtilities::createProject();
		$projectId = $this->project->id;


		$projectTasksData = array (
			'taskOne' => array (
				'project_id' => $projectId,
				'parent_task_id' => '',
				'project_task_id' => '1',
				'percent_complete' => '0',
				'name' => 'Task 1',
                'duration_unit' => 'Days',
                'duration' => '1',
			),
            'taskTwo' => array (
				'project_id' => $projectId,
				'parent_task_id' => '1',
				'project_task_id' => '2',
				'percent_complete' => '0',
				'name' => 'Task 2',
                'duration_unit' => 'Days',
                'duration' => '1',
			),
			'taskThree' => array (
				'project_id' => $projectId,
				'parent_task_id' => '1',
				'project_task_id' => '3',
				'percent_complete' => '0',
				'name' => 'Task 3',
                'duration_unit' => 'Days',
                'duration' => '1',
			),
			'taskFour' => array (
				'project_id' => $projectId,
				'parent_task_id' => '3',
				'project_task_id' => '4',
				'percent_complete' => '0',
				'name' => 'Task 4',
                'duration_unit' => 'Days',
                'duration' => '1',
			),
            'taskFive' => array (
				'project_id' => $projectId,
				'parent_task_id' => '3',
				'project_task_id' => '5',
				'percent_complete' => '0',
				'name' => 'Task 5',
                'duration_unit' => 'Days',
                'duration' => '1',
			),
		);

		foreach ($projectTasksData as $key => $value)
		{
			$this->projectTasks[$key] = SugarTestProjectTaskUtilities::createProjectTask($value);
		}
	}

	public function tearDown()
	{
		SugarTestProjectUtilities::removeAllCreatedProjects();
		SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
		unset($this->project);
		unset($this->projectTasks);
        unset($this->_user);
	}

	public function testResourceName()
	{
		$processingTask = $this->projectTasks['taskFive'];
		$processingTask->percent_complete = '65';
		$processingTask->save();

        $taskOne = new ProjectTask();
		$taskOne->retrieve($this->projectTasks['taskOne']->id);

		$this->assertEquals('22', $taskOne->percent_complete);

        $taskThree = new ProjectTask();
		$taskThree->retrieve($this->projectTasks['taskThree']->id);

		$this->assertEquals('33', $taskThree->percent_complete);
	}
}