<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * EditView for Project
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: EditView.php 17070 2006-10-13 22:09:18 +0000 (Fri, 13 Oct 2006) awu $

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Project/Project.php');
require_once('include/time.php');
require_once('modules/Project/Forms.php');

global $timedate;
global $app_strings;
global $app_list_strings;
global $current_language;
global $current_user;
global $sugar_version, $sugar_config;

$focus = new Project();

if(!empty($_REQUEST['record']))
{
    $focus->retrieve($_REQUEST['record']);
}

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_PROJECT_TEMPLATE'].": ".$focus->name, true);
echo "\n</p>\n";
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');

$GLOBALS['log']->info("Project detail view");

$xtpl=new XTemplate ('modules/Project/ProjectTemplatesEditView.html');

/// Users Popup
$json = getJSONobj();
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

///
/// Assign the template variables
///

$xtpl->assign('MOD', $mod_strings);
$xtpl->assign('APP', $app_strings);
$xtpl->assign('name', $focus->name);
$xtpl->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
$xtpl->assign('USER_DATEFORMAT', '('. $timedate->get_user_date_format().')');
if(isset($focus->estimated_start_date))
    $xtpl->assign('START_DATE', $focus->estimated_start_date);
if(isset($focus->estimated_end_date))
    $xtpl->assign('END_DATE', $focus->estimated_end_date);

if (isset ($_REQUEST['status'])) {
    $focus->status = $_REQUEST['status'];
}
elseif (empty ($focus->status)) {
    $focus->status = $app_list_strings['project_status_default'];
}
if (isset ($_REQUEST['priority'])) {
    $focus->priority = $_REQUEST['status'];
}
elseif (empty ($focus->priority)) {
    $focus->priority = $app_list_strings['project_priority_default'];
}

if (empty($focus->assigned_user_id) && empty($focus->id))  $focus->assigned_user_id = $current_user->id;
if (empty($focus->assigned_name) && empty($focus->id))  $focus->assigned_user_name = $current_user->user_name;
$xtpl->assign("ASSIGNED_USER_OPTIONS", get_select_options_with_id(get_user_array(TRUE, "Active", $focus->assigned_user_id), $focus->assigned_user_id));
$xtpl->assign("ASSIGNED_USER_NAME", $focus->assigned_user_name);
$xtpl->assign("ASSIGNED_USER_ID", $focus->assigned_user_id );

$xtpl->assign('description', $focus->description);
$change_parent_button = "<input title='".$app_strings['LBL_SELECT_BUTTON_TITLE']
    ."' accessKey='".$app_strings['LBL_SELECT_BUTTON_KEY']
    ."' tabindex='2' type='button' class='button' value='"
    .$app_strings['LBL_SELECT_BUTTON_LABEL']
    ."' name='button' LANGUAGE=javascript onclick='return window.open(\"index.php?module=\"+ document.EditView.parent_type.value + \"&action=Popup&html=Popup_picker&form=TasksEditView\",\"test\",\"width=600,height=400,resizable=1,scrollbars=1\");'>";
$xtpl->assign("CHANGE_PARENT_BUTTON", $change_parent_button);

// BEGIN SUGARCRM flav=pro ONLY 
if (empty($focus->id) && !isset($_REQUEST['isDuplicate'])) {    
    $xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $current_user->default_team));
    $xtpl->assign("TEAM_NAME", $current_user->default_team_name);
    $xtpl->assign("TEAM_ID", $current_user->default_team);
}
else {
    $xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $focus->team_id));
    $xtpl->assign("TEAM_NAME", $focus->team_name);
    $xtpl->assign("TEAM_ID", $focus->team_id);
}

$xtpl->parse("main.pro");
// END SUGARCRM flav=pro ONLY 

if (!empty($_REQUEST['opportunity_name']) && empty($focus->name)) {
        $focus->name = $_REQUEST['opportunity_name'];
}
if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
    $focus->id = "";
}

// linked record ids
if(isset($_REQUEST['account_id'])) $xtpl->assign("ACCOUNT_ID", $_REQUEST['account_id']);
if(isset($_REQUEST['contact_id'])) $xtpl->assign("CONTACT_ID", $_REQUEST['contact_id']);
if(isset($_REQUEST['opportunity_id'])) $xtpl->assign("OPPORTUNITY_ID", $_REQUEST['opportunity_id']);
if(isset($_REQUEST['quote_id'])) $xtpl->assign("QUOTE_ID", $_REQUEST['quote_id']);
if(isset($_REQUEST['email_id'])) $xtpl->assign("EMAIL_ID", $_REQUEST['email_id']);

if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
// handle Create $module then Cancel
if (empty($_REQUEST['return_id'])) {
    $xtpl->assign("RETURN_ACTION", 'index');
}

require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$sqs_objects = array('assigned_user_name' => $qsd->getQSUser(),
// BEGIN SUGARCRM flav=pro ONLY 
                    'team_name' => $qsd->getQSTeam()
// END SUGARCRM flav=pro ONLY 
                    );
$quicksearch_js = $qsd->getQSScripts();
$quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';

$xtpl->assign("JAVASCRIPT", get_set_focus_js().get_validate_record_js() . $quicksearch_js);
$xtpl->assign("THEME", $theme);
$xtpl->assign("IMAGE_PATH", $image_path);$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("NAME", $focus->name);
$xtpl->assign("STATUS_OPTIONS", get_select_options_with_id($app_list_strings['project_status_dom'], $focus->status));
$xtpl->assign("PRIORITY_OPTIONS", get_select_options_with_id($app_list_strings['project_priority_options'], $focus->priority));

//Add Custom Fields
require_once('modules/DynamicFields/templates/Files/EditView.php');

global $current_user;
if(is_admin($current_user)
    && $_REQUEST['module'] != 'DynamicLayout'
    && !empty($_SESSION['editinplace']))
{
    $record = '';
    if(!empty($_REQUEST['record']))
    {
        $record =   $_REQUEST['record'];
    }

    $xtpl->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action="
        .$_REQUEST['action'] ."&from_module=".$_REQUEST['module']
        ."&record=".$record. "'>".get_image($image_path
        ."EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>");   
}
// BEGIN SUGARCRM flav=pro ONLY 
//$xtpl->parse("main.pro");
/*
// END SUGARCRM flav=pro ONLY 
$xtpl->parse("main.open_source");
// BEGIN SUGARCRM flav=pro ONLY 
*/
// END SUGARCRM flav=pro ONLY 
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
$javascript->addToValidateBinaryDependency('assigned_user_name', 'alpha', $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $app_strings['LBL_ASSIGNED_TO'], 'false', '', 'assigned_user_id');

echo $javascript->getScript();
require_once('modules/SavedSearch/SavedSearch.php');
$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('Project')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;

?>
