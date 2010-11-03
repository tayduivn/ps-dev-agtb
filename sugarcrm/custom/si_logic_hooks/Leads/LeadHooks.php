<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class LeadHooks  {
	function leadCountryRegionMap(&$focus, $event, $arguments) {
		if($event == "before_save"){
			require('custom/si_custom_files/meta/countryRegionMap.php');
			if(!empty($focus->primary_address_country) && array_key_exists($focus->primary_address_country, $countryRegionMap)){
				$focus->region_c = $countryRegionMap[$focus->primary_address_country];
			}
		}
	}
	function setLeadPassDate(&$focus, $event, $arguments) {
	    if ($event == "before_save") {
		if (isset($focus->lead_pass_c) && ($focus->lead_pass_c == 'on' || $focus->lead_pass_c == '1')
		    && empty($focus->lead_pass_date_c)
		    && (empty($focus->fetched_row) || $focus->fetched_row['lead_pass_c'] != '1')) {
			require_once('include/TimeDate.php');
			$timedate = new TimeDate();
		    $focus->lead_pass_date_c = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s"));
			
			// IT REQUEST 7891 - We also have to set the Lead Pass Department for the Lead Pass Report
		    $focus->lead_pass_department_c = $GLOBALS['current_user']->department;
		    //$focus->lead_pass_date_c = gmdate('Y-m-d H:i:s', gmmktime(gmdate("H") + $timedate->get_hour_offset()));
		}
	    }
	}


	// This function is for IT REQUEST 8500 - The third part of the request
	function updateLeadContactAssignedFields(&$focus, $event, $arguments){
		if($event == "before_save"){
			if(!empty($focus->leadaccount_id)){
				require_once('modules/LeadAccounts/LeadAccount.php');
				$la = new LeadAccount();
				$la->retrieve($focus->leadaccount_id);
				if(!empty($la->partner_assigned_to_c)){
					$focus->partner_assigned_to_c = $la->partner_assigned_to_c;
				}
			}
		}
	}
}
