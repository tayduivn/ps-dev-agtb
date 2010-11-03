<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************
 * Call Assistant Page
 * Show relevant information regarding an inbound call
 *
 * Author: Felix Nilam
 * Date: 20/08/2007
 ********************************************************/

global $current_user;
global $timedate;
global $app_list_strings;
global $app_strings;
global $mod_strings;
global $inbound_call_config;
global $sugar_version;

global $theme;
$theme_path = "themes/".$theme."/";
$image_path = $theme_path."images/";
$default_image_path = "themes/default/images/";
require_once ($theme_path.'layout_utils.php');
require_once('UAE/common/utils.php');

$GLOBALS['log']->info("Call Assistant Screen page");
require_once ('include/Sugar_Smarty.php');
require_once('fonality/include/InboundCall/inbound_call_config.php');
require_once('fonality/include/InboundCall/utils.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Prospects/Prospect.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Cases/Case.php');
require_once('modules/Calls/Call.php');
require_once('fonality/include/normalizePhone/normalizePhone.php');

// Handle Ajax search
$search = $_REQUEST['search'];
$ajax = empty($_REQUEST['ajax']) ? 0 : $_REQUEST['ajax'];
$show_attach_note_links = empty($_REQUEST['show_attach_note_links']) ? 0 : $_REQUEST['show_attach_note_links'];

// set the form name
global $call_assistant_form;
$call_assistant_form = 'inboundcall';

$phone = trim($_REQUEST['phone']);
$direction = $_REQUEST['direction'];
if(empty($direction)){
	$direction = "Inbound";
}

if(!empty($search)){
	$phone = $search;
}

if(empty($phone)){
	$unknown_phone = 1;
} else {
	$nphone = normalizePhone($phone);
	if(empty($nphone)){
		$unknown_phone = 1;
	}
}

$prompt_pbx_settings = 0;

// Record the start time
if(!$ajax){
	logFONactivity('callassistant');
	
	// prompt user to setup PBX Settings for the first use
	// get the pbx_settings
	$pbx_setting = new fonuae_PBXSettings();
	$pbx_setting->retrieve_by_string_fields(array('assigned_user_id' => $current_user->id));
	if(empty($pbx_setting->id)){
		$prompt_pbx_settings = 1;
	}
	
	if($sugar_version <= '6.0.0'){
		$dt = gmdate("Y-m-d H:i:s");
	} else {
		$dt = date("Y-m-d H:i:s");
	}
	$call_start_time = $timedate->to_display_date_time($dt);
}

if(!$unknown_phone || ($ajax && !empty($search))){

$db = DBManagerFactory::getInstance();

// Find Contacts with this number
$contact = new Contact();

// if this is ajax search call, search based on name
if($ajax){
	if(!empty($nphone)){
		$contacts = find_records_with_phone($contact, $nphone);
	} else {
 		$contacts = find_records_with_string($contact, $search);
	}
} else {
	$contacts = find_records_with_phone($contact, $nphone);
}

// Find Accounts with this number
$account = new Account();
if($ajax){
	if(!empty($nphone)){
		$accounts = find_records_with_phone($account, $nphone);
	} else {
 		$accounts = find_records_with_string($account, $search);
	}
} else {
	$accounts = find_records_with_phone($account, $nphone);
}

// Find Leads with this number
$lead = new Lead();
if($ajax){
	if(!empty($nphone)){
		$leads = find_records_with_phone($lead, $nphone, $inbound_call_config['lead_status_exclude']);
	} else {
 		$leads = find_records_with_string($lead, $search);
	}
} else {
	$leads = find_records_with_phone($lead, $nphone, $inbound_call_config['lead_status_exclude']);
}

// Find all related Accounts from Contacts and Leads and merge it with accounts
$accounts = array_merge($accounts, find_related_accounts($contacts, "Contacts"));
$accounts = array_merge($accounts, find_related_accounts($leads, "Leads"));
$accounts = array_unique($accounts);

// For ENT version, find related opportunities, cases and/or calls
if($inbound_call_config['version'] == "ENT"){
	// Find any related opportunities excluding specified status
	// Find any related cases excluding specified status
	// Find related contacts
	// Find any planned calls related to the bean and related records for a specified period of time
	
	if(!empty($contacts)){
		if($inbound_call_config['show_related_opportunities'])
			$contacts_opps = find_related_opportunities($contacts, 'Contacts', $inbound_call_config['opportunity_status_exclude']);
		if($inbound_call_config['show_related_cases'])
			$contacts_cases = find_related_cases($contacts, 'Contacts', $inbound_call_config['case_status_exclude']);
		if($inbound_call_config['show_planned_calls']){
			$contacts_calls = find_planned_calls($contacts, 'Contacts', $inbound_call_config['planned_call_period']);
			if(!empty($contacts_opps)){
				$contacts_opps_calls = find_planned_related_calls($contacts_opps, 'Opportunities', $inbound_call_config['planned_call_period']);
			} else {
				$contacts_opps_calls = array();
			}
			if(!empty($contacts_cases)){
				$contacts_cases_calls = find_planned_related_calls($contacts_cases, 'Cases', $inbound_call_config['planned_call_period']);
			} else {
				$contacts_cases_calls = array();
			}
			$contacts_all_calls = array_merge_recursive($contacts_calls, $contacts_opps_calls, $contacts_cases_calls);	
		}
	}
	if(!empty($leads)){
		if($inbound_call_config['show_planned_calls'])
			$leads_calls = find_planned_calls($leads, 'Leads', $inbound_call_config['planned_call_period']);
	}
	if(!empty($accounts)){
		if($inbound_call_config['show_related_opportunities'])
			$accounts_opps = find_related_opportunities($accounts, 'Accounts', $inbound_call_config['opportunity_status_exclude']);
		if($inbound_call_config['show_related_cases'])
			$accounts_cases = find_related_cases($accounts, 'Accounts', $inbound_call_config['case_status_exclude']);
		if($inbound_call_config['show_related_account_contacts'])
			$accounts_contacts = find_related_contacts($accounts);
		if($inbound_call_config['show_planned_calls']){
			$accounts_calls = find_planned_calls($accounts, 'Accounts', $inbound_call_config['planned_call_period']);
			if(!empty($accounts_opps)){
				$accounts_opps_calls = find_planned_related_calls($accounts_opps, 'Opportunities', $inbound_call_config['planned_call_period']);
			} else {
				$accounts_opps_calls = array();
			}
			if(!empty($accounts_cases)){
				$accounts_cases_calls = find_planned_related_calls($accounts_cases, 'Cases', $inbound_call_config['planned_call_period']);
			} else {
				$accounts_cases_calls = array();
			}
			if(!empty($accounts_contacts)){
				$accounts_contacts_calls = find_planned_related_calls($accounts_contacts, 'Contacts', $inbound_call_config['planned_call_period']);
			} else {
				$accounts_contacts_calls = array();
			}
			$accounts_all_calls = array_merge_recursive($accounts_calls, $accounts_opps_calls, $accounts_cases_calls, $accounts_contacts_calls);
		}
	}
}
} // end if unknown

logUAE('callassistant', 
"=================\nphone: $phone, direction: $direction, search: $search, ajax: $ajax, show_attach_note_links: $show_attach_note_links, Normalized phone: $nphone");

// Parse the results
$tpl = new Sugar_Smarty();
$tpl->assign("THEMEPATH", $image_path);
$tpl->assign("DEFAULTPATH", $default_image_path);
$tpl->assign("START_TIME", $call_start_time);
$tpl->assign("DIRECTION", $direction);
$tpl->assign("BACKGROUND_COLOUR", "#ffffff");
$tpl->assign("HIGHLIGHT_COLOUR", "#f6f6f6");
$tpl->assign("SHOW_ATTACH_NOTE_LINKS", $show_attach_note_links);
$tpl->assign("PROMPT_PBX_SETTINGS", $prompt_pbx_settings);
$results = 0;

// Contacts
if(!empty($contacts)){
	parse_template_record($tpl, $contacts, new Contact());
	$results = 1;
}

// Accounts
if(!empty($accounts)){
	parse_template_record($tpl, $accounts, new Account());
	$results = 1;
}
	
// Leads
if(!empty($leads)){
	parse_template_record($tpl, $leads, new Lead());
	$results = 1;
}

// Display the related calls cases, opportunities or contacts
if($inbound_call_config['version'] == "ENT"){
	$all_calls = array();
	if(!empty($contacts_all_calls)){
		// extract call ids
		$c_all_calls = array();
		foreach($contacts_all_calls as $key => $val){
			foreach($val as $call_id){
				if(!in_array($call_id, $all_calls)){
				$all_calls[] = $call_id;
					$c_all_calls[] = $call_id;
			}
		}
		}
		parse_template_record($tpl, $c_all_calls, new Call(), 'CONTACTS_CALLS');		
	}
	if(!empty($contacts_cases)){
		parse_template_record_multi($tpl, $contacts_cases, new aCase(), 'CONTACTS_CASES', 'Contacts');	
	}
	if(!empty($contacts_opps)){
		parse_template_record_multi($tpl, $contacts_opps, new Opportunity(), 'CONTACTS_OPPORTUNITIES', 'Contacts');
	}
	if(!empty($leads_calls)){
		// extract call ids
		$l_all_calls = array();
		foreach($leads_calls as $key => $val){
			foreach($val as $call_id){
				if(!in_array($call_id, $all_calls)){
				$all_calls[] = $call_id;
					$l_all_calls[] = $call_id;
			}
		}
		}
		parse_template_record($tpl, $l_all_calls, new Call(), 'LEADS_CALLS');
	}
	if(!empty($accounts_all_calls)){
		// extract call ids
		$a_all_calls = array();
		foreach($accounts_all_calls as $key => $val){
			foreach($val as $call_id){
				if(!in_array($call_id, $all_calls)){
				$all_calls[] = $call_id;
					$a_all_calls[] = $call_id;
				}
			}
		}
		parse_template_record($tpl, $a_all_calls, new Call(), 'ACCOUNTS_CALLS');
	}
	if(!empty($accounts_opps)){
		parse_template_record_multi($tpl, $accounts_opps, new Opportunity(), 'ACCOUNTS_OPPORTUNITIES');
	}
	if(!empty($accounts_cases)){
		parse_template_record_multi($tpl, $accounts_cases, new aCase(), 'ACCOUNTS_CASES');
	}
	if(!empty($accounts_contacts)){
		parse_template_record_multi($tpl, $accounts_contacts, new Contact(), 'ACCOUNTS_CONTACTS');
	}
	
	$tpl->assign("create_new_call", "1");
}

if($unknown_phone){
	$tpl->assign("UNKNOWN", "1");
	$phone = "Unknown";
}
if(!$results){
	$tpl->assign("NO_RESULTS", "1");
}

if(!$ajax){
if($direction == "Inbound"){
	echo "<h2>Inbound Call from: <nofoncall>".uae_format_number($phone)."</nofoncall></h2><br>";
} else {
	echo "<h2>Outbound Call to: <nofoncall>".uae_format_number($phone)."</nofoncall></h2><br>";
}
}

if($phone != "Unknown"){
	$tpl->assign("PHONE_NO", $phone);
}

if($ajax){
	$tpl->display("modules/Calls/UAECallAssistantAjax.tpl");
} else {
	$tpl->display("modules/Calls/UAECallAssistant.tpl");
}
?>
