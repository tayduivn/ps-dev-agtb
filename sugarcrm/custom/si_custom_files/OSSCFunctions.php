<?php

/* array of items that should not be changed if the */
global $betterSupportArray;
$betterSupportArray = array(
	0 => "legacy_pro",
	1 => "standard",
	2 => "extended",
	3 => "premium",
);

// SADEK: BEGIN OSSC CUSTOMIZATION --- ENTIRE FILE IS CUSTOM
/*
 * ossc (Open Source Support Case) handleSave function
 *
 * This function applies all the necessary logic that must execute in order
 * to apply the ossc implementation for saving Opportunities
 * 
 * $new_focus - the values of the newly saved object
 */
function osscOpportunitiesHandleSave($new_focus){
	// if it's not sugar network, return false straight away so we don't have to hit the DB below when we do the retrieve

	// 2009-03-24 jostrow
	// changed the triggering sales_stage to 'Closed Won' or 'Sales Ops Closed' -- the process has changed in the sales team
	// now sales reps process new Sugar Network orders instead of Sales Ops... meaning they are creating the Opportunities
	// since they cannot set "Sales Ops Closed" as a sales stage, new Support Cases were never being added to the related Account

	// we'll leave the option for 'Sales Ops Closed' as well, just in case Sales Ops processes one of these orders
	if($new_focus->opportunity_type != "Sugar Network" || !in_array($new_focus->sales_stage, array('Closed Won', 'Sales Ops Closed'))) {
		return false;
	}
	
	// get the data that was there before the save
	$old_focus = new Opportunity();
	$old_focus->retrieve($new_focus->id);

	/* Type is "Sugar Network" and status is "Closed Won" and (opp is a new one or subscriptions field is being updated) */
        if(empty($old_focus->id) || ($new_focus->users != $old_focus->users) ||
				    ($new_focus->additional_support_cases_c != $old_focus->additional_support_cases_c)){
		
		global $betterSupportArray;
		
		$related_account = new Account();
		$related_account->retrieve($new_focus->account_id);
		//print_r($related_account); die();
		
		/* If new case, just add the total subscriptions to the related accounts remaining cases */
		if(empty($old_focus->id)){
			
			/* Set the appropriate Support Cases Purchased value */
			if(empty($related_account->support_cases_purchased_c))
				$related_account->support_cases_purchased_c = ($new_focus->users + $new_focus->additional_support_cases_c);
			else
				$related_account->support_cases_purchased_c += ($new_focus->users + $new_focus->additional_support_cases_c);
			
			/* Set the appropriate Remaining Support Cases value */
			if(empty($related_account->remaining_support_cases_c))
				$related_account->remaining_support_cases_c = ($new_focus->users + $new_focus->additional_support_cases_c);
			else
				$related_account->remaining_support_cases_c += ($new_focus->users + $new_focus->additional_support_cases_c);
			
			/* If the related account is not Standard/Premium/Extended/Legacy,
			   automatically change it to Sugar Network */
			if(!in_array($related_account->Support_Service_Level_c, $betterSupportArray))
			{
				$related_account->Support_Service_Level_c = "sugar_network";
			}
			
			$related_account->save();
		}
		/* If subscriptions being updated, take difference between before and after, then add it to the related accounts remaining cases */
		else if($new_focus->users != $old_focus->users || $new_focus->additional_support_cases_c != $old_focus->additional_support_cases_c){

			$add_total = $new_focus->users - $old_focus->users + $new_focus->additional_support_cases_c - $old_focus->additional_support_cases_c;
			
			/* Set the appropriate Support Cases Purchased value */
			if(empty($related_account->support_cases_purchased_c))
				$related_account->support_cases_purchased_c = $add_total;
			else
				$related_account->support_cases_purchased_c += $add_total;
			
			/* Set the appropriate Remaining Support Cases value */
			if(empty($related_account->remaining_support_cases_c))
				$related_account->remaining_support_cases_c = $add_total;
			else
				$related_account->remaining_support_cases_c += $add_total;
			
			/* If the related account is not Standard/Premium/Extended/Legacy,
			   automatically change it to Sugar Network */
			if(!in_array($related_account->Support_Service_Level_c, $betterSupportArray))
			{
				$related_account->Support_Service_Level_c = "sugar_network";
			}
			
			$related_account->save();
		}
        }
}


function osscAccountsHandleSave($new_focus){
	
	// if the account type isn't network, we don't need to change anything
	if($new_focus->account_type != "network")
		return $new_focus;
	
	$old_focus = new Account();
	$old_focus->retrieve($new_focus->id);
	
	// if this is a new account OR the old value was network, we don't need to update anything
	if(empty($old_focus->id) || $old_focus->account_type == "network")
		return $new_focus;
	
	/* if we reach this point, we should be updating the
	     account's remaining_support_cases_c and purchased_support_cases_c */
	$query =
	'select acc.id as account_id, opp.id as opportunity_id,
                opp_c.users as users, opp_c.additional_support_cases_c as additional_users
         from accounts acc left join accounts_cstm acc_c on acc_c.id_c = acc.id
           inner join accounts_opportunities acc_opp on acc_opp.account_id = acc.id
           inner join opportunities opp on acc_opp.opportunity_id = opp.id
           left join opportunities_cstm opp_c on opp_c.id_c = opp.id
         where acc.id=\''.$old_focus->id.'\' and opp_c.opportunity_type=\'Sugar Network\'';
	
	$accountres = mysql_query($query);
	$accountrow = mysql_fetch_assoc($accountres);
	$casequery = 'select count(*) from cases left join cases_cstm on cases.id = cases_cstm.id_c '.
					'where cases.account_id=\''.$accountrow['account_id'].'\' '.
					'  and cases_cstm.request_type_c = \'technical_support\'';
	$caserow = mysql_fetch_row(mysql_query($casequery));
	$casenum = $caserow[0];
	
	$new_focus->remaining_support_cases_c = 0;
	$new_focus->support_cases_purchased_c = 0;
	do{
		$new_focus->remaining_support_cases_c += $accountrow['users'] + $accountrow['additional_users'];
		$new_focus->support_cases_purchased_c += $accountrow['users'] + $accountrow['additional_users'];
	}while($accountrow = mysql_fetch_assoc($accountres));
	
	$new_focus->remaining_support_cases_c -= $casenum;
	
	return $new_focus;
}

function osscCasesHandleSave($new_focus){

        $related_account = new Account();
        $related_account->retrieve($new_focus->account_id);

        // if it's not sugar network, return false straight away so we don't have to hit the DB below when we do the retrieve
        if($related_account->Support_Service_Level_c != "sugar_network") 
//	if($related_account->Support_Service_Level_c != "sugar_network" && $related_account->Support_Service_Level_c != "Sugar Express") // NASSI
                return false;

        // get the data that was there before the save
        $old_focus = new aCase();
        $old_focus->retrieve($new_focus->id);
	
	if($new_focus->request_type_c != "technical_support"){
		// someone changed the case from a technical support case to a non tech support case, so we give back a support case
		if(!empty($old_focus->id) && $old_focus->request_type_c == 'technical_support'){
			$related_account->remaining_support_cases_c += 1;
			$related_account->save();
		}
		
		return true;
	}

        /* Only execute of this is a NEW case, and not someone editing a previously existing case */
        if(empty($old_focus->id)){

                /* if the related accounts support cases purchased are not empty
                   (although, they should never be empty) */
                if(!empty($related_account->remaining_support_cases_c))
                        $related_account->remaining_support_cases_c -= 1;

                $related_account->save();
        }
        else{

                /* If a previously existing case and the prior account was the Generic Support Account
                   then we must decrement the account case count. */
                if($old_focus->account_name == "Generic Support Account")
                        $related_account->remaining_support_cases_c -= 1;
		
		// someone changed the case from a non technical support case to a tech support case, so we take away a support case
		if($old_focus->request_type_c != 'technical_support' && $new_focus->request_type_c == 'technical_support'){
			$related_account->remaining_support_cases_c -= 1;
		}
		
                $related_account->save();
        }
}


// SADEK: END OSSC CUSTOMIZATION

?>
