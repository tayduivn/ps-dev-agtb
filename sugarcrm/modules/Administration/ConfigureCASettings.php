<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * Configure Default Call Assistant setting
 *
 * Author: Felix Nilam
 * Date: 29/10/2007
 ********************************************************************************/

require_once ('include/Sugar_Smarty.php');
require_once('include/utils.php');

global $app_strings;
global $timedate;
global $app_list_strings;
global $mod_strings;
global $sugar_version, $sugar_config;
global $theme;
global $current_user;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');

$GLOBALS['log']->info("Call Assistant config setting");

if(!is_admin($current_user)){
	sugar_die("Unauthorised Access to Administration");
}

// Handle Save
if(isset($_POST['save'])){
	$planned_call = $_REQUEST['planned_call_period'];
	$opportunity_status_exclude = $_REQUEST['opportunity_status_exclude'];
	$case_status_exclude = $_REQUEST['case_status_exclude'];
	$lead_status_exclude = $_REQUEST['lead_status_exclude'];
	$show_planned_calls = $_REQUEST['show_planned_calls'];
	$show_related_opportunities = $_REQUEST['show_related_opportunities'];
	$show_related_cases = $_REQUEST['show_related_cases'];
	$show_related_account_contacts = $_REQUEST['show_related_account_contacts'];
	$version = $_REQUEST['version'];
	
	// setup config string
	$config_string = "<?php\n";
	$config_string .= "\$inbound_call_config = array(\n";
	$config_string .= "'version' => '".$version."', // PRO or ENT\n";
	$config_string .= "'planned_call_period' => '".$planned_call."',\n";
	$config_string .= "'opportunity_status_exclude' => array('".implode("','", $opportunity_status_exclude)."'),\n";
	$config_string .= "'case_status_exclude' => array('".implode("','", $case_status_exclude)."'),\n";
	$config_string .= "'lead_status_exclude' => array('".implode("','", $lead_status_exclude)."'),\n";
	$config_string .= "'show_planned_calls' => ".$show_planned_calls.",\n";
	$config_string .= "'show_related_opportunities' => ".$show_related_opportunities.",\n";
	$config_string .= "'show_related_cases' => ".$show_related_cases.",\n";
	$config_string .= "'show_related_account_contacts' => ".$show_related_account_contacts.",\n";
	$config_string .= ");\n?>";
	
	$fp = fopen('fonality/include/InboundCall/inbound_call_config.php','w');
	fwrite($fp, $config_string);
	fclose($fp);
	
	// redirect to Admin index
	header("Location: index.php?module=Administration&action=index");
	exit();
}

require_once('fonality/include/InboundCall/inbound_call_config.php');
$tpl = new Sugar_Smarty();
$tpl->assign("MOD", $mod_strings);
$tpl->assign("APP", $app_strings);

$tpl->assign("USER_DATEFORMAT", '('.$timedate->get_user_date_format().')');
$tpl->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());
$time_format = $timedate->get_user_time_format();
$tpl->assign("TIME_FORMAT", '('.$time_format.')');

// assign config 
$tpl->assign("VERSION", $inbound_call_config['version']);
$tpl->assign("PLANNED_CALL_PERIOD_OPTIONS", get_select_options_with_id($app_list_strings['planned_call_period_dom'],$inbound_call_config['planned_call_period']));
$tpl->assign("OPPORTUNITY_STATUS_EXCLUDE_OPTIONS", get_select_options_with_id($app_list_strings['sales_stage_dom'],$inbound_call_config['opportunity_status_exclude']));
$tpl->assign("CASE_STATUS_EXCLUDE_OPTIONS", get_select_options_with_id($app_list_strings['case_status_dom'],$inbound_call_config['case_status_exclude']));
$tpl->assign("LEAD_STATUS_EXCLUDE_OPTIONS", get_select_options_with_id($app_list_strings['lead_status_dom'],$inbound_call_config['lead_status_exclude']));
if($inbound_call_config['show_planned_calls']){
	$tpl->assign("SHOW_PLANNED_CALLS_CHECKED", "checked");
}
if($inbound_call_config['show_related_opportunities']){
	$tpl->assign("SHOW_RELATED_OPPORTUNITIES_CHECKED", "checked");
}
if($inbound_call_config['show_related_cases']){
	$tpl->assign("SHOW_RELATED_CASES_CHECKED", "checked");
}
if($inbound_call_config['show_related_account_contacts']){
	$tpl->assign("SHOW_RELATED_ACCOUNT_CONTACTS_CHECKED", "checked");
}
$tpl->assign("SERVER_NAME", $sugar_config['site_url']);
$tpl->display('modules/Administration/ConfigureCASettings.tpl');
?>
