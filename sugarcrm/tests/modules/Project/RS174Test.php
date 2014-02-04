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

require_once 'modules/Project/Project.php';

/**
 *  RS174: Prepare Project Module.
 */
class RS174Test extends Sugar_PHPUnit_Framework_TestCase
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

    public function testEfforts()
    {
        $bean = SugarTestProjectUtilities::createProject();
        $res = $bean->_get_total_estimated_effort($bean->id);
        $this->assertEmpty($res);
        $res = $bean->_get_total_actual_effort($bean->id);
        $this->assertEmpty($res);
    }

    public function testTasks()
    {
        $bean = SugarTestProjectUtilities::createProject();
        SugarTestProjectTaskUtilities::createProjectTask(
            array(
                'project_id' => $bean->id,
                'parent_task_id' => 0,
                'project_task_id' => create_guid(),
                'percent_complete' => '30',
                'name' => 'RS174Task',
            )
        );
        $res = $bean->getAllProjectTasks();
        $this->assertCount(1, $res);
    }

    public function testHolidays()
    {
        $bean = SugarTestProjectUtilities::createProject();
        $query = $bean->getProjectHolidays();
        $resource = DBManagerFactory::getInstance()->query($query);
        $result = DBManagerFactory::getInstance()->fetchByAssoc($resource);
        $this->assertEmpty($result);
    }
}
