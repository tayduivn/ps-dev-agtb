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
 * @author avucinic@sugarcrm.com
 * @ticket 64815
 */
class Bug64815Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $workFlowId;

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
        $wf->name = 'WF 64815';
        $wf->base_module = 'Calls';
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
        $wft->parameters = 0;
        $wfo = $wft->glue_triggers('', '');
        $wft->save();

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'outlook_id';
        $wft->type = 'compare_specific';
        $wft->frame_type = 'Secondary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->eval = '(  ( !($focus->fetched_row["outlook_id"] ==  "64815" )) && (isset($focus->outlook_id) && $focus->outlook_id ==  "64815") )  ||  (  (isset($focus->outlook_id) && $focus->outlook_id ==  "64815") && !empty($_SESSION["workflow_cron"]) && $_SESSION["workflow_cron"]=="Yes" ) ';
        $wft->save();

        $wf->write_workflow();
        LogicHook::refreshHooks();
    }

    protected function tearDown()
    {
        TimeDate::getInstance()->clearCache();

        rmdir_recursive('custom/modules/Calls/workflow');

        $this->db->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        LogicHook::refreshHooks();

        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestHelper::tearDown();

        $_REQUEST = array();
    }

    /**
     * Ensure that deleting primary workflow trigger deletes all secondary triggers
     * and the schedules related to the workflow
     */
    public function testTimeElapsedWorkFlowScheduleCreation()
    {
        // Override TimeDate so we can test the update time
        $timeDate = TimeDate::getInstance();
        $time = $timeDate->fromString('2013-05-05 10:10:10');
        $timeDate->setNow($time);


        // Create a Call without setting the outlook_id and check that no schedule was created
        $bean = SugarTestCallUtilities::createCall();

        $result = $this->db->query(
            "SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            '0',
            $row['count'],
            'Workflow schedule should not be created.'
        );


        // Now set the needed value for the secondary trigger
        $bean = $bean->retrieve($bean->id);
        $bean->outlook_id = '64815';
        $bean->save();

        // Check that schedule was created
        $result = $this->db->query(
            "SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            '1',
            $row['count'],
            'Workflow schedule should be created.'
        );


        // Change description and check that workflow schedule got updated
        $timeDate->setNow($timeDate->getNow()->modify("+1 second"));
        $bean->description = "New Description";
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $timeDate->asDb($timeDate->getNow()),
            $row['date_expired'],
            'Workflow schedule should get updated for primary trigger'
        );


        // Change an attribute that has no triggers attached to it, and check that the workflow is not updated
        $timeDate->setNow($timeDate->getNow()->modify("+1 second"));
        $dateExpiredOld = $row['date_expired'];
        $bean = $bean->retrieve($bean->id);
        $bean->name = "New Name";
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired, count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $dateExpiredOld,
            $row['date_expired'],
            'Workflow schedule should not get updated'
        );


        // Now change the outlook_id field to something other than what the trigger requires
        // and see that it doesn't update the schedule
        $timeDate->setNow($timeDate->getNow()->modify("+1 second"));
        $bean = $bean->retrieve($bean->id);
        $bean->outlook_id = "New Location";
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $dateExpiredOld,
            $row['date_expired'],
            'Workflow schedule should not get updated when secondary trigger not equal to the expected value'
        );
    }
}
