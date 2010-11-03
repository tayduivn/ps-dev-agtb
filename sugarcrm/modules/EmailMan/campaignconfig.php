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

if (!is_admin($current_user)&&!is_admin_for_module($GLOBALS['current_user'],'Campaigns')) sugar_die("Unauthorized access to administration.");

echo get_module_title($mod_strings['LBL_MODULE_ID'], $mod_strings['LBL_CAMP_MODULE_NAME'].": ".$mod_strings['LBL_CONFIGURE_SETTINGS'], true);
global $currentModule;





$focus = new Administration();
$focus->retrieveSettings(); //retrieve all admin settings.
$GLOBALS['log']->info("Mass Emailer(EmailMan) ConfigureSettings view");

$xtpl=new XTemplate ('modules/EmailMan/campaignconfig.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("THEME", SugarThemeRegistry::current()->__toString());
$xtpl->assign("RETURN_MODULE", "Administration");
$xtpl->assign("RETURN_ACTION", "index");

$xtpl->assign("MODULE", $currentModule);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("HEADER", get_module_title("EmailMan", "{MOD.LBL_CONFIGURE_SETTINGS}", true));


if (isset($focus->settings['massemailer_campaign_emails_per_run']) && !empty($focus->settings['massemailer_campaign_emails_per_run'])) {
	$xtpl->assign("EMAILS_PER_RUN", $focus->settings['massemailer_campaign_emails_per_run']);
} else  {
	$xtpl->assign("EMAILS_PER_RUN", 500);
}

if (!isset($focus->settings['massemailer_tracking_entities_location_type']) or empty($focus->settings['massemailer_tracking_entities_location_type']) or $focus->settings['massemailer_tracking_entities_location_type']=='1') {
	$xtpl->assign("default_checked", "checked");
	$xtpl->assign("TRACKING_ENTRIES_LOCATION_STATE", "disabled");
	$xtpl->assign("TRACKING_ENTRIES_LOCATION",$mod_strings['TRACKING_ENTRIES_LOCATION_DEFAULT_VALUE']);
} else  {
	$xtpl->assign("userdefined_checked", "checked");
	$xtpl->assign("TRACKING_ENTRIES_LOCATION",$focus->settings["massemailer_tracking_entities_location"]);
}

// Change the default campaign to not store a copy of each message.
if (!empty($focus->settings['massemailer_email_copy']) and $focus->settings['massemailer_email_copy']=='1') {
    $xtpl->assign("yes_checked", "checked='checked'");
} else  {
    $xtpl->assign("no_checked", "checked='checked'");
}

$email = new Email();
$xtpl->assign('ROLLOVER', $email->rolloverStyle);

$xtpl->assign("JAVASCRIPT",get_validate_record_js());
$xtpl->parse("main");

$xtpl->out("main");
?>
