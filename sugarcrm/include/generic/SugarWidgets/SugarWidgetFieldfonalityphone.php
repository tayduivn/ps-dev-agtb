<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/generic/SugarWidgets/SugarWidgetFieldvarchar.php');

class SugarWidgetFieldFonalityphone extends SugarWidgetFieldVarchar
{
	function displayList(&$layout_def)
	{
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
		
		$module = $layout_def['module'];
		$record = $layout_def['fields']['ID'];
		$name = $layout_def['name'];
		
		$phone= $layout_def['fields'][strtoupper($name)];
		$parent_type = $_REQUEST['module'];
		$parent_id = $_REQUEST['record'];
		if($module == 'Contacts'){
			$contact_id = $record;
		} else {
			$contact_id = '';
		}
		if($module == 'Leads'){
			$parent_type = 'Leads';
			$parent_id = $record;
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
	
		return $this->displayListPlain($layout_def);
	}
}
?>
