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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Bug #55923
 * Workflow doesn't trigger when date_field changes
 *
 * @author vromanenko@sugarcrm.com
 * @ticket 55923
 */
class Bug55923Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;
    protected $workFlowActionShellId;
    protected $workFlowActionId;
    private $hasWorkflowFile = false;

    /**
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * @var DBManager
     */
    protected $db;

    protected function setUp()
    {
        $this->db = $GLOBALS['db'];
        $_REQUEST['base_module'] = 'Opportunities';
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig(
            array(
                'forecast_by' => 'Opportunities',
                'sales_stage_won' => 'won'
            )
        );

        $this->hasWorkflowFile = SugarAutoLoader::fileExists('custom/modules/Opportunities/workflow/workflow.php');

        $wf = new WorkFlow();
        $wf->name = 'WF1';
        $wf->base_module = 'Opportunities';
        $wf->status = 1;
        $wf->type = 'Normal';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'All';
        $this->workFlowId = $wf->save();
        $wf->check_logic_hook_file();

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'date_closed';
        $wft->type = 'compare_change';
        $wft->frame_type = 'Primary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->save();
        $wfo = $wft->glue_triggers('', '');
        $this->workFlowTriggerShellId = $wft->save();

        $wfa = new WorkFlowActionShell();
        $wfa->action_type = 'update';
        $wfa->parent_id = $wf->id;
        $wfa->rel_module = '';
        $wfa->action_module = '';
        $this->workFlowActionShellId = $wfa->save();
        $actionObject = new WorkFlowAction();
        $actionObject->adv_type = '';
        $actionObject->ext1 = '';
        $actionObject->ext2 = '';
        $actionObject->ext3 = '';
        $actionObject->field = 'description';
        $actionObject->value = 'TRIGGERED';
        $actionObject->set_type = 'Basic';
        $actionObject->adv_type = '';
        $actionObject->parent_id = $wfa->id;
        $this->workFlowActionId = $actionObject->save();

        $wf = $wfa->get_workflow_object();
        $wfa->check_for_invitee_bridge($wf);
        $wf->write_workflow();

        $this->workFlowId = $wf->id;
        LogicHook::refreshHooks();
    }

    protected function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestForecastUtilities::tearDownForecastConfig();

        rmdir_recursive('custom/modules/Opportunities/workflow');
        $this->db->query("delete from workflow where id = '$this->workFlowId'");
        $this->db->query("delete from workflow_triggershells where id = '$this->workFlowTriggerShellId'");
        $this->db->query("delete from workflow_actionshells where id = '$this->workFlowActionShellId'");
        $this->db->query("delete from workflow_actions where id = '$this->workFlowActionId'");
        LogicHook::refreshHooks();

        $_REQUEST = array();
        SugarTestHelper::tearDown();

        if(!$this->hasWorkflowFile) {
            SugarAutoLoader::delFromMap('custom/modules/Opportunities/workflow/workflow.php');
        }
    }

    /**
     * Ensure that workflow triggers actions when date field changes on newly created record.
     *
     * @group 55923
     */
    public function testWorkFlowTriggersWhenSavingNewOpportunityWithDateClosedChanged()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->assertEquals('TRIGGERED', $opportunity->description);
    }


}
