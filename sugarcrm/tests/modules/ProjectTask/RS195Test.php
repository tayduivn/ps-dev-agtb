<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/ProjectTask/ProjectTask.php';

/**
 *  RS195: Prepare ProjectTask Module.
 */
class RS195Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testTaskProject()
    {
        $project = SugarTestProjectUtilities::createProject();
        $bean = SugarTestProjectTaskUtilities::createProjectTask(
            array(
                'project_id' => $project->id,
                'parent_task_id' => 0,
                'project_task_id' => 1,
                'percent_complete' => '30',
                'name' => 'RS195Task_1',
            )
        );
        $res = $bean->_get_project_name($project->id);
        $this->assertEquals($project->name, $res);
        $res = $bean->_get_parent_name($project->id);
        $this->assertEquals($project->name, $res);
        $res = $bean->getResourceName();
        $this->assertEmpty($res);
        $res = SugarTestReflection::callProtectedMethod($bean, 'getNumberOfTasksInProject', array($project->id));
        $this->assertEquals(1, $res);
    }

    public function testTaskParent()
    {
        $project = SugarTestProjectUtilities::createProject();
        $bean = SugarTestProjectTaskUtilities::createProjectTask(
            array(
                'project_id' => $project->id,
                'parent_task_id' => 0,
                'project_task_id' => 1,
                'percent_complete' => '30',
                'name' => 'RS195Task_1',
            )
        );
        $bean2 = SugarTestProjectTaskUtilities::createProjectTask(
            array(
                'project_id' => $project->id,
                'parent_task_id' => 1,
                'project_task_id' => 2,
                'percent_complete' => '30',
                'name' => 'RS195Task_2',
            )
        );
        $res = $bean2->_get_depends_on_name($bean->id);
        $this->assertEquals('RS195Task_1', $res);
        $res = $bean2->getProjectTaskParent();
        $this->assertEquals($res->id, $bean->id);
        $res = $bean->getAllSubProjectTasks();
        $this->assertCount(1, $res);
        $res = array_shift($res);
        $this->assertEquals($bean2->id, $res->id);
    }
}
