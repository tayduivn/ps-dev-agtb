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
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: EditView.php 57829 2010-08-19 23:26:17Z kjing $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright(C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



$sugar_smarty = new Sugar_Smarty();
require_once('include/export_utils.php');
require_once('modules/Configurator/Configurator.php');
require_once('modules/Users/Forms.php');
require_once('modules/Users/UserSignature.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;

$admin = new Administration();
$admin->retrieveSettings();

$focus = new User();

if(isset($_REQUEST['record'])) {
    if(!$is_current_admin && $_REQUEST['record'] != $current_user->id) sugar_die("Unauthorized access to administration.");
    $focus->retrieve($_REQUEST['record']);
}

if(!$is_super_admin && $GLOBALS['current_user']->isAdminForModule('Users') && $focus->is_admin == 1) sugar_die("Unauthorized access to administrator.");

else if(!isset($_REQUEST['record'])){
}



echo "\n<p>\n";
$params = array();
if(empty($focus->id)){
	$params[] = $GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL'];
}else{
	$params[] = "<a href='index.php?module=Users&action=DetailView&record={$focus->id}'>".$locale->getLocaleFormattedName($focus->first_name,$focus->last_name)."</a>";
	$params[] = $GLOBALS['app_strings']['LBL_EDIT_BUTTON_LABEL'];
}

$index_url = ($is_current_admin) ? "index.php?module=Users&action=index" : "index.php?module=Users&action=DetailView&record={$focus->id}"; 
echo getClassicModuleTitle("Users", $params, true,$index_url);

$GLOBALS['log']->info('User edit view');
$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {

	$sugar_smarty->assign('RETURN_MODULE', $_REQUEST['return_module']);
	$sugar_smarty->assign('RETURN_ACTION', $_REQUEST['return_action']);
	$sugar_smarty->assign('RETURN_ID', $_REQUEST['record']);

} else {
	if(isset($_REQUEST['return_module'])) $sugar_smarty->assign('RETURN_MODULE', $_REQUEST['return_module']);
	else { $sugar_smarty->assign('RETURN_MODULE', $focus->module_dir);}
	if(isset($_REQUEST['return_id'])) $sugar_smarty->assign('RETURN_ID', $_REQUEST['return_id']);
	else { $sugar_smarty->assign('RETURN_ID', $focus->id); }
	if(isset($_REQUEST['return_action'])) $sugar_smarty->assign('RETURN_ACTION', $_REQUEST['return_action']);
	else { $sugar_smarty->assign('RETURN_ACTION', 'DetailView'); }
}

//BEGIN SUGARCRM flav!=com ONLY
require_once('include/SugarFields/Fields/Image/SugarFieldImage.php');
$sfimage = new SugarFieldImage('Image');
$displayParams = array();
$displayParams['formName'] = 'EditView';
$sfimage->ss->assign('APP', $app_strings);
$sfimage->ss->assign('MOD', $mod_strings);
$sfimage->ss->assign('picture_value', $focus->picture);
$code = $sfimage->getUserEditView('fields', $focus->field_defs['picture'], $displayParams, 0);
$sugar_smarty->assign('PICTURE_FILE_CODE', $code);
//END SUGARCRM flav!=com ONLY

$sugar_smarty->assign('JAVASCRIPT',user_get_validate_record_js().user_get_chooser_js().user_get_confsettings_js().getVersionedScript("modules/Users/User.js"));
$sugar_smarty->assign('PRINT_URL', 'index.php?'.$GLOBALS['request_string']);
$sugar_smarty->assign('ID', $focus->id);

$sugar_smarty->assign('USER_NAME', $focus->user_name);
$sugar_smarty->assign('FIRST_NAME', $focus->first_name);
$sugar_smarty->assign('LAST_NAME', $focus->last_name);
$sugar_smarty->assign('TITLE', $focus->title);
$sugar_smarty->assign('DEPARTMENT', $focus->department);
$sugar_smarty->assign('REPORTS_TO_ID', $focus->reports_to_id);
$sugar_smarty->assign('REPORTS_TO_NAME', get_assigned_user_name($focus->reports_to_id));
$sugar_smarty->assign('PHONE_HOME', $focus->phone_home);
$sugar_smarty->assign('PHONE_MOBILE', $focus->phone_mobile);
$sugar_smarty->assign('PHONE_WORK', $focus->phone_work);
$sugar_smarty->assign('PHONE_OTHER', $focus->phone_other);
$sugar_smarty->assign('PHONE_FAX', $focus->phone_fax);
$sugar_smarty->assign('EMAIL1', $focus->email1);
$sugar_smarty->assign('EMAIL2', $focus->email2);
$sugar_smarty->assign('ADDRESS_STREET', $focus->address_street);
$sugar_smarty->assign('ADDRESS_CITY', $focus->address_city);
$sugar_smarty->assign('ADDRESS_STATE', $focus->address_state);
$sugar_smarty->assign('ADDRESS_POSTALCODE', $focus->address_postalcode);
$sugar_smarty->assign('ADDRESS_COUNTRY', $focus->address_country);
$sugar_smarty->assign('DESCRIPTION', $focus->description);

/// IKEA: I am here












//require_once($theme_path.'config.php');




$sugar_smarty->assign("MAIL_SENDTYPE", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->getPreference('mail_sendtype')));
//Add Custom Fields
$xtpl = $sugar_smarty;
require_once('modules/DynamicFields/templates/Files/EditView.php');

if($is_current_admin) {
	$status  = "<td scope='row'><slot>".$mod_strings['LBL_STATUS'].": <span class='required'>".$app_strings['LBL_REQUIRED_SYMBOL']."</span></slot></td>\n";
	$status .= "<td><select name='status' tabindex='1'";
	if((!empty($sugar_config['default_user_name']) &&
		$sugar_config['default_user_name']== $focus->user_name &&
		isset($sugar_config['lock_default_user_name']) &&
		$sugar_config['lock_default_user_name']) || $admin_edit_self)
	{
		$status .= ' disabled="disabled" ';
	}
	$status .= ">";
	$status .= get_select_options_with_id($app_list_strings['user_status_dom'], $focus->status);
	$status .= "</select></td>\n";
	$sugar_smarty->assign("USER_STATUS_OPTIONS", $status);
}
/*
RKA - Pretty sure this is no longer needed
if($is_current_admin && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
	$record = '';
	if(!empty($_REQUEST['record'])){
		$record = 	$_REQUEST['record'];
	}
	$sugar_smarty->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$record. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>");
}
*/

if(!empty($sugar_config['default_user_name']) &&
	$sugar_config['default_user_name'] == $focus->user_name &&
	isset($sugar_config['lock_default_user_name']) &&
	$sugar_config['lock_default_user_name'])
{
	$status .= ' disabled ';
	$sugar_smarty->assign('FIRST_NAME_DISABLED', 'disabled="disabled"');
	$sugar_smarty->assign('USER_NAME_DISABLED', 'disabled="disabled"');
	$sugar_smarty->assign('LAST_NAME_DISABLED', 'disabled="disabled"');
	$sugar_smarty->assign('IS_ADMIN_DISABLED', 'disabled="disabled"');
	$sugar_smarty->assign('IS_PORTAL_ONLY_DISABLED', 'disabled="disabled"');
	$sugar_smarty->assign('IS_GROUP_DISABLED', 'disabled="disabled"');
}


/*
RKA - No longer needed
$sugar_smarty->assign("USER_TYPE_DESC", $mod_strings['LBL_'.$usertype.'_DESC']);
$sugar_smarty->assign("USER_TYPE_LABEL", $mod_strings['LBL_'.$usertype.'_USER']);
$sugar_smarty->assign('USER_TYPE',$usertype);
*/






/////////////////////////////////////////////
/// Handle email account selections for users
/////////////////////////////////////////////
 $hide_if_can_use_default = true;
if( !($usertype=='GROUP' || $usertype=='PORTAL_ONLY') )
{
    // email smtp
    $systemOutboundEmail = new OutboundEmail();
    $systemOutboundEmail = $systemOutboundEmail->getSystemMailerSettings();
    $mail_smtpserver = $systemOutboundEmail->mail_smtpserver;
    $mail_smtptype = $systemOutboundEmail->mail_smtptype;
    $mail_smtpport = $systemOutboundEmail->mail_smtpport;
    $mail_smtpssl = $systemOutboundEmail->mail_smtpssl;
    $mail_smtpuser = "";
    $mail_smtppass = "";
    $mail_smtpdisplay = $systemOutboundEmail->mail_smtpdisplay;
    $hide_if_can_use_default = true;
    $mail_smtpauth_req=true;

    if( !$systemOutboundEmail->isAllowUserAccessToSystemDefaultOutbound() )
    {

    	$mail_smtpauth_req = $systemOutboundEmail->mail_smtpauth_req;
        $userOverrideOE = $systemOutboundEmail->getUsersMailerForSystemOverride($current_user->id);
        if($userOverrideOE != null) {

            $mail_smtpuser = $userOverrideOE->mail_smtpuser;
            $mail_smtppass = $userOverrideOE->mail_smtppass;

        }


        if(!$mail_smtpauth_req &&
            ( empty($systemOutboundEmail->mail_smtpserver) || empty($systemOutboundEmail->mail_smtpuser)
            || empty($systemOutboundEmail->mail_smtppass)))
        {
            $hide_if_can_use_default = true;
        }
        else{
            $hide_if_can_use_default = false;
        }
    }

    $sugar_smarty->assign("mail_smtpdisplay", $mail_smtpdisplay);
    $sugar_smarty->assign("mail_smtpserver", $mail_smtpserver);
    $sugar_smarty->assign("mail_smtpuser", $mail_smtpuser);
    $sugar_smarty->assign("mail_smtppass", "");
    $sugar_smarty->assign("mail_haspass", empty($systemOutboundEmail->mail_smtppass)?0:1);
    $sugar_smarty->assign("mail_smtpauth_req", $mail_smtpauth_req);
    $sugar_smarty->assign('MAIL_SMTPPORT',$mail_smtpport);
    $sugar_smarty->assign('MAIL_SMTPSSL',$mail_smtpssl);
}
$sugar_smarty->assign('HIDE_IF_CAN_USE_DEFAULT_OUTBOUND',$hide_if_can_use_default );

$reports_to_change_button_html = '';

if($is_current_admin) {
	//////////////////////////////////////
	///
	/// SETUP USER POPUP

	$reportsDisplayName = showFullName() ? 'name' : 'user_name';
	$popup_request_data = array(
		'call_back_function' => 'set_return',
		'form_name' => 'EditView',
		'field_to_name_array' => array(
			'id' => 'reports_to_id',
			"$reportsDisplayName" => 'reports_to_name',
			),
		);

	$json = getJSONobj();
	$encoded_popup_request_data = $json->encode($popup_request_data);
	$sugar_smarty->assign('encoded_popup_request_data', $encoded_popup_request_data);

	//
	///////////////////////////////////////

	$reports_to_change_button_html = '<input type="button"'
	. " title=\"{$app_strings['LBL_SELECT_BUTTON_TITLE']}\""
	. " accesskey=\"{$app_strings['LBL_SELECT_BUTTON_KEY']}\""
	. " value=\"{$app_strings['LBL_SELECT_BUTTON_LABEL']}\""
	. ' tabindex="5" class="button" name="btn1"'
	. " onclick='open_popup(\"Users\", 600, 400, \"\", true, false, {$encoded_popup_request_data});'"
	. "' />";
} else {
	$sugar_smarty->assign('IS_ADMIN_DISABLED', 'disabled="disabled"');
}
//BEGIN SUGARCRM flav!=dce ONLY
//BEGIN SUGARCRM flav=ent ONLY
$sugar_smarty->assign('OC_STATUS', get_select_options_with_id($app_list_strings['oc_status_dom'], $focus->getPreference('OfflineClientStatus')));
//END SUGARCRM flav=ent ONLY
//END SUGARCRM flav!=dce ONLY
$sugar_smarty->assign('REPORTS_TO_CHANGE_BUTTON', $reports_to_change_button_html);

///////////////////////////////////////////////////////////////////////////////
////	EMAIL OPTIONS
// We need to turn off the requiredness of emails if it is a group or portal user
if ($usertype == 'GROUP' || $usertype == 'PORTAL_ONLY' ) {
    global $dictionary;
    $dictionary['User']['fields']['email1']['required'] = false;
}
// hack to disable email field being required if it shouldn't be required
if ( $sugar_smarty->get_template_vars("REQUIRED_EMAIL_ADDRESS") == '0' )
    $GLOBALS['dictionary']['User']['fields']['email1']['required'] = false;
$sugar_smarty->assign("NEW_EMAIL", getEmailAddressWidget($focus, "email1", $focus->email1, "EditView"));
// hack to undo that previous hack
if ( $sugar_smarty->get_template_vars("REQUIRED_EMAIL_ADDRESS") == '0' )
    $GLOBALS['dictionary']['User']['fields']['email1']['required'] = true;
$sugar_smarty->assign('EMAIL_LINK_TYPE', get_select_options_with_id($app_list_strings['dom_email_link_type'], $focus->getPreference('email_link_type')));
/////	END EMAIL OPTIONS
///////////////////////////////////////////////////////////////////////////////


if ($is_current_admin) {
$employee_status = '<select tabindex="5" name="employee_status">';
$employee_status .= get_select_options_with_id($app_list_strings['employee_status_dom'], $focus->employee_status);
$employee_status .= '</select>';
} else {
	$employee_status = $focus->employee_status;
}
$sugar_smarty->assign('EMPLOYEE_STATUS_OPTIONS', $employee_status);
$sugar_smarty->assign('EMPLOYEE_STATUS_OPTIONS', $employee_status);
