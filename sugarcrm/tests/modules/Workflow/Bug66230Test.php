<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License. Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party. Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited. You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License. Please refer to the License for the specific language
 * governing these rights and limitations under the License. Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Class Bug66230Test
 *
 * Test that nothing breaks time elapsed workflows with only one trigger
 *
 */
class Bug66230Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        // Create a workflow firing on New Quotes
        $wf = new WorkFlow();
        $wf->name = 'WF1 66230';
        $wf->base_module = 'Quotes';
        $wf->status = 1;
        $wf->type = 'Time';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'New';
        $this->workFlowId = $wf->save();
        $wf->check_logic_hook_file();

        // Condition, if description doesn't change for 0 hours
        $wft = new WorkFlowTriggerShell();
        $wft->field = 'description';
        $wft->type = 'compare_any_time';
        $wft->frame_type = 'Primary';
        $wft->parameters = 0;
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->save();
        $wfo = $wft->glue_triggers('', '');
        $this->workFlowTriggerShellId = $wft->save();

        $wf->write_workflow();

        $this->workFlowId = $wf->id;

        // Refresh Hooks
        LogicHook::refreshHooks();

        $_SESSION['workflow_cron'] = array();
    }

    public function tearDown()
    {
        // Remove workflow defs
        rmdir_recursive('custom/modules/Quotes/workflow');
        rmdir_recursive('custom/modules/Quotes/logic_hooks.php');
        // Delete all the stuff created for the workflow
        $GLOBALS['db']->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        $GLOBALS['db']->query("DELETE FROM workflow_triggershells WHERE id = '$this->workFlowTriggerShellId'");
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        // Refresh hooks
        LogicHook::refreshHooks();

        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestHelper::tearDown();
    }

    public function testSingleAfterTimeElapsesTrigger()
    {
        // Create a Quote
        $quote = SugarTestQuoteUtilities::createQuote();

        $result = $quote->db->query(
            "SELECT count(*) as count
                FROM workflow_schedules
                WHERE target_module = 'Quotes'
                AND bean_id = '{$quote->id}'"
        );
        $row = $quote->db->fetchByAssoc($result);

        // Check if the workflow fired by looking into workflow_schedules table
        $this->assertEquals(1, $row['count'], 'workflow_schedule not created');
    }
}
