<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {fonality_phone} function plugin
 *
 * Type:     function<br>
 * Name:     fonality_phone<br>
 * Purpose:  translates a phone into click to dial url
 * 
 * @author Felix Nilam 23/01/2008
 * @param array
 * @param Smarty
 */
function smarty_function_fonality_phone($params, &$smarty)
{
	if (!isset($params['value'])){
		$smarty->trigger_error("fonality_phone: missing 'value' parameter");
		return '';
	}
	
	require_once('fonality/include/normalizePhone/normalizePhone.php');
	require('fonality/include/normalizePhone/default_dial_code.php');
	require_once('fonality/include/FONcall/FONcall.inc.php');
	
	global $system_config;
	global $current_user;
	global $sugar_config;
	global $app_list_strings;
	global $dial_popup;
	if($dial_popup){
		$dial_popup = 1;
	} else {
		$dial_popup = 0;
	}
	
	$phone = $params['value'];
	$parent_type = $params['this_module'];
	$parent_id = $params['this_id'];
	$contact_id = $params['contact_id'];
	
	// if contact_id is a Lead or Account, set the parent type accordingly
	require_once('modules/Leads/Lead.php');
	require_once('modules/Accounts/Account.php');
	$testLead = new Lead();
	$testLead->retrieve($contact_id);
	$testAcct = new Account();
	$testAcct->retrieve($contact_id);
	if(!empty($testLead->id)){
		$parent_type = "Leads";
		$parent_id = $contact_id;
		$contact_id = '';
	} else if(!empty($testAcct->id)){
		$parent_type = "Accounts";
		$parent_id = $contact_id;
		$contact_id = '';
	}
	
	if($nphone = normalizePhone($phone, $current_user)){
		$replace_phone_string = "<nofoncall>".$phone."</nofoncall>";
		
		$create_call_url = $sugar_config['site_url']."/uae_create_call_on_dial.php?phone=".urlencode($phone)."&parent_type=".$parent_type."&parent_id=".$parent_id;
		
		if($system_config->settings['system_tapidial_on'] == '1'){
			$replace_phone_string .= ' <a href="javascript:void(1)" onclick="javascript:ccall_number(\''.$phone.'\',\''.$parent_type.'\',\''.$parent_id.'\',\''.$contact_id.'\',\''.$_REQUEST['action'].'\',\''.$dial_popup.'\');"><img title="Call using the Fonality phone system" border="0" src="fonality/include/images/dial.jpg" align="top"></a>';
		}
		if($system_config->settings['system_skypeout_on'] == '1'){
			if($system_config->settings['system_call_assistant_on_dial'] == '1'){
				$replace_phone_string .=' <a onClick="window.open(\'UAECallAssistant.php?action=UAECallAssistant&opt=1&direction=Outbound&phone='.$phone.'\')" href="callto://'.$nphone.'"><img title="Call Through Skype" border="0" src="fonality/include/images/skype.jpg" align="top"></a> ';
			} else if($system_config->settings['system_create_call_on_dial'] == '1'){
				$replace_phone_string .=' <a onClick="window.open(\''.$create_call_url.'\')" href="callto://'.$nphone.'"><img title="Call Through Skype" border="0" src="fonality/include/images/skype.jpg" align="top"></a> ';
			} else {
				$replace_phone_string .=' <a href="callto://' . $nphone. '"><img title="Call Through Skype" border="0" src="fonality/include/images/skype.jpg" align="top"></a> ';
			}
		}
		return $replace_phone_string;
	}
	
	return $phone;
}
?>
