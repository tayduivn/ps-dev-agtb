<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: CreateStep2.php,v 1.1 2006/07/07 22:00:05 jgreen Exp $
 * Description:
 ********************************************************************************/

global $theme;
require_once('modules/WorkFlow/WorkFlow.php');
require_once('modules/WorkFlowActions/WorkFlowAction.php');
require_once('modules/WorkFlowActionShells/WorkFlowActionShell.php');
require_once('themes/'.$theme.'/layout_utils.php');
require_once('include/workflow/field_utils.php');
require_once('log4php/LoggerManager.php');
require_once('XTemplate/xtpl.php');
require_once('include/utils.php');
require_once('include/ListView/ListView.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;

global $urlPrefix;
global $currentModule;


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
if (!isset($_REQUEST['html'])) {
	$form =new XTemplate ('custom/workflow/plugins/weighted_route/CreateStep2.html');
	$GLOBALS['log']->debug("using file custom/workflow/plugins/weighted_route/CreateStep2.html");
}


$form->assign("MOD", $mod_strings);
$form->assign("APP", $app_strings);

$focus = new WorkFlowActionShell();  
//Add When Expressions Object is availabe
//$exp_object = new Expressions();

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);

}
        
        
if(isset($_REQUEST['action_type']) && $_REQUEST['action_type']!=""){     
	$focus->action_type = $_REQUEST['action_type'];
}
	$focus->action_module = $seed_object->base_module;

//set the parent id
$focus->parent_id = $_REQUEST['workflow_id'];
	
	$form->assign("ID", $focus->id); 
    $form->assign("WORKFLOW_ID", $_REQUEST['workflow_id']);
    $form->assign("ACTION_MODULE", $focus->action_module);
    $form->assign("ACTION_TYPE", $focus->action_type);
    $form->assign("TARGET_FIELD", $focus->rel_module);
    
    
	$form->assign("THEME", $theme);
	$form->assign("IMAGE_PATH", $image_path);
	$form->assign("MODULE_NAME", $currentModule);
	//$form->assign("FORM", $_REQUEST['form']);
	$form->assign("GRIDLINE", $gridline);

	insert_popup_header($theme);


	$form->parse("embeded");
	$form->out("embeded");    
    
	
	
	$focus->parameters = unserialize(base64_decode($focus->parameters));
	
	
	if(!empty($focus->parameters['user_1'])){
		$assigned_user_1 = $focus->parameters['user_1'];
	} else {
		$assigned_user_1 = '';
	}		
	if(!empty($focus->parameters['user_2'])){
		$assigned_user_2 = $focus->parameters['user_2'];
	} else {
		$assigned_user_2 = '';
	}

	if(!empty($focus->parameters['user_1_weight'])){
		$user_1_weight = $focus->parameters['user_1_weight'];
	} else {
		$user_1_weight = '50';
	}		
	
	if(!empty($focus->parameters['user_2_weight'])){
		$user_2_weight = $focus->parameters['user_2_weight'];
	} else {
		$user_2_weight = '50';
	}		
	
	$form->assign("USER_1_OPTIONS", get_select_options_with_id(get_user_array(false, "Active", ''), $assigned_user_1));
	$form->assign("USER_2_OPTIONS", get_select_options_with_id(get_user_array(false, "Active", ''), $assigned_user_2));
	
	$form->assign("USER_1_WEIGHT", $user_1_weight);
	$form->assign("USER_2_WEIGHT", $user_2_weight);
	
	
	
	
	
    	//rsmith
    	require_once('include/ListView/ProcessView.php');
		$ProcessView = & new ProcessView($seed_object, $focus);
	/*
		$results = $ProcessView->get_action_shell_display_text($focus);
		$result = $results["RESULT_ARRAY"];
		$field_count = 0;
		foreach($result as $value)
		{
			foreach($value as $aKey=>$aVal)
			{
				$form->assign($aKey, $aVal);
			}
			$form->parse("main.lang_field");
			++ $field_count;
		}
    	//rsmith
    	
    	$form->assign("TARGET_MODULE", $results["TEMP_MODULE_DIR"]);
    	$form->assign("TOTAL_FIELD_COUNT", $field_count);    	
    */


//SET Previous Display Text

	global $process_dictionary;
	require_once('custom/workflow/plugins/weighted_route/action_meta_array.php');
	
	$prev_display_text = $ProcessView->get_prev_text("ActionsCreateStep1", $focus->action_type);

	$form->assign("PREV_DISPLAY_TEXT", $prev_display_text);
	
	
	if($focus->id==""){
	$target_field_text = 'field';	
	} else {
		
	$target_field_text = $focus->rel_module;	
	}		
	
	$form->assign("TARGET_FIELD_TEXT", $target_field_text);
	
	
	$adv_related_array = $ProcessView->get_adv_related("ActionsCreateStep1", $focus->action_type, "action");

		$form->assign("ADVANCED_SEARCH_PNG", get_image($image_path.'advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
		$form->assign("BASIC_SEARCH_PNG", get_image($image_path.'basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
		
	if($adv_related_array!=""){
		$form->assign("ADV_RELATED_BLOCK", $adv_related_array['block']);
		if($focus->rel_module_type=="all" || $focus->rel_module_type==""){
			$form->assign("REL_SET_TYPE", "Basic");	
		} else {	
			$form->assign("REL_SET_TYPE", "Advanced");	
		}
		
		$form->assign("SET_DISABLED", "No");	
	} else {
		$form->assign("REL_SET_TYPE", "Basic");	
		$form->assign("SET_DISABLED", "Yes");
	}	
			
$form->parse("main");
$form->out("main");

?>

<?php echo get_form_footer(); ?>
<?php insert_popup_footer(); ?>
