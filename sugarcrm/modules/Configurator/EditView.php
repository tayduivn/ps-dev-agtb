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



if(!is_admin($current_user)){
	sugar_die('Admin Only');
}
require_once('modules/Administration/Forms.php');
echo get_module_title($mod_strings['LBL_MODULE_ID'], $mod_strings['LBL_MODULE_NAME'].": ", true);
require_once('modules/Configurator/Configurator.php');
$configurator = new Configurator();
$sugarConfig = SugarConfig::getInstance();
$focus = new Administration();
$configurator->parseLoggerSettings();

if(!empty($_POST['save'])){
	$configurator->saveConfig();
	$focus->saveConfig();
	//Clear the Contacts file b/c portal flag affects rendering
	if(file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/Contacts/EditView.tpl')) {
	   unlink($GLOBALS['sugar_config']['cache_dir'].'modules/Contacts/EditView.tpl');
	}
	header('Location: index.php?module=Administration&action=index');
}

$focus->retrieveSettings();
if(!empty($_POST['restore'])){
	$configurator->restoreConfig();
}


require_once('include/SugarLogger/SugarLogger.php');
$sugar_smarty = new Sugar_Smarty();


$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);
$sugar_smarty->assign('config', $configurator->config);
$sugar_smarty->assign('error', $configurator->errors);
$sugar_smarty->assign('THEMES', SugarThemeRegistry::availableThemes());
$sugar_smarty->assign('LANGUAGES', get_languages());
$sugar_smarty->assign("JAVASCRIPT",get_set_focus_js(). get_configsettings_js());
$sugar_smarty->assign('company_logo', SugarThemeRegistry::current()->getImageURL('company_logo.png'));
$sugar_smarty->assign("settings", $focus->settings);
$sugar_smarty->assign("mail_sendtype_options", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->settings['mail_sendtype']));
if(!empty($focus->settings['proxy_on'])){
	$sugar_smarty->assign("PROXY_CONFIG_DISPLAY", 'inline');
}else{
	$sugar_smarty->assign("PROXY_CONFIG_DISPLAY", 'none');
}
if(!empty($focus->settings['proxy_auth'])){
	$sugar_smarty->assign("PROXY_AUTH_DISPLAY", 'inline');
}else{
		$sugar_smarty->assign("PROXY_AUTH_DISPLAY", 'none');
}

$ini_session_val = ini_get('session.gc_maxlifetime');
if(!empty($focus->settings['system_session_timeout'])){
    $sugar_smarty->assign("SESSION_TIMEOUT", $focus->settings['system_session_timeout']);
}else{
    $sugar_smarty->assign("SESSION_TIMEOUT", $ini_session_val);
}

if (!empty($configurator->config['logger']['level'])) {
	$sugar_smarty->assign('log_levels', get_select_options_with_id(  SugarLogger::$log_levels, $configurator->config['logger']['level']));
} else {
	$sugar_smarty->assign('log_levels', get_select_options_with_id(  SugarLogger::$log_levels, ''));
}
if (!empty($configurator->config['logger']['file']['suffix'])) {
	$sugar_smarty->assign('filename_suffix', get_select_options_with_id(  SugarLogger::$filename_suffix,$configurator->config['logger']['file']['suffix']));
} else {
	$sugar_smarty->assign('filename_suffix', get_select_options_with_id(  SugarLogger::$filename_suffix,''));
}

//nsingh- moved to locale.php , bug 18064.
	//$sugar_smarty->assign("exportCharsets", get_select_options_with_id($locale->getCharsetSelect(), $sugar_config['default_export_charset']));*/
$sugar_smarty->display('modules/Configurator/EditView.tpl');


$javascript = new javascript();
$javascript->setFormName("ConfigureSettings");
$javascript->addFieldGeneric("notify_fromaddress", "email", $mod_strings['LBL_NOTIFY_FROMADDRESS'], TRUE, "");
$javascript->addFieldGeneric("notify_subject", "varchar", $mod_strings['LBL_NOTIFY_SUBJECT'], TRUE, "");
$javascript->addFieldGeneric("proxy_host", "varchar", $mod_strings['LBL_PROXY_HOST'], TRUE, "");
$javascript->addFieldGeneric("proxy_port", "int", $mod_strings['LBL_PROXY_PORT'], TRUE, "");
$javascript->addFieldGeneric("proxy_password", "varchar", $mod_strings['LBL_PROXY_PASSWORD'], TRUE, "");
$javascript->addFieldGeneric("proxy_username", "varchar", $mod_strings['LBL_PROXY_USERNAME'], TRUE, "");
$javascript->addFieldRange("system_session_timeout", "int", $mod_strings['SESSION_TIMEOUT'], TRUE, "", 0, $ini_session_val);
echo $javascript->getScript();
?>
