<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('modules/EmailMan/Forms.php');

global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

if (!is_admin($current_user)&&!is_admin_for_module($GLOBALS['current_user'],'Emails')&&!is_admin_for_module($GLOBALS['current_user'],'Campaigns')) sugar_die("Unauthorized access to administration.");

echo get_module_title($mod_strings['LBL_MODULE_ID'], $mod_strings['LBL_MODULE_NAME'].": ".$mod_strings['LBL_CONFIGURE_SETTINGS'], true);
global $currentModule;





$focus = new Administration();
$focus->retrieveSettings(); //retrieve all admin settings.
$GLOBALS['log']->info("Mass Emailer(EmailMan) ConfigureSettings view");

$xtpl=new XTemplate ('modules/EmailMan/config.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

$xtpl->assign("RETURN_MODULE", "Administration");
$xtpl->assign("RETURN_ACTION", "index");

$xtpl->assign("MODULE", $currentModule);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("HEADER", get_module_title("EmailMan", "{MOD.LBL_CONFIGURE_SETTINGS}", true));

$xtpl->assign("notify_fromaddress", $focus->settings['notify_fromaddress']);
$xtpl->assign("notify_send_from_assigning_user", (isset($focus->settings['notify_send_from_assigning_user']) && !empty($focus->settings['notify_send_from_assigning_user'])) ? "checked='checked'" : "");
$xtpl->assign("notify_on", ($focus->settings['notify_on']) ? "checked='checked'" : "");
$xtpl->assign("notify_fromname", $focus->settings['notify_fromname']);
$xtpl->assign("notify_allow_default_outbound_on", (!empty($focus->settings['notify_allow_default_outbound']) && $focus->settings['notify_allow_default_outbound']) ? "checked='checked'" : "");

// show Gmail defaults link
$showGmail = ($focus->settings['mail_sendtype'] == 'SMTP') ? 'inline' : 'none';
$xtpl->assign("gmailSmtpLink", $showGmail);
$xtpl->assign("mail_smtpserver", $focus->settings['mail_smtpserver']);
$xtpl->assign("mail_smtpport", $focus->settings['mail_smtpport']);
$xtpl->assign("mail_smtpuser", $focus->settings['mail_smtpuser']);
$xtpl->assign("mail_smtppass", $focus->settings['mail_smtppass']);
$xtpl->assign("mail_smtpauth_req", ($focus->settings['mail_smtpauth_req']) ? "checked='checked'" : "");
$xtpl->assign("MAIL_SSL_OPTIONS", get_select_options_with_id($app_list_strings['email_settings_for_ssl'], $focus->settings['mail_smtpssl']));

//Assign the current users email for the test send dialogue.
$xtpl->assign("CURRENT_USER_EMAIL", $current_user->email1);

$showSendMail = FALSE;
$outboundSendTypeCSSClass = "yui-hidden";
if(isset($sugar_config['allow_sendmail_outbound']) && $sugar_config['allow_sendmail_outbound']) 
{
	$showSendMail = TRUE;
	$app_list_strings['notifymail_sendtype']['sendmail'] = 'sendmail';
	$outboundSendTypeCSSClass = "";
}

$xtpl->assign("OUTBOUND_TYPE_CLASS", $outboundSendTypeCSSClass);
$xtpl->assign("mail_sendtype_options", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->settings['mail_sendtype']));

///////////////////////////////////////////////////////////////////////////////
////	USER EMAIL DEFAULTS
// editors
$editors = $app_list_strings['dom_email_editor_option'];
$newEditors = array();
foreach($editors as $k => $v) {
	if($k != "") { $newEditors[$k] = $v; }
}

// preserve attachments
$preserveAttachments = '';
if(isset($sugar_config['email_default_delete_attachments']) && $sugar_config['email_default_delete_attachments'] == true) {
	$preserveAttachments = 'CHECKED';
} 
$xtpl->assign('DEFAULT_EMAIL_DELETE_ATTACHMENTS', $preserveAttachments);
////	END USER EMAIL DEFAULTS
///////////////////////////////////////////////////////////////////////////////


//setting to manage.
//emails_per_run
//tracking_entities_location_type default or custom
//tracking_entities_location http://www.sugarcrm.com/track/

//////////////////////////////////////////////////////////////////////////////
////	EMAIL SECURITY
if(!isset($sugar_config['email_xss']) || empty($sugar_config['email_xss'])) {
	$sugar_config['email_xss'] = getDefaultXssTags();
}

foreach(unserialize(base64_decode($sugar_config['email_xss'])) as $k => $v) {
	$xtpl->assign($k."Checked", 'CHECKED');
}

//clean_xss('here');
////	END EMAIL SECURITY
///////////////////////////////////////////////////////////////////////////////

require_once('modules/Emails/Email.php');
$email = new Email();
$xtpl->assign('ROLLOVER', $email->rolloverStyle);

$xtpl->assign("JAVASCRIPT",get_validate_record_js());
$xtpl->parse("main");

$xtpl->out("main");
?>