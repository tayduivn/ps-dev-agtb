<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class TouchpointHooks  {
	function touchpointCountryRegionMap(&$focus, $event, $arguments) {
		if($event == "before_save"){
			require('custom/si_custom_files/meta/countryRegionMap.php');
			if(!empty($focus->primary_address_country) && array_key_exists($focus->primary_address_country, $countryRegionMap)){
				$focus->region_c = $countryRegionMap[$focus->primary_address_country];
			}
		}
	}
	
	function setLeadGroupFromValues(&$focus, $event, $arguments){
		if($event == "before_save"){
			require_once('custom/si_custom_files/custom_functions.php');
            /*
            ** @author: jwhitcraft
            ** SUGARINTERNAL CUSTOMIZATION
            ** ITRequest 15142
            ** Description: Default the annual revenue to Unknown if it's empty or not set so it doesn't cause a notice
            */
            if(!isset($focus->annual_revenue) || empty($focus->annual_revenue)) {
                $focus->annual_revenue = "Unknown";
            }
            // end SI Customization
			$focus->lead_group_c = getLeadGroupFromValues($focus->potential_users_c, $focus->annual_revenue, $focus->lead_group_c, $focus->assigned_user_id);
		}	
	}




	//** BEGIN CUSTOMIZATION EDDY IT TIX 12706
	function rollupTouchpointData_fp(&$focus, $event, $arguments){
	$acc = false;
	$leadacc = false;
	$leadcon = false;
	$con = false;
	$int = false;
	$xclude = array();

	//get new_leadaccounts_id to retrieve leadaccounts
	if(isset($focus->new_leadaccount_id)  && !empty($focus->new_leadaccount_id)){
		$leadacc = $this->retrieve_bean_fp('LeadAccount', 'modules/LeadAccounts/LeadAccount.php', $focus->new_leadaccount_id);
	}else{
		$xclude[] = 'LeadAccount';
	}
	
	//get accounts_id from new_leadaccounts_id to retrieve accounts
	if($leadacc!=false && isset($leadacc->account_id)  && !empty($leadacc->account_id))
	{
		$acc = $this->retrieve_bean_fp('Account', 'modules/Accounts/Account.php', $leadacc->account_id);
	}else{
		$xclude[] = 'Account';	
	}
	
	//get new_leadcontact_id to retrieve leadcontacts
	if(isset($focus->new_leadcontact_id)  && !empty($focus->new_leadcontact_id))
	{
		$leadcon = $this->retrieve_bean_fp('LeadContact', 'modules/LeadContacts/LeadContact.php', $focus->new_leadcontact_id);
	}else{
		$xclude[] = 'LeadContact';
	}
	
	//get contact_id from leadcontact to retrieve contacts
	if($leadcon!=false && isset($leadcon->contact_id)  && !empty($leadcon->contact_id))
	{
		$con = $this->retrieve_bean_fp('Contact', 'modules/Contacts/Contact.php', $leadcon->contact_id);
	}else{
		$xclude[] = 'Contact';
	}

        //always exclude Interaction bean from updates
        $xclude[] = 'Interaction';
	
/*	
	//get interaction if one exists, if not, then  from leadcontact to retrieve contacts
		$int_id = false;
		//query for the interaction
		$query = "select id from interactions where source_id = '$focus->id' and source_module = 'Touchpoints' ";
	
		//execute query and process results
		$result =$GLOBALS['db']->query($query);
	
		while ($row = $GLOBALS['db']->fetchByAssoc($result)){
			$int_id = $row['id'] ;
		}
		
		if($int_id !=false){
			$int = $this->retrieve_bean_fp('Interaction', 'modules/Interactions/Interaction.php', $int_id);
		}else{
			$xclude[] = 'Interaction';
		}
	
*/
	
	// iterate through mapping to update beans
	$acc_dirty = false;
	$leadacc_dirty = false;
	$leadcon_dirty = false;
	$con_dirty = false;
	$int_dirty = false;
	
	//include mapping and process fields
	require('custom/si_custom_files/meta/touchpointsRollupMap.php');
	foreach($touchpointsRollupMap as $fieldkey=>$fieldmap){
			if(!empty($fieldmap)){
				//for each field in mappting
				foreach($fieldmap as $bean_type=>$field){//_pp($field);
					//skip if the bean in field mapping is in exclude array
					if(in_array($bean_type,$xclude) || empty($field)) continue;
					//if mapping exists for this bean type and field, and the bean is empty, 
					// or if the field is score field, then copy value from touchpoint
					if($leadacc != false && $bean_type == 'LeadAccount'&& (($fieldkey == 'score')
					||(isset($leadacc->$field) && isset($focus->$fieldkey) && !empty($focus->$fieldkey) && empty($leadacc->$field))) && ($leadacc->$field !=$focus->$fieldkey)) {
						$leadacc->$field = $focus->$fieldkey;
						$leadacc_dirty = true;
					}
					else if($acc!=false && $bean_type == 'Account' &&  (($fieldkey =='score' )
                                        ||(isset($acc->$field) && isset($focus->$fieldkey) && !empty($focus->$fieldkey) && empty($acc->$field))) && ($acc->$field !=$focus->$fieldkey)) {
						$acc->$field = $focus->$fieldkey;
						$acc_dirty = true;
					}
					else if($leadcon !=false && $bean_type == 'LeadContact' && (($fieldkey =='score')
                                        ||(isset($leadcon->$field) && isset($focus->$fieldkey) && !empty($focus->$fieldkey) && empty($leadcon->$field))) && ($leadcon->$field !=$focus->$fieldkey)) {
						$leadcon->$field = $focus->$fieldkey;
						$leadcon_dirty = true;
					}
					else if($con != false && $bean_type == 'Contact' && (($fieldkey =='score')
                                        ||(isset($con->$field) && isset($focus->$fieldkey) && !empty($focus->$fieldkey) && empty($con->$field))) && ($con->$field !=$focus->$fieldkey)) {
					$con->$field = $focus->$fieldkey;
						$con_dirty = true;
					}
/*					else if($bean_type == 'Interaction' &&(($field =='score' ||$field =='score_c')
                                        ||(isset($int->$field) && isset($focus->$field) && !empty($focus->$field) && empty($int->$field))) && ($int->$field !=$focus->$field)) {
						$con->$field = $focus->$field;
						$int_dirty = true;
					}*/
				}
			}
	}
	//save beans if they have been dirtied
	if($acc_dirty) $acc->save();
	if($leadacc_dirty) $leadacc->save();
	if($leadcon_dirty) $leadcon->save();
	if($con_dirty) $con->save();// note that this save will update max score on related opportunities
//	if($int_dirty) $int->save();
	
}

//this function is to be used by rollupTouchpointData_fp() to retrieve a new bean instance
	function retrieve_bean_fp($bean_type='', $bean_file='', $bean_id=''){

		//if any of the parameters are invalid then return false
		if(empty($bean_type) || empty($bean_file) || !is_file($bean_file)) return false;
		require_once($bean_file);
	
		//create new bean
		$bean = new $bean_type();
	
		//return retrieved bean
		return $bean->retrieve($bean_id);
	
	}

//** END CUSTOMIZATION EDDY IT TIX 12706
}
