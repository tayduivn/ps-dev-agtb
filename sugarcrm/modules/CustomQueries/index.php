<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * $Id: index.php 53846 2010-01-19 20:17:43Z jmertic $
 * Description:  
 ********************************************************************************/

$header_text = '';
global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

if (!is_admin($current_user))
{
   sugar_die($app_strings['LBL_UNAUTH_ADMIN']);
}

if(empty($_REQUEST['action']) || $_REQUEST['action']=="index") $_REQUEST['action'] = "ListView";
$focus = new CustomQuery();
echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_TITLE']), true); 
$is_edit = false;
if(!empty($_REQUEST['record'])) {
    $result = $focus->retrieve($_REQUEST['record']);
	if($result == null)
    {
		$is_edit=true;
    }
}

if(!empty($_REQUEST['repair']) && $_REQUEST['repair']=="repair"){
	
	if(isset($_SESSION['temp_custom_array'])){
		
		foreach($_SESSION['temp_custom_array'] as $field => $value){
				
			$focus->$field = $value;
		}	
	
		unset($_SESSION['temp_custom_array']);
	
	//end if the session temp_custom_array is set
	}	
		
	$is_edit=true;
	
//end if coming here from a repair
}	


if(isset($_REQUEST['edit']) && $_REQUEST['edit']=='true') {
	$is_edit=true;
	//Only allow admins to enter this screen
	if (!is_admin($current_user)) {
		$GLOBALS['log']->error("Non-admin user ($current_user->user_name) attempted to enter the CustomQueries edit screen");
		session_destroy();
		include('modules/Users/Logout.php');
	}
}

$GLOBALS['log']->info("CustomQuery list view");
global $theme;

$button  = "<form border='0' action='index.php' method='post' name='form'>\n";
$button .= "<input type='hidden' name='module' value='CustomQueries'>\n";
$button .= "<input type='hidden' name='action' value='EditView'>\n";
$button .= "<input type='hidden' name='edit' value='true'>\n";
$button .= "<input type='hidden' name='return_module' value='".$currentModule."'>\n";
$button .= "<input type='hidden' name='return_action' value='".$action."'>\n";
$button .= "<input title='".$app_strings['LBL_NEW_BUTTON_TITLE']."' accessyKey='".$app_strings['LBL_NEW_BUTTON_KEY']."' class='button' type='submit' name='New' value='  ".$app_strings['LBL_NEW_BUTTON_LABEL']."  '>\n";
$button .= "</form>\n";

$ListView = new ListView();
$ListView->initNewXTemplate( 'modules/CustomQueries/ListView.html',$mod_strings);
$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" border="0"', null,null,'.gif',$app_strings['LNK_DELETE']));
$ListView->setHeaderTitle($mod_strings['LBL_LIST_FORM_TITLE'] . $header_text);
$ListView->setHeaderText($button);

//Temporary until we upgrade the export feature to multi
$ListView->show_export_button = false;

$ListView->setQuery("", "", "name", "CUSTOMQUERY");
$ListView->show_mass_update = false;
$ListView->processListView($focus, "main", "CUSTOMQUERY");

if ($is_edit) {

echo get_form_header($mod_strings['LBL_CUSTOMQUERY']." ".$focus->name . '&nbsp;' . $header_text, '', false); 


	$GLOBALS['log']->info("CustomQuery edit view");
	$xtpl=new XTemplate ('modules/CustomQueries/EditView.html');
	$xtpl->assign("MOD", $mod_strings);
	$xtpl->assign("APP", $app_strings);
	
	if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
	if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
	if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
	$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
	$xtpl->assign("JAVASCRIPT", get_set_focus_js());
	$xtpl->assign("ID", $focus->id);
	$xtpl->assign('NAME', $focus->name);
	$xtpl->assign('DESCRIPTION', $focus->description);
	$xtpl->assign('CUSTOM_QUERY', $focus->custom_query);
	if ($focus->query_locked == 'on' or $focus->query_locked == '1'){
		$xtpl->assign("QUERY_LOCKED", "checked");
		$xtpl->assign("QUERY_DISABLED", "disabled");
	}
	
if (empty($focus->id) && !isset($_REQUEST['isDuplicate'])) {
	$xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $current_user->default_team));
}
else {
	$xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $focus->team_id));
}
// adding custom fields:

require_once('modules/DynamicFields/templates/Files/EditView.php');


	require_once('include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');
	$teamSetField = new SugarFieldTeamset('Teamset');
	$teamSetField->initClassicView($focus->field_defs);
	$code = $teamSetField->getClassicView($focus->field_defs, 'EditView');
	$xtpl->assign("TEAM_SET_FIELD", $code);	

	$xtpl->parse("main");
	$xtpl->out("main");
	
$javascript = new javascript();
$javascript->setFormName('EditView');
$javascript->setSugarBean($focus);
$javascript->addAllFields('');
echo $javascript->getScript();

}
?>
