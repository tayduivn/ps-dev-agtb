<?php
require_once('modules/WorkFlowActionShells/WorkFlowActionShell.php');
require_once('include/workflow/field_utils.php');

class Bug35116Test extends Sugar_PHPUnit_Framework_TestCase 
{
    private $temp_module;
    private $field;
    private $field_value;
    private $adv_type;
    private $ext1;
    private $for_action_display;
    
    public function setUp() 
    {

    	$beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $app_strings = array();
        require('include/language/en_us.lang.php');
        $GLOBALS['app_list_strings'] = $app_list_strings;
		
        // Create a workflow action object
		$temp_module = new WorkFlowActionShell();
        $temp_module->name = 'Test Workflow Action';
        $temp_module->assigned_user_id = $GLOBALS['current_user']->id;
        $temp_module->disable_custom_fields = true;
        $this->temp_module = $temp_module;
    	
    }
    
    public function tearDown() 
    {
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_strings']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    /**
     * @group bug35116
     */
    public function testAdvancedDisplayText35116() 
    {
		
        // Setup test
        $this->field = 'assigned_user_id';
        $this->field_value = 'created_by';
        $this->adv_type = 'assigned_user_id'; // 'User assigned to triggered record'
        $this->ext1 = 'Manager'; // User or User's Manager
        $this->for_action_display = true;
		// Set to the field defs to relate type
		$this->temp_module->field_defs[$this->field]['type'] = "relate";
        
        // Text Expected
        $user_type_text = $GLOBALS['app_list_strings']['wflow_adv_user_type_dom'][$this->field_value];
        $relate_type_text = $GLOBALS['app_list_strings']['wflow_relate_type_dom'][$this->ext1];
    	$display_text = get_display_text($this->temp_module, $this->field, $this->field_value, $this->adv_type, $this->ext1, $this->for_action_display);
    	
        $this->assertTrue(strpos($display_text, $user_type_text) !== false, true);
        $this->assertTrue(strpos($display_text, $relate_type_text) !== false, true);
		
    }

}
