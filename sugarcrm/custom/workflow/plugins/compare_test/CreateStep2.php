<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: CreateStep2.php,v 1.1 2006/07/07 22:00:05 jgreen Exp $
 * Description:
 ********************************************************************************/

global $theme;
require_once('modules/WorkFlow/WorkFlow.php');
require_once('modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php');
require_once('include/workflow/workflow_utils.php');
require_once('include/workflow/field_utils.php');
require_once('modules/Expressions/Expression.php');
require_once('themes/'.$theme.'/layout_utils.php');
require_once('log4php/LoggerManager.php');
require_once('XTemplate/xtpl.php');
require_once('include/utils.php');
require_once('include/ListView/ListView.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;

global $urlPrefix;
global $currentModule;


$workflow_object = new WorkFlow();
if(isset($_REQUEST['workflow_id']) && isset($_REQUEST['workflow_id'])) {
    $workflow_object->retrieve($_REQUEST['workflow_id']);
} else {
	sugar_die("You shouldn't be here");
}

$focus = new WorkFlowTriggerShell();

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);

}

if(!empty($_REQUEST['field']) && $_REQUEST['field']!="") {
   $focus->field = $_REQUEST['field'];
}

if(!empty($_REQUEST['type']) && $_REQUEST['type']!="") {
   $focus->type = $_REQUEST['type'];
}




$image_path = 'themes/'.$theme.'/images/';


////////////////////////////////////////////////////////
// Start the output
////////////////////////////////////////////////////////
	$form =new XTemplate ('custom/workflow/plugins/compare_test/CreateStep2.html');
	$GLOBALS['log']->debug('custom/workflow/plugins/compare_test/CreateStep2.html');

	
        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return() {\n";
        $the_javascript .= "    window.opener.document.EditView.submit();";
        $the_javascript .= "}\n";
        $the_javascript .= "</script>\n";	
	
	$form->assign("MOD", $mod_strings);
	$form->assign("APP", $app_strings);
	$form->assign("THEME", $theme);
	$form->assign("IMAGE_PATH", $image_path);
	$form->assign("MODULE_NAME", $currentModule);
	$form->assign("GRIDLINE", $gridline);
	$form->assign("SET_RETURN_JS", $the_javascript);	

	$form->assign("BASE_MODULE", $workflow_object->base_module);
	$form->assign("WORKFLOW_ID", $workflow_object->id);
	$form->assign("ID", $focus->id);
	$form->assign("FIELD", $focus->field);
	$form->assign("PARENT_ID", $workflow_object->id);
	$form->assign("TRIGGER_TYPE", $workflow_object->type);
	$form->assign("TYPE", $focus->type);
	

	
	//Check multi_trigger filter conditions
	if(!empty($_REQUEST['frame_type']) && $_REQUEST['frame_type']=="Secondary"){
		$form->assign("FRAME_TYPE", $_REQUEST['frame_type']);
	} else {
		$form->assign("FRAME_TYPE", "Primary");
	}		

 	
insert_popup_header($theme);

$form->parse("embeded");
$form->out("embeded");
	

////////Middle Items/////////////////////////////


	global $process_dictionary;
	require_once('custom/workflow/plugins/compare_test/trigger_meta_array.php');
	
	
	//deal with unserializing and preparing the list array if it exists
	///!!!

	
	$temp_module = get_module_info($workflow_object->base_module);
	$display_field_name = $temp_module->field_defs[$focus->field]['vname'];
	$current_module_strings = return_module_language($current_language, $workflow_object->base_module);
	$display_field_name = "<i><b>\" ".get_label($display_field_name, $current_module_strings)." \"</i></b>";	
	$form->assign("SPECIFIC_FIELD", $display_field_name);
	
//SET Previous Display Text
	require_once('include/ListView/ProcessView.php');
	$ProcessView = new ProcessView($workflow_object, $focus);
	$prev_display_text = $ProcessView->get_prev_text("TriggersCreateStep1", $focus->type);
	
	$form->assign("PREV_DISPLAY_TEXT", $prev_display_text);
	
	
	//set the parameters to the list array

		$form->assign("LIST_ARRAY", $focus->parameters);
	
/////////////////End Items 	//////////////////////

//close window and refresh parent if needed

if(!empty($_REQUEST['special_action']) && $_REQUEST['special_action'] == "refresh"){
	
	$special_javascript = "window.opener.document.DetailView.action.value = 'DetailView'; \n";
	$special_javascript .= "window.opener.document.DetailView.submit(); \n";
	$special_javascript .= "window.close();";
	$form->assign("SPECIAL_JAVASCRIPT", $special_javascript);
	
}	

$form->parse("main");
$form->out("main");

?>

<?php echo get_form_footer(); ?>
<?php insert_popup_footer(); ?>
