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

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
	$focus->user_name = "";
}else if(!isset($_REQUEST['record'])){
    if ( !defined('SUGARPDF_USE_DEFAULT_SETTINGS') ) {
        define('SUGARPDF_USE_DEFAULT_SETTINGS', true);
    }
}


$the_query_string = 'module=Users&action=DetailView';
if(isset($_REQUEST['record'])) {
    $the_query_string .= '&record='.$_REQUEST['record'];
}
$buttons = "";
if (!$current_user->is_group){
    if ($focus->id == $current_user->id) {
        $reset_pref_warning = $mod_strings['LBL_RESET_PREFERENCES_WARNING'];
        $reset_home_warning = $mod_strings['LBL_RESET_HOMEPAGE_WARNING'];
    }
    else {
        $reset_pref_warning = $mod_strings['LBL_RESET_PREFERENCES_WARNING_USER'];
        $reset_home_warning = $mod_strings['LBL_RESET_HOMEPAGE_WARNING_USER'];
    }
	$buttons .="<input type='button' class='button' onclick='if(confirm(\"{$reset_pref_warning}\"))window.location=\"".$_SERVER['PHP_SELF'] .'?'.$the_query_string."&reset_preferences=true\";' value='".$mod_strings['LBL_RESET_PREFERENCES']."' />";
	$buttons .="&nbsp;<input type='button' class='button' onclick='if(confirm(\"{$reset_home_warning}\"))window.location=\"".$_SERVER['PHP_SELF'] .'?'.$the_query_string."&reset_homepage=true\";' value='".$mod_strings['LBL_RESET_HOMEPAGE']."' />";
}
if (isset($buttons)) $sugar_smarty->assign("BUTTONS", $buttons);

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
$sugar_smarty->assign('PWDSETTINGS', isset($GLOBALS['sugar_config']['passwordsetting']) ? $GLOBALS['sugar_config']['passwordsetting'] : array());
//BEGIN SUGARCRM flav=pro ONLY
if ( isset($GLOBALS['sugar_config']['passwordsetting']) && isset($GLOBALS['sugar_config']['passwordsetting']['customregex']) ) {
    $pwd_regex=str_replace( "\\","\\\\",$GLOBALS['sugar_config']['passwordsetting']['customregex']);
    $sugar_smarty->assign("REGEX",$pwd_regex);
}
//END SUGARCRM flav=pro ONLY

if(!empty($GLOBALS['sugar_config']['authenticationClass'])){
		$sugar_smarty->assign('EXTERNAL_AUTH_CLASS_1', $GLOBALS['sugar_config']['authenticationClass']);
		$sugar_smarty->assign('EXTERNAL_AUTH_CLASS', $GLOBALS['sugar_config']['authenticationClass']);
}else{
	if(!empty($GLOBALS['system_config']->settings['system_ldap_enabled'])){
		$sugar_smarty->assign('EXTERNAL_AUTH_CLASS_1', $mod_strings['LBL_LDAP']);
		$sugar_smarty->assign('EXTERNAL_AUTH_CLASS', $mod_strings['LBL_LDAP_AUTHENTICATION']);
	}
}
if(!empty($focus->external_auth_only))$sugar_smarty->assign('EXTERNAL_AUTH_ONLY_CHECKED', 'CHECKED');







// check if the user has access to the User Management
$sugar_smarty->assign('USER_ADMIN',$current_user->isAdminForModule('Users')&& !is_admin($current_user));

//BEGIN SUGARCRM flav=sales ONLY
if($current_user->user_type == "UserAdministrator" && !is_admin($current_user)){
    $sugar_smarty->assign('USER_ADMIN', false);
    $sugar_smarty->assign('NON_ADMIN_USER_ADMIN_RIGHTS', true);
}
if(!empty($focus->id) && $focus->user_type == "UserAdministrator" && !is_admin($focus)){
    $sugar_smarty->assign('IS_USER_ADMIN', true); // although wording is similar as above, these are different
}
//END SUGARCRM flav=sales ONLY

///////////////////////////////////////////////////////////////////////////////
////	NEW USER CREATION ONLY
if(empty($focus->id)) {
	$sugar_smarty->assign('SHOW_ADMIN_CHECKBOX','height="30"');
	$sugar_smarty->assign('NEW_USER','1');
}else{
	$sugar_smarty->assign('NEW_USER','0');
	$sugar_smarty->assign('NEW_USER_TYPE','DISABLED');
	//BEGIN SUGARCRM flav=pro ONLY
	$sugar_smarty->assign('confirmReassignJs', $confirmReassignJs);
	$sugar_smarty->assign('REASSIGN_JS', "return confirmReassignRecords();");
	//END SUGARCRM flav=pro ONLY
}

////	END NEW USER CREATION ONLY
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	REDIRECTS FROM COMPOSE EMAIL SCREEN
if(isset($_REQUEST['type']) && (isset($_REQUEST['return_module']) && $_REQUEST['return_module'] == 'Emails')) {
	$sugar_smarty->assign('REDIRECT_EMAILS_TYPE', $_REQUEST['type']);
}
////	END REDIRECTS FROM COMPOSE EMAIL SCREEN
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////	LOCALE SETTINGS
////	Date/time format
$dformat = $locale->getPrecedentPreference($focus->id?'datef':'default_date_format', $focus);
$tformat = $locale->getPrecedentPreference($focus->id?'timef':'default_time_format', $focus);
$timeOptions = get_select_options_with_id($sugar_config['time_formats'], $tformat);
$dateOptions = get_select_options_with_id($sugar_config['date_formats'], $dformat);
$sugar_smarty->assign('TIMEOPTIONS', $timeOptions);
$sugar_smarty->assign('DATEOPTIONS', $dateOptions);
//BEGIN SUGARCRM flav=pro ONLY
///////////////////////////////////////////////////////////////////////////////
/////////  PDF SETTINGS
global $focus_user;
$focus_user = $focus;
if ( !defined('SUGARPDF_USE_FOCUS') ) {
    define('SUGARPDF_USE_FOCUS', true);
}
include_once('include/Sugarpdf/sugarpdf_config.php');
$sugar_smarty->assign('PDF_CLASS',PDF_CLASS);
$sugar_smarty->assign('PDF_UNIT',PDF_UNIT);
$sugar_smarty->assign('PDF_PAGE_FORMAT_LIST',get_select_options_with_id(array_combine(explode(",",PDF_PAGE_FORMAT_LIST), explode(",",PDF_PAGE_FORMAT_LIST)), PDF_PAGE_FORMAT));
$sugar_smarty->assign('PDF_PAGE_ORIENTATION_LIST',get_select_options_with_id(array("P"=>$mod_strings["LBL_PDF_PAGE_ORIENTATION_P"],"L"=>$mod_strings["LBL_PDF_PAGE_ORIENTATION_L"]),PDF_PAGE_ORIENTATION));
$sugar_smarty->assign('PDF_MARGIN_HEADER',PDF_MARGIN_HEADER);
$sugar_smarty->assign('PDF_MARGIN_FOOTER',PDF_MARGIN_FOOTER);
$sugar_smarty->assign('PDF_MARGIN_TOP',PDF_MARGIN_TOP);
$sugar_smarty->assign('PDF_MARGIN_BOTTOM',PDF_MARGIN_BOTTOM);
$sugar_smarty->assign('PDF_MARGIN_LEFT',PDF_MARGIN_LEFT);
$sugar_smarty->assign('PDF_MARGIN_RIGHT',PDF_MARGIN_RIGHT);

require_once('include/Sugarpdf/FontManager.php');
$fontManager = new FontManager();
$fontlist = $fontManager->getSelectFontList();
$sugar_smarty->assign('PDF_FONT_NAME_MAIN',get_select_options_with_id($fontlist, PDF_FONT_NAME_MAIN));
$sugar_smarty->assign('PDF_FONT_SIZE_MAIN',PDF_FONT_SIZE_MAIN);
$sugar_smarty->assign('PDF_FONT_NAME_DATA',get_select_options_with_id($fontlist, PDF_FONT_NAME_DATA));
$sugar_smarty->assign('PDF_FONT_SIZE_DATA',PDF_FONT_SIZE_DATA);
///////// END PDF SETTINGS
////////////////////////////////////////////////////////////////////////////////
//END SUGARCRM flav=pro ONLY

//require_once($theme_path.'config.php');




$sugar_smarty->assign("MAIL_SENDTYPE", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->getPreference('mail_sendtype')));
//Add Custom Fields
$xtpl = $sugar_smarty;
require_once('modules/DynamicFields/templates/Files/EditView.php');

$edit_self = $current_user->id == $focus->id;
$admin_edit_self = is_admin($current_user) && $edit_self;

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
if($is_current_admin && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
	$record = '';
	if(!empty($_REQUEST['record'])){
		$record = 	$_REQUEST['record'];
	}
	$sugar_smarty->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$record. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>");
}

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

//BEGIN SUGARCRM flav=pro ONLY
if(!empty($focus->id)) {
	//If you're an administrator editing the user or if you're a module level admin, then allow the widget to display all Teams
	if($usertype == 'ADMIN' || $GLOBALS['current_user']->isAdminForModule( 'Users')) {
		require_once('include/SugarFields/Fields/Teamset/EmailSugarFieldTeamsetCollection.php');
		$teamsWidget = new EmailSugarFieldTeamsetCollection($focus, $focus->field_defs, '', 'EditView');
		$sugar_smarty->assign('DEFAULT_TEAM_OPTIONS', $teamsWidget->get_code());
	} else {
		require_once('include/SugarFields/Fields/Teamset/EmailSugarFieldTeamsetCollection.php');
		$teamsWidget = new EmailSugarFieldTeamsetCollection($focus, $focus->field_defs, 'get_non_private_teams_array', 'EditView');
		$teamsWidget->user_id = $focus->id;
		$sugar_smarty->assign('DEFAULT_TEAM_OPTIONS', $teamsWidget->get_code());
	}
}

$sugar_smarty->assign('SHOW_TEAM_SELECTION', !empty($focus->id));
$sugar_smarty->assign('IS_PORTALONLY', '0');

if (isset($sugar_config['enable_web_services_user_creation']) && $sugar_config['enable_web_services_user_creation'] &&
	(!empty($focus->portal_only) && $focus->portal_only) || (isset($_REQUEST['usertype']) && $_REQUEST['usertype']=='portal')) {
	$sugar_smarty->assign('IS_PORTALONLY', '1');
	$usertype='PORTAL_ONLY';
}
//END SUGARCRM flav=pro ONLY

//BEGIN SUGARCRM flav=ent ONLY
if((!empty($focus->portal_only) && $focus->portal_only) || (isset($_REQUEST['usertype']) && $_REQUEST['usertype']=='portal')){
	$sugar_smarty->assign('IS_PORTALONLY', '1');
	$usertype='PORTAL_ONLY';
}
//END SUGARCRM flav=ent ONLY
//BEGIN SUGARCRM flav!=sales ONLY

if((!empty($focus->is_group) && $focus->is_group)  || (isset($_REQUEST['usertype']) && $_REQUEST['usertype']=='group')){
	$sugar_smarty->assign('IS_GROUP', '1');
	$usertype='GROUP';
} else
//END SUGARCRM flav!=sales ONLY
	$sugar_smarty->assign('IS_GROUP', '0');

$sugar_smarty->assign("USER_TYPE_DESC", $mod_strings['LBL_'.$usertype.'_DESC']);
$sugar_smarty->assign("USER_TYPE_LABEL", $mod_strings['LBL_'.$usertype.'_USER']);
$sugar_smarty->assign('USER_TYPE',$usertype);


$sugar_smarty->assign('IS_FOCUS_ADMIN', is_admin($focus));

if($edit_self) {
	$sugar_smarty->assign('EDIT_SELF','1');
}
if($admin_edit_self) {
	$sugar_smarty->assign('ADMIN_EDIT_SELF','1');
}





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

//BEGIN SUGARCRM flav!=sales ONLY
/* Module Tab Chooser */
require_once('include/templates/TemplateGroupChooser.php');
require_once('modules/MySettings/TabController.php');
$chooser = new TemplateGroupChooser();
$controller = new TabController();


if($is_current_admin || $controller->get_users_can_edit()) {
	$chooser->display_hide_tabs = true;
} else {
	$chooser->display_hide_tabs = false;
}

$chooser->args['id'] = 'edit_tabs';
$chooser->args['values_array'] = $controller->get_tabs($focus);
foreach($chooser->args['values_array'][0] as $key=>$value) {
    $chooser->args['values_array'][0][$key] = $app_list_strings['moduleList'][$key];
}

foreach($chooser->args['values_array'][1] as $key=>$value) {
    $chooser->args['values_array'][1][$key] = $app_list_strings['moduleList'][$key];
}

foreach($chooser->args['values_array'][2] as $key=>$value) {
    $chooser->args['values_array'][2][$key] = $app_list_strings['moduleList'][$key];
}

$chooser->args['left_name'] = 'display_tabs';
$chooser->args['right_name'] = 'hide_tabs';

$chooser->args['left_label'] =  $mod_strings['LBL_DISPLAY_TABS'];
$chooser->args['right_label'] =  $mod_strings['LBL_HIDE_TABS'];
$chooser->args['title'] =  $mod_strings['LBL_EDIT_TABS'].' <!--not_in_theme!--><img border="0" src="themes/default/images/helpInline.gif" onmouseover="return overlib(\'Choose which tabs are displayed.\', FGCLASS, \'olFgClass\', CGCLASS, \'olCgClass\', BGCLASS, \'olBgClass\', TEXTFONTCLASS, \'olFontClass\', CAPTIONFONTCLASS, \'olCapFontClass\', CLOSEFONTCLASS, \'olCloseFontClass\', WIDTH, -1, NOFOLLOW, \'ol_nofollow\' );" onmouseout="return nd();"/>';

$sugar_smarty->assign('TAB_CHOOSER', $chooser->display());
$sugar_smarty->assign('CHOOSER_SCRIPT','set_chooser();');
$sugar_smarty->assign('CHOOSE_WHICH', $mod_strings['LBL_CHOOSE_WHICH']);
//END SUGARCRM flav!=sales ONLY
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

$messenger_type = '<select tabindex="5" name="messenger_type">';
$messenger_type .= get_select_options_with_id($app_list_strings['messenger_type_dom'], $focus->messenger_type);
$messenger_type .= '</select>';
$sugar_smarty->assign('MESSENGER_TYPE_OPTIONS', $messenger_type);
$sugar_smarty->assign('MESSENGER_ID', $focus->messenger_id);


$sugar_smarty->display('modules/Users/EditView.tpl');
$json = getJSONobj();

require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$sqs_objects = array('EditView_reports_to_name' => $qsd->getQSUser());
$sqs_objects['EditView_reports_to_name']['populate_list'] = array('reports_to_name', 'reports_to_id');

//BEGIN SUGARCRM flav=pro ONLY
if(!empty($focus->id)) {
	$sqs_objects = array_merge($sqs_objects, $teamsWidget->createQuickSearchCode(false));
}
//END SUGARCRM flav=pro ONLY

$quicksearch_js = '<script type="text/javascript" language="javascript">
                    sqs_objects = ' . $json->encode($sqs_objects) . '; enableQS();</script>';
echo $quicksearch_js;

//BEGIN SUGARCRM flav=pro ONLY
$savedSearch = new SavedSearch();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('Users')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
//END SUGARCRM flav=pro ONLY
//echo $str;
//BUG #16298
?>
