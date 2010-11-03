<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class LeadAccountsHooks  {
	
	function leadCountryRegionMap(&$focus, $event, $arguments) {
		if($event == "before_save"){
			require('custom/si_custom_files/meta/countryRegionMap.php');
			if(!empty($focus->billing_address_country) && array_key_exists($focus->billing_address_country, $countryRegionMap)){
				$focus->region_c = $countryRegionMap[$focus->billing_address_country];
			}
		}
	}
	
	function setLeadPassDate(&$focus, $event, $arguments) {
	    if ($event == "before_save") {
			if (isset($focus->lead_pass_c) && 'on' == $focus->lead_pass_c
			    && empty($focus->lead_pass_date_c)
			    && (empty($focus->fetched_row) || $focus->fetched_row['lead_pass_c'] != '1')) {
				require_once('include/TimeDate.php');
				$timedate = new TimeDate();
				$focus->lead_pass_date_c = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s"));
			}
	    }
	}
	
	// This function is for IT REQUEST 8500 - The third part of the request
	function updateLeadContactAssignedFields(&$focus, $event, $arguments){
		if($event == "before_save"){
			if(!empty($focus->partner_assigned_to_c)){
				require_once('modules/LeadContacts/LeadContact.php');
				$query = "select id from leadcontacts inner join leadcontacts_cstm on leadcontacts.id = leadcontacts_cstm.id_c where leadaccount_id = '{$focus->id}' and leadcontacts_cstm.partner_assigned_to_c != '{$focus->partner_assigned_to_c}' and deleted = 0";
				$res = $GLOBALS['db']->query($query);
				
				$update_query = "update leadcontacts_cstm inner join leadcontacts on leadcontacts_cstm.id_c = leadcontacts.id set partner_assigned_to_c = '{$focus->partner_assigned_to_c}' where leadaccount_id = '{$focus->id}'";
				$GLOBALS['db']->query($update_query);
			}
		}
	}
}
