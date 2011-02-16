<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'include/controller/Controller.php';
require_once 'modules/WorkFlow/WorkFlow.php';
require_once 'modules/WorkFlowActions/WorkFlowAction.php';
require_once 'modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php';


class WorkFlowTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $testWFName = "WFUnitTest";
	protected $testValue = "Workflow triggred!";
	protected $testAccName = "WF Test Account";

	public function setUp()
    {
    	$this->testWFName = "WFUnitTest" . mt_rand(); 
    	$this->testAccName = "WFTestAccount" . mt_rand(); 
    	$this->wf = new WorkFlow();
    	$this->wf->name = $this->testWFName;
    	$this->wf->base_module = "Accounts";
    	$this->wf->type = "Normal";
    	$this->wf->fire_order = "alerts_actions";
    	$this->wf->record_type = "All";
    	$this->wf->save();
	}

	public function tearDown()
	{
	    $this->wf->deleted = true;
	    $this->wf->cascade_delete($this->wf);
	    $sql = "DELETE FROM workflow WHERE id='{$this->wf->id}'";
        $GLOBALS['db']->query($sql);
	}

	public function testCreate_new_list_query()
    {
        $query = $this->wf->create_new_list_query("name", "workflow.name like '{$this->testWFName}%'");
        $result = $this->wf->db->query($query);
        $count = 0;
        while ( $row = $this->wf->db->fetchByAssoc($result) ) $count++;
        $this->assertEquals(1, $count);
    }

    /* Non-functional test.
    public function testWrite_workflow()
    {
        //Build the workflow components
    	echo ("Building workflow trigger...\n");
    	$trigger = new WorkFlowTriggerShell();
        $trigger->type = "trigger_record_change";
        $trigger->frame_type = "Primary";
        $trigger->rel_module_type = "any";
        $trigger->parent_id = $this->wf->id;
        $trigger->save();

        echo ("Building workflow Action Shell...\n");
        $actionShell = new WorkFlowActionShell();
        $actionShell->action_type = "update";
        $actionShell->rel_module_type = "all";
        $actionShell->parent_id = $this->wf->id;
        $actionShell->save();

        echo ("Building workflow Action...\n");
        $action = new WorkFlowAction();
        $action->field = "description";
        $action->value = $this->testValue;
        $action->set_type = "Basic";
        $action->parent_id = $actionShell->id;
        $action->save();

        echo ("Rebuilding workflow...\n");
        //Now build the logic hook and test it
        $this->wf->check_logic_hook_file();
        $this->wf->write_workflow();

        echo ("Creating a new Account...w\n");
        $acc = new Account();
        $acc->name = $this->testAccName;
        $acc->save();

        $this->assertEquals($this->testValue, $acc->description);
    }
    */
}

