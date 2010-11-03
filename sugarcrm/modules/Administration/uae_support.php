<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * Provides button to send the following UAE log files to Fonality:
 * UAE/log/uae_callassistant_log_file.log
 * UAE/log/uae_click2call_log_file.log
 * fonality/include/InboundCall/inbound_call_config.php
 * fonality/include/normalizePhone/default_dial_code.php
 *
 * Author: Felix Nilam
 * Date: 03/04/2010
 ********************************************************************************/

global $theme;
global $current_user;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');

if(!is_admin($current_user)){
	sugar_die("Unauthorised Access to Administration");
}

$tpl = new Sugar_Smarty();

$tpl->display('modules/Administration/uae_support.tpl');
?>
