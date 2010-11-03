<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class AccountsViewDetail extends ViewDetail {
	function AccountsViewDetail(){
		parent::ViewDetail();
	}
	function display(){
		global $app_list_strings;
		global $mod_strings;
		
		if(!empty($this->bean->id)){
			require_once('custom/si_custom_files/checkRecordValidity.php');
			$checker = new checkRecordValidity();
			$checker->checkValidity($this->bean, 'custom/si_custom_files/accountCheckMeta.php');
			$warningString = '';
			if(!empty($checker->warningArray)){
				$warningString .= "<font color=red><i>Please resolve the following:</i><BR>\n";
				foreach($checker->warningArray as $string){
					$warningString .= "$string<BR>\n";
				}
				$warningString .= "</font><BR>\n";
				echo $warningString;
			}
		}
		
		$js = "\n<script>\n";
		
		// SADEK - BEGIN IT REQUEST 7537 - For the time being, nobody should ever delete this account. Someone deleted this and it broke cloud console
		if($GLOBALS['current_user']->user_name != 'sadek' && $this->bean->id == 'b80d0cc0-1622-eebb-998e-4147933a7b54'){
			$js .= "var delete_buttons = document.getElementsByName('Delete'); for (var i = 0; i < delete_buttons.length; i++){ delete_buttons[i].disabled = true; }\n";
		}
		// SADEK - END IT REQUEST 7537 - For the time being, nobody should ever delete this account. Someone deleted this and it broke cloud console
		
		// Load up all the references to the panels based on the labels
		$d=$this->dv->defs['panels'];
		$panelArray = array();
		foreach($d as $panel_label=>$panel_data) {
			if(isset($mod_strings[strtoupper($panel_label)])){
				$panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
			}
		}

		$this->dv->th->clearCache($this->module, 'DetailView.tpl');
		
		// BEGIN: Determine whether or not we display the DCE fields
		if( !$GLOBALS['current_user']->check_role_membership('DCE Field Access') && $GLOBALS['current_user']->department != 'Customer Support'){
			foreach($this->dv->defs['panels'] as $panel_index => $panel_rows){
				if($panel_index == $panelArray['DCE Information']){
					unset($this->dv->defs['panels'][$panel_index]);
					break;
				}
			}
		}
		// END: Determine whether or not we display the DCE fields		

		// ITR #19685 jbartek -> Determine whether or not we display the customer_msa_not_required_c
		if(!$GLOBALS['current_user']->check_role_membership('Sales Operations') && !$GLOBALS['current_user']->check_role_membership('Sales Operations Opportunity Admin') && !is_admin($GLOBALS['current_user'])) {
			$field_name = 'customer_msa_not_required_c';
			
			foreach($this->dv->defs['panels']['DEFAULT'] AS $key => $set) {			
				foreach($set AS $k => $info) {
					if($info['name'] == $field_name) {
						unset($this->dv->defs['panels']['DEFAULT'][$key][$k]);
					}
				}
			}
			
		}
		// END: ITR #19685

		
		// BEGIN: Don't display sugar network information if the account is not of type customer network/Express
		if($this->bean->account_type != 'network' && $this->bean->account_type != 'Customer-Express'){
			$js .= "document.getElementById('{$panelArray['Sugar Network Information']}').style.display='none';\n";
		}
		// END: Don't display sugar network information if the account is not of type customer network
		
		$js .= "\n</script>\n";
		parent::display();
		echo $js;
	}
}
