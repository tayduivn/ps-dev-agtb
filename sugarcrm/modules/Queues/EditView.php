<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
$_REQUEST['edit']='true';


require_once('modules/Queues/Queue.php');



require_once('include/language/en_us.lang.php');
require_once("include/templates/TemplateGroupChooser.php");


// GLOBALS
global $mod_strings;
global $app_strings;
global $app_list_strings;

$focus = new Queue();
$javascript = new Javascript();
/* Start standard EditView setup logic */
$mod_strings = return_module_language($current_language, 'Queues');

if(isset($_REQUEST['record'])) {
	$GLOBALS['log']->debug("In Queues edit view, about to retrieve record: ".$_REQUEST['record']);
	$result = $focus->retrieve($_REQUEST['record']);
    if($result == null)
    {
    	sugar_die($app_strings['ERROR_NO_RECORD']);
    }
}
if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$GLOBALS['log']->debug("isDuplicate found - duplicating record of id: ".$focus->id);
	$focus->id = "";
}

$GLOBALS['log']->info("Queues Edit View");
/* End standard EditView setup logic */

/* Start custom setup logic */
$focus->getQueues();

$queueTemp = new Queue();

//_pp('children');
//_pp($focus->child_ids);
//_pp('parents');
//_pp($focus->parent_ids);
//_pp('name: '. $focus->name);
//_pp('id: '.$focus->id);


$alls = $queueTemp->get_full_list();
foreach($alls as $key => $obj) {
	$allQueues[$obj->id] = $obj->name;
}

$chooserParent = new TemplateGroupChooser();
$chooserChild = new TemplateGroupChooser();
$chooserParent->display_third_tabs = false;
//$chooserParent->args['disable'] = false;
$chooserChild->display_third_tabs = false;

if(is_admin($current_user)) {
	$chooserParent->display_hide_tabs = true;
	$chooserParent->args['third_name'] = 'remove_tabs';
	$chooserParent->args['third_label'] =  $mod_strings['LBL_REMOVED_TABS'];
}
// PARENT QUEUES
$chooserParent->args['id'] = 'parent_edit_tabs';
$chooserParent->args['values_array'][0] = $allQueues;
$chooserParent->args['values_array'][1] = $allQueues;
foreach ($chooserParent->args['values_array'][0] as $key => $value) {
//	_pp($key.$value[$key]);
	if(in_array($key, $focus->parent_ids)) {
		$chooserParent->args['values_array'][1][$key] = $value;
		unset($chooserParent->args['values_array'][0][$key]);
	} else {
		$chooserParent->args['values_array'][0][$key] = $value;
		unset($chooserParent->args['values_array'][1][$key]);
	}
}

$chooserParent->args['left_name'] = 'parent_available_queues';
$chooserParent->args['right_name'] = 'parent_queues';
$chooserParent->args['left_label'] =  $mod_strings['LBL_AVAILABLE_QUEUES'];
$chooserParent->args['right_label'] =  $mod_strings['LBL_CONNECTED_QUEUES'];
$chooserParent->args['title'] =  $mod_strings['LBL_PARENT_QUEUES'];

// CHILD QUEUES
$chooserChild->args['id'] = 'child_edit_tabs';
$chooserChild->args['values_array'][0] = $allQueues;
$chooserChild->args['values_array'][1] = $allQueues;
foreach ($chooserChild->args['values_array'][0] as $key => $value) {
	if(in_array($key, $focus->child_ids)) {
		$chooserChild->args['values_array'][1][$key] = $value;
		unset($chooserChild->args['values_array'][0][$key]);
	} else {
		$chooserChild->args['values_array'][0][$key] = $value;
		unset($chooserChild->args['values_array'][1][$key]);
	}
}

$chooserChild->args['left_name'] = 'child_available_queues';
$chooserChild->args['right_name'] = 'child_queues';
$chooserChild->args['left_label'] =  $mod_strings['LBL_AVAILABLE_QUEUES'];
$chooserChild->args['right_label'] =  $mod_strings['LBL_CONNECTED_QUEUES'];
$chooserChild->args['title'] =  $mod_strings['LBL_CHILD_QUEUES'];




$queueType = '';
foreach($app_list_strings['queue_type_dom'] as $k => $type) {
	if($focus->queue_type == $type) { $selected = " SELECTED"; }
	else { $selected = ""; }

	$queueType .= "<option value='".$k."'.".$selected.">".$type."</option>";
}
$status = '';
foreach($app_list_strings['user_status_dom'] as $k => $stat) {
	if($focus->status == $stat) { $selected = " SELECTED"; }
	else { $selected = ""; }

	$status .= "<option value='".$stat."' ".$selected.">".$stat."</option>";
}


// WORKFLOWS select options
$workflows = '';
foreach($focus->getWorkflows() as $k => $value) {
	if($k == $focus->workflows) {
		$selected = ' SELECTED';
	} else {
		$selected = '';
	}
	$workflows .= '<option value="'.$k.'" '.$selected.'>'.$value['name'].'</option>';  
} 

// javascript
$javascript->setSugarBean($focus);
$javascript->setFormName('EditView');
$javascript->addFieldGeneric('name', 'alpha', 'Name', true, $prefix='');

/* End custom setup logic */


// TEMPLATE ASSIGNMENTS
$xtpl = new XTemplate('modules/Queues/EditView.html');
// standard assigns
$xtpl->assign('MOD', $mod_strings);
$xtpl->assign('APP', $app_strings);
$xtpl->assign("THEME", $theme);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("JAVASCRIPT", get_set_focus_js().get_chooser_js($chooserParent->args['left_name'],$chooserParent->args['right_name'],$chooserChild->args['left_name'],$chooserChild->args['right_name']).$javascript->getScript());
$xtpl->assign('RETURN_MODULE', 'Queues');
$xtpl->assign('RETURN_ID', $focus->id);
$xtpl->assign('RETURN_ACTION', 'DetailView');
// module specific
$xtpl->assign('MODULE_TITLE', get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->name, true));
$xtpl->assign('ID', $focus->id);
$xtpl->assign('NAME', $focus->name);
$xtpl->assign('STATUS', $status);
$xtpl->assign('OWNER_ID', $focus->owner_id);
$xtpl->assign("TAB_CHOOSER_PARENT", $chooserParent->display());
$xtpl->assign("TAB_CHOOSER_CHILD", $chooserChild->display());
$xtpl->assign("CHOOSER_SCRIPT","set_chooser();");
$xtpl->assign("LEFT1",$chooserParent->args['left_name']);
$xtpl->assign("RIGHT1", $chooserParent->args['right_name']);
$xtpl->assign("LEFT2", $chooserChild->args['left_name']);
$xtpl->assign("RIGHT2", $chooserChild->args['right_name']);
$xtpl->assign('QUEUE_TYPE', $queueType);
$xtpl->assign('WORKFLOWS', $workflows);

// PARSE AND PRINT
$xtpl->parse("main");
$xtpl->out("main");




?>
