<?php
//FILE SUGARCRM flav=pro ONLY
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
 * by SugarCRM are Copyright (C) 2004-2013 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Removal of primary trigger should condition
 * removal of all triggers, and the related schedules
 *
 * @author avucinic
 * @ticket 49723
 */
class Bug49723Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;

    /**
     * @var DBManager
     */
    protected $db;

    protected function setUp()
    {
        $this->db = $GLOBALS['db'];
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $wf = new WorkFlow();
        $wf->name = 'WF1';
        $wf->base_module = 'Contacts';
        $wf->status = 1;
        $wf->type = 'Time';
        $wf->description = '';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'All';
        $wf->save();
        $wf->check_logic_hook_file();
        // Save workflow id
        $this->workFlowId = $wf->id;

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'description';
        $wft->type = 'compare_any_time';
        $wft->frame_type = 'Primary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = null;
        $wft->show_past = 0;
        $wft->parameters = 345600;
        $wfo = $wft->glue_triggers('', '');
        $wft->save();
        // Save primary trigger id
        $this->workFlowTriggerShellId = $wft->id;

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'department';
        $wft->type = 'filter_field';
        $wft->frame_type = 'Secondary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->eval = '(isset($focus->department) && $focus->department == \'www\')';
        $wft->save();

        $wf->write_workflow();
        LogicHook::refreshHooks();
    }

    protected function tearDown()
    {
        // Bad idea, but because of include_once all tests that run after this one need the workflow..
        // rmdir_recursive('custom/modules/Contacts/workflow');

        $this->db->query("DELETE FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        LogicHook::refreshHooks();

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();

        $_REQUEST = array();
    }

    /**
     * Ensure that deleting primary workflow trigger deletes all secondary triggers
     * and the schedules related to the workflow
     *
     * @group 49723
     */
    public function testWorkFlowDeleteTriggers()
    {
        $bean = SugarTestContactUtilities::createContact();
        $bean->description = 'Test';
        $bean->department = 'www';
        $bean->save();

        // Check that triggers were created
        $result = $this->db->query("SELECT count(*) as count FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('2', $row['count'], 'Workflow triggers not created.');

        // Check that schedule was created
        $result = $this->db->query("SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('1', $row['count'], 'Workflow schedule not created.');

        // Delete primary trigger
        $wft = new WorkFlowTriggerShell();
        $wft->retrieve($this->workFlowTriggerShellId);
        $wft->mark_deleted($wft->id);

        // Check that triggers were deleted
        $result = $this->db->query("SELECT count(*) as count FROM workflow_triggershells WHERE parent_id = '$this->workFlowId' AND deleted = 1");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('2', $row['count'], 'Workflow triggers not deleted.');

        // Check that schedule was deleted
        $result = $this->db->query("SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('0', $row['count'], 'Workflow schedule not deleted.');
    }
}
