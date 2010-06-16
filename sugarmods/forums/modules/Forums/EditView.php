<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id
 * Description:
 ********************************************************************************/
require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Forums/Forum.php');
require_once('modules/ForumTopics/ForumTopic.php');
require_once('modules/Administration/Administration.php');

$admin = new Administration();
$admin->retrieveSettings("notify");

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $current_user;

$focus = new Forum();

if (!isset($_REQUEST['record'])) $_REQUEST['record'] = "";

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}

if(!ACLController::checkAccess('Forums', 'edit', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}

if(!is_admin($current_user))
{
	die('Only administrators can create/edit forums');
}

//if duplicate record request then clear the Primary key(id) value.
if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == '1') {
	$focus->id = "";
}

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->title, true);
echo "\n</p>\n";

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";


$GLOBALS['log']->info("Forum EditView");
$xtpl=new XTemplate ('modules/Forums/EditView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

///////////////////////////////////////
///
/// SETUP POPUPS

$popup_request_data = array(
  'call_back_function' => 'set_return',
  'form_name' => 'EditView',
  'field_to_name_array' => array(
    'id' => 'reports_to_id',
    'name' => 'reports_to_name',
    ),
  );

$json = getJSONobj();
$encoded_forum_popup_request_data = $json->encode($popup_request_data);
$xtpl->assign('encoded_forum_popup_request_data', $encoded_forum_popup_request_data);

/// Users Popup
$popup_request_data = array(
  'call_back_function' => 'set_return',
  'form_name' => 'EditView',
  'field_to_name_array' => array(
    'id' => 'assigned_user_id',
    'user_name' => 'assigned_user_name',
    ),
  );
$xtpl->assign('encoded_users_popup_request_data', $json->encode($popup_request_data));


// BEGIN SUGARCRM flav=pro ONLY 
$popup_request_data = array(
  'call_back_function' => 'set_return',
  'form_name' => 'EditView',
  'field_to_name_array' => array(
    'id' => 'team_id',
    'name' => 'team_name',
    ),
  );
$xtpl->assign('encoded_team_popup_request_data', $json->encode($popup_request_data));
// END SUGARCRM flav=pro ONLY 

//
///////////////////////////////////////


if (isset($_REQUEST['return_module']))
  $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action']))
  $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id']))
  $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("JAVASCRIPT", get_set_focus_js());
$xtpl->assign("IMAGE_PATH", $image_path);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);

require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$sqs_objects = array(
// BEGIN SUGARCRM flav=pro ONLY 
                    'team_name' => $qsd->getQSTeam()
// END SUGARCRM flav=pro ONLY 
          );
$quicksearch_js = $qsd->getQSScripts();
$quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';

$xtpl->assign("JAVASCRIPT", get_set_focus_js().get_validate_record_js() . $quicksearch_js);


$xtpl->assign("ID", $focus->id);
$xtpl->assign("TITLE", $focus->title);
$xtpl->assign("DESCRIPTION", $focus->description);

// BEGIN SUGARCRM flav=pro ONLY 
if (empty($focus->id) && !isset($_REQUEST['isDuplicate'])) {
  $xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $current_user->default_team));
  $xtpl->assign("TEAM_NAME", $current_user->default_team_name);
  $xtpl->assign("TEAM_ID", $current_user->default_team);
}
else {
  $xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $focus->team_id));
  $xtpl->assign("TEAM_NAME", $focus->assigned_name);
  $xtpl->assign("TEAM_ID", $focus->team_id);
}
$xtpl->parse("main.pro");
// END SUGARCRM flav=pro ONLY 

$seedForumTopic = new ForumTopic();

$category_select = "";

if(!isset($focus->category))
  $category_select = get_select_options_with_id($seedForumTopic->get_topics(), "");
else
  $category_select = get_select_options_with_id($seedForumTopic->get_topics(), $focus->category);

$xtpl->assign("CATEGORY_OPTIONS", $category_select);

if(empty($category_select))
{
  $xtpl->assign("SAVE_DISABLED", "disabled");
  $xtpl->assign("CREATE_FORUM_TOPIC", $mod_strings['MSG_CREATE_FORUM_TOPIC']);
}

if (isset($_REQUEST['account_id'])) {
	$xtpl->assign("ACCOUNT_ID", $_REQUEST['account_id']);
}	
if (isset($_REQUEST['bug_id'])) {
	$xtpl->assign("BUG_ID", $_REQUEST['bug_id']);
}

//Add Custom Fields
require_once('modules/DynamicFields/templates/Files/EditView.php');

$xtpl->assign("THEME", $theme);
$xtpl->parse("main");
$xtpl->out("main");

require_once('include/javascript/javascript.php');
$javascript = new javascript();
$javascript->setFormName('EditView');
$javascript->setSugarBean($focus);
$javascript->addAllFields('');

// BEGIN SUGARCRM flav=pro ONLY 
$javascript->addFieldGeneric('team_name', 'varchar', $app_strings['LBL_TEAM'] ,'true');
$javascript->addToValidateBinaryDependency('team_name', 'alpha', $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $app_strings['LBL_TEAM'], 'false', '', 'team_id');
// END SUGARCRM flav=pro ONLY 

//$javascript->addToValidateBinaryDependency('user_name', 'alpha', $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $app_strings['LBL_ASSIGNED_TO'], 'false', '', 'assigned_user_id');
echo $javascript->getScript();

?>
