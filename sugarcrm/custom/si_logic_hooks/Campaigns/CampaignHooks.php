<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class CampaignHooks  {
	
	function generateCampaignList(&$focus, $event, $arguments) {
		if($event == "before_save"){
			if(empty($focus->fetched_row['id'])){
				if($focus->display_in_leads_dropdown_c){
					global $campaign_build_campaign_list;
					$campaign_build_campaign_list = true;
				}
			}
			else{
				if($focus->fetched_row['name'] != $focus->name || $focus->fetched_row['deleted'] != $focus->deleted || (isset($focus->display_in_leads_dropdown_c) && $focus->fetched_row['display_in_leads_dropdown_c'] != $focus->display_in_leads_dropdown_c)){
					global $campaign_build_campaign_list;
					$campaign_build_campaign_list = true;
				}
			}
		}
		if($event == "after_save"){
			global $campaign_build_campaign_list;
			if(isset($campaign_build_campaign_list) && $campaign_build_campaign_list == true){
				$this->buildCampaignList($focus);
			}
		}
		if($event == "after_delete"){
			$this->buildCampaignList($focus);
		}
	}
	
	function buildCampaignList(&$focus){
		require_once("modules/Campaigns/Campaign.php");
		$seed = new Campaign();
	
		require_once("include/database/PearDatabase.php");
		$db = PearDatabase::getInstance();
	
		// this block prevents the script from failing before the database is populated during installation
		$check_result = $db->query("SHOW TABLES LIKE 'campaigns_cstm'", "Error checking for campaigns_cstm table");
		$check_row = $db->fetchByAssoc($check_result);
		if ($check_row === FALSE) {
			return array();
		}
		// end table check block
	
		$temp_result = array('' => '');

		$query = "SELECT campaigns.id, campaigns.name FROM campaigns ";
		$query .= " LEFT JOIN campaigns_cstm ON campaigns_cstm.id_c = campaigns.id ";
		$query .= " WHERE campaigns_cstm.display_in_leads_dropdown_c = 1 AND campaigns.deleted = 0";

		$query .= " ORDER BY name ASC";

		$result = $db->query($query, TRUE, "Error filling in campaign array: ");

		while ($row = $db->fetchByAssoc($result)) {
			$temp_result[$row['id']] = $row['name'];
		}

		$campaign_array = $temp_result;
		
		write_array_to_file('campaign_list', $campaign_array, 'custom/si_logic_hooks/Campaigns/campaign_list.php', "w");
	}
}	
