<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Selector.php,v 1.13 2006/06/06 17:58:54 majed Exp $
 * Description:
 ********************************************************************************/

global $theme;
require_once('modules/WorkFlow/WorkFlow.php');
require_once('modules/WorkFlowActions/WorkFlowAction.php');
require_once('modules/WorkFlowActionShells/WorkFlowActionShell.php');
require_once('themes/'.$theme.'/layout_utils.php');

require_once('XTemplate/xtpl.php');
require_once('include/utils.php');
require_once('include/ListView/ListView.php');
require_once('include/javascript/javascript.php');
require_once('include/ListView/ProcessView.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;

$mod_strings = return_module_language($current_language, 'WorkFlowActions');

global $urlPrefix;
global $currentModule;
global $current_language;

$seed_object = new WorkFlow();

if(!empty($_REQUEST['workflow_id']) && $_REQUEST['workflow_id']!="") {
    $seed_object->retrieve($_REQUEST['workflow_id']);
} else {
	sugar_die("You shouldn't be here");	
}	

$image_path = 'themes/'.$theme.'/images/';

////////////////////////////////////////////////////////
// Start the output
////////////////////////////////////////////////////////

	$form =new XTemplate ('custom/workflow/plugins/more_action_rel/Selector.html');
	$GLOBALS['log']->debug("using file custom/workflow/plugins/more_action_rel/Selector.html");


$form->assign("MOD", $mod_strings);
$form->assign("APP", $app_strings);

$focus = new WorkFlowActionShell();  


///////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!?////////////////////

//Add When Expressions Object is availabe
//$exp_object = new Expressions();


//////////////////////////////////////////////////////////////////

	$action_object = new WorkFlowAction();

	if(!empty($_REQUEST['action_id']) && $_REQUEST['action_id']!=""){
		$action_object->retrieve($_REQUEST['action_id']); 	
	}
		
	if(!empty($_REQUEST['target_field']) && $_REQUEST['target_field']!=""){
		$action_object->field = $_REQUEST['target_field']; 	
	}

	foreach($action_object->selector_fields as $field){
		if(isset($_REQUEST[$field])){
			//echo "FIELD".$field."REQ:".$_REQUEST[$field]."<BR>";
		$action_object->$field = $_REQUEST[$field];	
		}
	}
	
	if(!empty($_REQUEST['adv_value']) && $_REQUEST['adv_value']!=""){
		$action_object->value = $_REQUEST['adv_value']; 	
	}	
	
	//BEGIN DYNVAR CUSTOMIZATIONS 
		if(!empty($_REQUEST['action_type']) && $_REQUEST['action_type']!=""){
			$opt['action_type'] = $_REQUEST['action_type']; 	
		} else {
			$opt['action_type'] = '';
		}	
		$opt['action_object'] = $action_object;
		$opt['workflow_object'] = $seed_object;
		$opt['field_num'] = $_REQUEST['field_num'];
		$opt['target_module'] = $_REQUEST['target_module'];
		$opt['action_type'] = $_REQUEST['action_type'];
		$opt['target_state'] = 'ok';

	echo "&nbsp;&nbsp;[Extra Relationship Mode]";	

	
//extra relationship mode is only available in new and new_rel mode
if($_REQUEST['action_type']!='new' && $_REQUEST['action_type']!='new_rel'){
	
	$process_na = true;
	
}	else {

	$process_na = false;
	
	
	
	$temp_module = get_module_info($_REQUEST['target_module']);
	$temp_module_strings = return_module_language($current_language, $temp_module->module_dir);
	$all_fields_array = $temp_module->getFieldDefinitions();
	$target_field_array = $all_fields_array[$action_object->field];
			if(!empty($target_field_array['vname'])){
				$target_vname = $target_field_array['vname'];	
			} else {
				$target_vname ="";
			}		
			$label_name = get_label($target_vname, $temp_module_strings);	
			$field_type = get_field_type($target_field_array);
			$field_name = $target_field_array['name'];		
					
			$form->assign('LABEL_NAME', $label_name);	
			
			$form->assign('VARDEF_NAME', $field_name);
			$form->assign('REAL_REL_NAME', $target_field_array['relationship']);
			
			
			
			
		$form->assign('BASE_MODULE', $seed_object->base_module	);
		$form->assign('ACTION_TYPE', $_REQUEST['action_type'] );
			
		$form->assign('FIELD_NUM', "field_".$_REQUEST['field_num']);
		$form->assign('FIELD_NUMBER', $_REQUEST['field_num']);			
		$form->assign('PREFIX', "field_".$_REQUEST['field_num']."__");

		$form->assign('ADV_TYPE', 'custom_p');	
		$form->assign('EXT1', 'more_action_rel');
		
		
		//determine if this relationship is self-referencing a member-of type relationship
		require_once('modules/Relationships/Relationship.php');
		$relationship_object = new Relationship();
		$relationship_object->retrieve_by_name($target_field_array['relationship']);
		

		//echo 'lhs_module'.$relationship_object->lhs_module.'rhs module'.$relationship_object->rhs_module.'<BR>';
		
		/* 	-Extra options stored in value for self referencing relationships or member-of scenarios
			-four scenarios when the trigger mod and the target mod are the same module
			-All are only available on new or new_rel actions only
			-if not member-of or self referencing then just one option, which is scenario 1.
		
		scenario #1: Apply triggered record's relationship id
		new_module->relationship_field_id = trigger_module->relationship_field_id
		
		scenario #2: Apply triggered record's id
		new_module->relationship_field_id = trigger_module->id
		
		scenario #3: Use Scenario #1 if available, else use Scenario #2
		if(!empty(trigger_module->relationship_field_id)){
			
			new_module->relationship_field_id = trigger_module->relationship_field_id
		
		} else {
		
			new_module->relationship_field_id = trigger_module->id
		
		}
		
		scenario #4 Apply new record's id to the triggered record's relationship id regardless of rather the
		triggered record's relationship id is present or not
		
		trigger_module->relationship_field_id = new_module->id

		scenario #5 Apply new record's id to the triggered record's relationship id only if the 
		triggered record's relationship id is not present
		
		if(trigger_module->relationship_field_id is empty){
		trigger_module->relationship_field_id = new_module->id	
		}	
		*/		
		
		
		if($relationship_object->lhs_module != $relationship_object->rhs_module){
		
		$more_rel_array  = array(	'No' => 'No',
									'scenario_1' => 'Yes'
								);		
		
		$form->parse("main.simple");						
								
		} else {
		
		$more_rel_array  = array(	'No' => 'Do not use',
									'scenario_1' => 'Scenario 1',
									'scenario_2' => 'Scenario 2',
									'scenario_3' => 'Scenario 3',
								//	'scenario_4' => 'Scenario 4',
								//	'scenario_5' => 'Scenario 5'
									);
		
		$form->parse("main.extra_options");
												
		//end if else
		}						
								
		if($action_object->ext1 == 'more_action_rel'){
			$form->assign('VALUE', $action_object->value);
			$form->assign('DYNAMIC_VALUE', $action_object->value);
			$form->assign('EXT2', $action_object->ext2);
			
			if(!empty($action_object->value)){
				$more_rel_key = $action_object->value;
			} else {
				$more_rel_key = 'No';
			}
				
			$form->assign("MORE_REL_OPTIONS", get_select_options_with_id($more_rel_array, $more_rel_key));

					
		} else {
			
			
			$form->assign("MORE_REL_OPTIONS", get_select_options_with_id($more_rel_array, 'No'));
	
		}	
		
		$form->assign('SET_TYPE', 'Advanced');
		$form->assign('FIELD_NAME', $field_name);
		$form->assign('FIELD_TYPE', $field_type );
	

//if process_na is true/false
}		
		
$form->assign("THEME", $theme);
$form->assign("IMAGE_PATH", $image_path);
$form->assign("MODULE_NAME", $currentModule);

$form->assign("GRIDLINE", $gridline);

insert_popup_header($theme);

	$form->parse("embeded");
	$form->out("embeded");

	
	
if($process_na==true){		
	
	$form->parse("not_available");
	$form->out("not_available");		
	
} else {	

	$form->parse("main");
	$form->out("main");

}
	
?>

<?php echo get_form_footer(); ?>
<?php insert_popup_footer(); ?>
