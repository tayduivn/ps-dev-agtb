<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id: EditView.php 53116 2009-12-10 01:24:37Z mitani $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('modules/Teams/Forms.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $current_user;

$GLOBALS['log']->info("Team edit view");

if (!$GLOBALS['current_user']->isAdminForModule('Users')) sugar_die("Unauthorized access to administration.");

$focus = new Team();

if (isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}


echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_NAME'],$focus->get_summary_text()), true);

$xtpl = new XTemplate("modules/Teams/EditView.html");
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

if (isset($_REQUEST['error_string'])) $xtpl->assign("ERROR_STRING", "<span class='error'>Error: ".$_REQUEST['error_string']."</span>");
if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
// handle Create $module then Cancel
if (empty($_REQUEST['return_id'])) {
	$xtpl->assign("RETURN_ACTION", 'index');
}if (isset($_REQUEST['isDuplicate'])) $xtpl->assign("IS_DUPLICATE", $_REQUEST['isDuplicate']);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("NAME", Team::getDisplayName($focus->name, $focus->name_2));
$xtpl->assign("DESCRIPTION", $focus->description);

$buttons = array(
    <<<EOD
            <input title="{APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="this.form.action.value='Save'; return check_form('EditView');" type="submit" id="btn_save2" value="{$app_strings['LBL_SAVE_BUTTON_LABEL']}" />
EOD
,
    <<<EOD
            <input title="{APP.LBL_CANCEL_BUTTON_TITLE}" class="button" onclick="this.form.action.value='{RETURN_ACTION}'; this.form.module.value='{RETURN_MODULE}'; this.form.record.value='{RETURN_ID}'" type="submit" id="btn_cancel2" value="{$app_strings['LBL_CANCEL_BUTTON_LABEL']}" />
EOD

);
require_once('include/Smarty/plugins/function.sugar_action_menu.php');
$action_button = smarty_function_sugar_action_menu(array(
    'id' => 'Teams_edit_action_buttons',
    'buttons' => $buttons,
    'class' => 'clickMenu fancymenu',
), $xtpl);

$xtpl->assign("ACTION_BUTTON", $action_button);


global $current_user;
if($current_user->isAdminForModule('Users') && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
	$record = '';
	if(!empty($_REQUEST['record'])){
		$record = 	$_REQUEST['record'];
	}
	$xtpl->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$record. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>");
}


$javascript = new javascript();
$javascript->setFormName("EditView");

$javascript->addFieldGeneric("name", "varchar", $mod_strings['LBL_NAME'], TRUE, "");


$xtpl->parse("main");
$xtpl->out("main");



echo $javascript->getScript();
?>