<?php

function spscOpportunitiesHandleSave($new_focus){
	
	if($new_focus->opportunity_type != 'Sugar Express') {
		return false;
	}
	$related_account = new Account();
        $related_account->retrieve($new_focus->account_id);

	$old_focus = new Opportunity();
       	$old_focus->retrieve($new_focus->id);

	if($new_focus->opportunity_type == 'Sugar Express' 
		&& ($new_focus->Term_c == 'Annual' || $new_focus->Term_c == 'Remainder of Term')
		&& ($new_focus->Revenue_Type_c == 'New' || $new_focus->Revenue_Type_c == 'Renewal' || $new_focus->Revenue_Type_c == 'Additional')
		&& $related_account->account_type == 'Customer-Express'
		&& ($related_account->Support_Service_Level_c == 'Sugar Express' || $related_account->Support_Service_Level_c == 'Sugar Network')
	) {	
		if(empty($new_focus->additional_support_cases_c) && ($old_focus->opportunity_type != 'Sugar Express')) {
                	$support_cases = 3;
			$new_focus->additional_support_cases_c = $support_cases;
        	}

		if(!empty($new_focus->additional_support_cases_c)) {
			if(empty($related_account->support_cases_purchased_c))
                		$related_account->support_cases_purchased_c = $new_focus->additional_support_cases_c;
			else {
				if(($related_account->support_cases_purchased_c != $new_focus->additional_support_cases_c)
                        		&& ($new_focus->additional_support_cases_c != $old_focus->additional_support_cases_c)
				) {
					$related_account->support_cases_purchased_c += $new_focus->additional_support_cases_c;
				}
			}
			if(empty($related_account->remaining_support_cases_c))
                       		$related_account->remaining_support_cases_c = $new_focus->additional_support_cases_c;
			else {
				if(($related_account->support_cases_purchased_c != $new_focus->additional_support_cases_c)
                                        && ($new_focus->additional_support_cases_c != $old_focus->additional_support_cases_c)
                                ) {
					$related_account->remaining_support_cases_c += $new_focus->additional_support_cases_c;
				}
			}
			$related_account->save();
		}
	}
}	


function spscAccountsHandleSave($new_focus){

	$old_focus = new Account();
        $old_focus->retrieve($new_focus->id);
	
	if(!empty($old_focus->id) && $old_focus->account_type != 'Customer-Express' && $new_focus->account_type == 'Customer-Express') {
	
		$query = 
		'SELECT opportunities_cstm.additional_support_cases_c 
		FROM opportunities
		INNER JOIN  accounts_opportunities 
		ON opportunities.id=accounts_opportunities.opportunity_id 
		AND accounts_opportunities.deleted=0
		INNER JOIN  accounts 
		ON accounts.id=accounts_opportunities.account_id 
		AND accounts.deleted=0
		LEFT JOIN opportunities_cstm opportunities_cstm ON opportunities.id = opportunities_cstm.id_c
		WHERE ((( opportunities_cstm.additional_support_cases_c IS NOT NULL AND opportunities_cstm.additional_support_cases_c<>\'\' )) 
		AND (accounts.id=\''.$old_focus->id.'\') 
		AND (opportunities_cstm.opportunity_type = \'Sugar Express\')) 
		AND  opportunities.deleted=0';

		if($old_focus->account_type != 'network') {
			$new_focus->remaining_support_cases_c = 0;
	        	$new_focus->support_cases_purchased_c = 0;
		}
		else {
			$new_focus->remaining_support_cases_c = $old_focus->remaining_support_cases_c;
                        $new_focus->support_cases_purchased_c = $old_focus->support_cases_purchased_c;
		}
		$accountres = mysql_query($query);	
		while($accountrow = mysql_fetch_assoc($accountres)) {
			$new_focus->remaining_support_cases_c += $accountrow['additional_support_cases_c'];
			$new_focus->support_cases_purchased_c += $accountrow['additional_support_cases_c'];
		}
	}
}
	
	
function spscCasesHandleSave($new_focus){

        $related_account = new Account();
        $related_account->retrieve($new_focus->account_id);

	//ONLY IF Account support service level is CE OnDemand
	if($related_account->Support_Service_Level_c == 'Sugar Express') {
		// get the data that was there before the save
        	$old_focus = new aCase();
        	$old_focus->retrieve($new_focus->id);
		
		//if an existing case
		if(!empty($old_focus->id)) {
			//if old request type == technical support and new request type is not technical support
                	//increment remaining support cases by 1
			if($old_focus->request_type_c == "technical_support" && $new_focus->request_type_c != "technical_support") {
				$related_account->remaining_support_cases_c += 1;
				$related_account->save();
			}
			// someone changed the case from a non technical support case to a tech support case, so we take away a support case
                	if($old_focus->request_type_c != 'technical_support' && $new_focus->request_type_c == 'technical_support'){
                        	$related_account->remaining_support_cases_c -= 1;
                		$related_account->save();
			}
		}

		//if it is a new case and request type = technical_support
		if(empty($old_focus->id) && $new_focus->request_type_c == 'technical_support') {
			/* if the related accounts support cases purchased are not empty
                   (although, they should never be empty) */
                	if(!empty($related_account->remaining_support_cases_c)) {
                        	syslog(LOG_DEBUG, "dszcz before: {$related_account->remaining_support_cases_c}");
				$related_account->remaining_support_cases_c -= 1;
				syslog(LOG_DEBUG, "dszcz after: {$related_account->remaining_support_cases_c}");
                		$related_account->save();
			}
		}
	}
}

?>
