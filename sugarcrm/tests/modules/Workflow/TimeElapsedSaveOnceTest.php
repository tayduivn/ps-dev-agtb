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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once('modules/WorkFlow/WorkFlowSchedule.php');

/**
 * Class TimeElapsedWorkflowTest
 */
class TimeElapsedSaveOnceTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $beanId = 'TimeElapsedSaveOnceTest_BeanId';

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE bean_id = '{$this->beanId}'");
        SugarTestHelper::tearDown();
    }

    public function testUniqueSaveOnce()
    {
        // Initialize the mock
        $bean = $this->getMock('Account', array('save'));
        // We expect save() to run on this bean only once
        $bean->expects($this->once())->method('save');
        $bean->id = $this->beanId;
        $bean->fetched_row = array(
            'deleted' => 0
        );
        // Need to register the mock, to be reused in process_scheduled
        BeanFactory::registerBean($bean);

        // Create 2 workflow_schedules for different workflows
        $temp = new WorkFlowSchedule();
        $temp->bean_id = $bean->id;
        $temp->workflow_id = 'TimeElapsedSaveOnceTest_1';
        $temp->target_module = $bean->module_dir;
        $temp->date_expired = '2010-01-01 00:00:00';
        $temp->save();

        $temp = new WorkFlowSchedule();
        $temp->bean_id = $bean->id;
        $temp->workflow_id = 'TimeElapsedSaveOnceTest_2';
        $temp->target_module = $bean->module_dir;
        $temp->date_expired = '2011-01-01 00:00:00';
        $temp->save();

        // Process schedules
        $workflowSchedule = new WorkFlowSchedule();
        $workflowSchedule->process_scheduled();
    }
}
