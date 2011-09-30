<?php

/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * ****************************************************************************** */



require_once "modules/ProjectTask/ProjectTask.php";
require_once "modules/Project/Project.php";

/**
 * Created: Sep 21, 2011
 */
class Bug46350Test extends Sugar_PHPUnit_Framework_TestCase
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
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user'] = $this->_user;
		$this->project = SugarTestProjectUtilities::createProject();
		$projectId = $this->project->id;
		$projectTasksData = array (
			'parentTask' => array (
				'project_id' => $projectId,
				'parent_task_id' => '',
				'project_task_id' => 1,
				'percent_complete' => $this->countAverage(array ($this->oldPercentValue, $this->defaultStaticSecondPercent)),
				'name' => 'Task 1',
			),
			'firstChildTask' => array (
				'project_id' => $projectId,
				'parent_task_id' => 1,
				'project_task_id' => 2,
				'percent_complete' => $this->oldPercentValue,
				'name' => 'Task 2',
			),
			'secondChildTask' => array (
				'project_id' => $projectId,
				'parent_task_id' => 1,
				'project_task_id' => 3,
				'percent_complete' => $this->defaultStaticSecondPercent,
				'name' => 'Task 3',
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
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($this->project);
		unset($this->projectTasks);
        unset($this->_user);
		unset($GLOBALS['current_user']);
	}

	public function countAverage($values)
	{
		$count = 0;
		foreach ($values as $key => $value)
		{
			$count += $value;
		}
		return (round($count / count($values)));
	}

	public function testResourceName()
	{
		$processingTask = $this->projectTasks['firstChildTask'];
		$processingTask->percent_complete = $this->newPercentValue;
		$processingTask->save();

		/**
		 * New method testing
		 */
		$processingTask->updateParentProjectTaskPercentage();

		$testparentTask = new ProjectTask();
		$testparentTask->retrieve($this->projectTasks['parentTask']->id);

		$average = $this->countAverage(array ($this->newPercentValue, $this->projectTasks['secondChildTask']->percent_complete));
		$this->assertEquals($average, $testparentTask->percent_complete);
	}
}