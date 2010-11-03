<?php

// DON'T FORGET TO CREATE A NEW LOGIC HOOK THAT WILL UPDATE THE DATE_MODIFIED ON THE CASE WHEN A NOTE IS CREATED FROM SUGAR INTERNAL

require_once('custom/si_custom_files/custom_functions.php');

function supportUserLastCreatedMultiplier($user_id){
	if(empty($user_id)){
		return 0;
	}
	
	if(!isset($_SESSION['si_note_created_user_id_map'])){
		$support_id_array[] = array();
		$support_id_query = "select id from users where department = 'Customer Support' and status = 'Active' and deleted = 0";
		$res = $GLOBALS['db']->query($support_id_query);
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$support_id_array[] = $row['id'];
		}
		$_SESSION['si_note_created_user_id_map']['support'] = $support_id_array;
		$_SESSION['si_note_created_user_id_map']['portal'] = array('1d72284d-6496-c5cb-4312-47433f21629b'); // currently case_portal2 only
	}
	
	if(in_array($user_id, $_SESSION['si_note_created_user_id_map']['portal'])){
		return 1;
	}
	else if(in_array($user_id, $_SESSION['si_note_created_user_id_map']['support'])){
		return -1;
	}
	else{
		return 0;
	}
}

/***
 * function siCaseScore
 * param $case_ids string array - an array of all the case ids to score. if empty, it will score all open cases
 * param $action string - if update, update the case score, if return, then return an array with the scores without updating
 * return $caseScoreArray - return an array with all the case scores
 */
function siCaseScore($case_ids = array(), $action = 'update', $process_messages = false){
	$caseScoreArray = array();
	
	// Added this check to support passing in a string of the guid
	if(!is_array($case_ids)){
		$case_ids = array($case_ids);
	}
	
	// BEGIN multipliers - the global multipliers stored here for easy access to modify them
	$constants['opp_renew']['61_to_90'] = 1.25;
	$constants['opp_renew']['31_to_60'] = 1.50;
	$constants['opp_renew']['0_to_30'] = 1.75;
	$constants['case_priority_level']['P3'] = 1;
	$constants['case_priority_level']['P2'] = 2;
	$constants['case_priority_level']['P1'] = 3;
	$constants['case_support_level']['Sugar Express'] = 0.5; // NASSI
	$constants['case_support_level']['network'] = 0.5;
	$constants['case_support_level']['standard'] = 1;
	$constants['case_support_level']['extended'] = 2;
	$constants['case_support_level']['premium'] = 3;
	$constants['case_support_level']['partner'] = 1.5;
	$constants['account_type']['pro'] = 1;
	$constants['account_type']['ent'] = 2;
	$constants['account_partner_type']['bronze'] = 1.5;
	$constants['account_partner_type']['silver'] = 2;
	$constants['account_partner_type']['gold'] = 3;
	$constants['account_partner_type']['oem'] = 3;
	$constants['case_age_multiplier'] = 200;
	$constants['note_day_multiplier'] = 100;
	$constants['subscriptions_perpetual'] = 1000;
	$constants['days_since_last_note'] = 100;
	$constants['account_open_cases'] = 100;
	$constants['cases_in_x_days'] = 100;
	$constants['related_cases_days_threshold'] = 30;
	$constants['closed_defects'] = 100;
	$constants['per_survey'] = 25;
	// END multipliers - the global multipliers stored here for easy access to modify them
	
	$caseOpenStatuses = getSugarInternalOpenCaseStatuses('array'); 
	if(empty($case_ids)){
		$open_cases_query = "select id\nfrom cases\n".
							"where status in ('".implode("', '", $caseOpenStatuses)."') and deleted = 0";
		$res = $GLOBALS['db']->query($open_cases_query);
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$case_ids[] = $row['id'];
		}
	}
	
	foreach($case_ids as $case_id){
		$messages = array();
		// BEGIN cases_accounts: Gather Case and Account data
		$case_query = "select cases_cstm.priority_level 'priority_level', ".
					  "       cases_cstm.Support_Service_Level_c 'Support_Service_Level_c', ".
					  "       DATEDIFF(NOW(), cases.date_entered) 'days_since_created', ".
					  "       cases.account_id 'account_id', ".
					  "       accounts.account_type 'account_type', ".
					  "       accounts_cstm.Partner_Type_c 'Partner_Type_c'\n".
					  "from cases inner join cases_cstm on cases.id = cases_cstm.id_c\n".
					  "           inner join accounts on cases.account_id = accounts.id and accounts.deleted = 0\n".
					  "           inner join accounts_cstm on accounts.id = accounts_cstm.id_c\n".
					  "where cases.id = '{$case_id}' and cases.deleted = 0";
		
		$res = $GLOBALS['db']->query($case_query);
		$case_account_data = $GLOBALS['db']->fetchByAssoc($res);
		
		// The information could not be retrieved from the database. Skip this case.
		if(!$case_account_data){
			// echo "Could not find case / account data<BR>\n";
			continue;
		}
		
		$components['case_age'] = 0;
		$messages['case_age'] = "The Case was created today, so there were no additions to the score";
		if($case_account_data['days_since_created'] > 0){
			$components['case_age'] = $case_account_data['days_since_created'] * $constants['case_age_multiplier'];
			$messages['case_age'] = "The Case was created {$case_account_data['days_since_created']} days ago, ".
									"with age multiplier of {$constants['case_age_multiplier']}, resulting in {$components['case_age']} points";
		}
		
		$components['case_priority_level'] = 1;
		switch($case_account_data['priority_level']){
			case 'P3 General Issue':
				$components['case_priority_level'] = $constants['case_priority_level']['P3'];
				break;
			case 'P2 System Impaired':
				$components['case_priority_level'] = $constants['case_priority_level']['P2'];
				break;
			case 'P1 System Down':
				$components['case_priority_level'] = $constants['case_priority_level']['P1'];
				break;
			default:
				$messages['case_priority_level'] = "The Priority Level was not in the list, leaving the multiplier at {$components['case_priority_level']}";
				break;
		}
		if(!isset($messages['case_priority_level'])){
			$messages['case_priority_level'] = "The Priority Level is {$case_account_data['priority_level']}, making the multiplier {$components['case_priority_level']}";
		}
		
		$components['case_support_level'] = 1;
		switch($case_account_data['Support_Service_Level_c']){
			case 'Sugar Express':  // NASSI
				$components['case_support_level'] = $constants['case_support_level']['Sugar Express'];  // NASSI
				break;  // NASSI
			case 'Sugar Network':
				$components['case_support_level'] = $constants['case_support_level']['network'];
				break;
			case 'Standard Support':
				$components['case_support_level'] = $constants['case_support_level']['standard'];
				break;
			case 'Extended Support':
				$components['case_support_level'] = $constants['case_support_level']['extended'];
				break;
			case 'Premium Support':
				$components['case_support_level'] = $constants['case_support_level']['premium'];
				break;
			case 'SugarCRM Partner':
				$components['case_support_level'] = $constants['case_support_level']['partner'];
				break;
			default:
				$messages['case_support_level'] = "The Support Service Level was not in the list, leaving the multiplier at {$components['case_support_level']}";
				break;
		}
		if(!isset($messages['case_support_level'])){
			$messages['case_support_level'] = "The Support Service Level is {$case_account_data['Support_Service_Level_c']}, making the multiplier {$components['case_support_level']}";
		}
		
		$components['account_type'] = 1;
		switch($case_account_data['account_type']){
			case 'Customer':
			case 'Customer-Pro-Webex':
				$components['account_type'] = $constants['account_type']['pro'];
				break;
			case 'Customer-Ent':
				$components['account_type'] = $constants['account_type']['ent'];
				break;
			default:
				$messages['account_type'] = "The Account Type was not in the list, leaving the multiplier at {$components['account_type']}";
				break;
		}
		if(!isset($messages['account_type'])){
			$messages['account_type'] = "The Account Type is {$case_account_data['account_type']}, making the multiplier {$components['account_type']}";
		}
		
		if(strpos($case_account_data['account_type'], 'Partner') === 0){
			unset($messages['account_type']);
			switch($case_account_data['Partner_Type_c']){
				case 'Bronze':
					$components['account_type'] = $constants['account_partner_type']['bronze'];
					break;
				case 'Silver':
					$components['account_type'] = $constants['account_partner_type']['silver'];
					break;
				case 'Gold':
					$components['account_type'] = $constants['account_partner_type']['gold'];
					break;
				case 'OEM':
					$components['account_type'] = $constants['account_partner_type']['oem'];
					break;
				default:
					$messages['account_type'] = "The Account Type was a Partner item, but none of the Partner Type dropdown values matched, leaving the multiplier at {$components['account_type']}";
					break;
			}
			if(!isset($messages['account_type'])){
				$messages['account_type'] = "The Account Type was a Partner item, and the Partner Type field is {$case_account_data['Partner_Type_c']}, making the multiplier {$components['account_type']}";
			}
		}
		// END cases_accounts: Gather Case and Account data
		
		// BEGIN check_related_note_poster: Get last poster information and time since to increment or decrement score
		$note_information = array();
		$note_query =   "select notes.id 'id', DATEDIFF(NOW(), notes.date_entered) 'days_since_entered',\n".
						"       notes.created_by 'created_by'\n".
						"from notes\n".
						"where notes.parent_id = '{$case_id}'\n".
						"  and notes.parent_type = 'Cases'\n".
						"  and notes.deleted = 0\n".
						"order by notes.date_modified desc\n".
						"limit 1";
		
		$res = $GLOBALS['db']->query($note_query);
		$note_data = $GLOBALS['db']->fetchByAssoc($res);
		// This is the multiplier for the most recent note
		$components['note_sum'] = 0;
		$messages['note_sum'] = "There were no notes associated with the Case, so there was no value added or subtracted";
		if($note_data){
			$support_user_sign = supportUserLastCreatedMultiplier($note_data['created_by']);
			if($support_user_sign != 0 && $note_data['days_since_entered'] != 0){
				$components['note_sum'] = $support_user_sign * $note_data['days_since_entered'] * $constants['note_day_multiplier'];
				$messages['note_sum'] = "The last note was not created by ".($support_user_sign > 0 ? "a support rep" : "a customer")." and has existed for {$note_data['days_since_entered']} with a multiplier of {$constants['note_day_multiplier']}, making the note modifier {$components['note_sum']}";
			}
			else{
				if($support_user_sign == 0){
					$messages['note_sum'] = "The last note was not created by a support rep or a customer, so no value will be added or subtracted";
				}
				else{
					$messages['note_sum'] = "The last note was created today, so no value will be added or subtracted";
				}
			}
		}
		// END check_related_note_poster: Get last poster information and time since to increment or decrement score
		
		// BEGIN related_case_details: Gather details about the related cases for processing
		$related_case_details_query =
					"select cases.id 'id',\n".
					"       cases.status 'status',\n".
					"       DATEDIFF(NOW(), cases.date_entered) 'days_since_opened'\n". 
					"from cases inner join accounts on cases.account_id = accounts.id\n".
					"           inner join cases_cstm on cases.id = cases_cstm.id_c\n".
					"where accounts.id = '{$case_account_data['account_id']}'\n".
					"  and cases.id != '{$case_id}'\n".
					"  and cases.deleted = 0";
		
		$res = $GLOBALS['db']->query($related_case_details_query);
		$case_ids_for_surveys = array($case_id);
		$number_of_defects = 0;
		$number_of_open_cases = 0;
		$recent_activity_count = 0;
		while($related_case_data = $GLOBALS['db']->fetchByAssoc($res)){
			$case_ids_for_surveys[] = $related_case_data['id'];
			if($related_case_data['status'] == 'Closed Defect'){ 
				$number_of_defects++;
			}
			if($related_case_data['days_since_opened'] < $constants['related_cases_days_threshold']){ 
				$recent_activity_count++;
			}
			if(in_array($related_case_data['status'], $caseOpenStatuses)){ 
				$number_of_open_cases++;
			}
		}

		$components['closed_defects'] = $number_of_defects * $constants['closed_defects'];
		$messages['closed_defects'] = "There were {$number_of_defects} Cases with status Closed Defect associated with the account, with a multiplier of {$constants['closed_defects']}, resulting in a value of {$components['closed_defects']}";

		$components['cases_in_x_days'] = $recent_activity_count * $constants['cases_in_x_days'];
		$messages['cases_in_x_days'] = "There were {$recent_activity_count} Cases opened in the last {$constants['related_cases_days_threshold']} days, with a multiplier of {$constants['cases_in_x_days']}, resulting in a value of {$components['cases_in_x_days']}";

		$components['account_open_cases'] = $number_of_open_cases * $constants['account_open_cases'];
		$messages['account_open_cases'] = "There are {$number_of_open_cases} Cases currently open for this account, with a multiplier of {$constants['account_open_cases']}, resulting in a value of {$components['account_open_cases']}";
		// END related_case_details: Gather details about the related cases for processing
		
		// BEGIN subscriptions: Gather Subscription Data
		$subscriptions_query = "select subscriptions_distgroups.quantity 'quantity', subscriptions.perpetual 'perpetual'\n".
							   "from subscriptions inner join subscriptions_distgroups on subscriptions.id = subscriptions_distgroups.subscription_id\n".
							   "where subscriptions.account_id = '{$case_account_data['account_id']}' ".
							   " and subscriptions_distgroups.deleted = 0".
							   " and subscriptions.status = 'enabled'".
							   " and subscriptions.expiration_date > NOW()".
							   " and subscriptions_distgroups.distgroup_id in ".getSugarInternalSubscriptionCustomerTypes('in_clause').
							   " and subscriptions.deleted = 0";
		// BEGIN: Logging
		// siLogThis('caseScoringLog.log', $subscriptions_query);
		// END: Logging
		
		$res = $GLOBALS['db']->query($subscriptions_query);
		$components['subscriptions_perpetual'] = 0;
		$messages['subscriptions_perpetual'] = "There are no perpetual subscriptions associated with the Account, modifying the score by 0";
		$components['total_subscriptions'] = 0;
		while($subscriptions_data = $GLOBALS['db']->fetchByAssoc($res)){
			if($subscriptions_data['perpetual'] == '1'){
				$components['subscriptions_perpetual'] = $constants['subscriptions_perpetual'];
				$messages['subscriptions_perpetual'] = "There is a perpetual subscription associated with the Account, modifying the score by {$constants['subscriptions_perpetual']}";
			}
			$components['total_subscriptions'] += $subscriptions_data['quantity'];
		}
		$messages['total_subscriptions'] = "The Account has {$components['total_subscriptions']} subscriptions (in the Subscriptions Module)";
		// END subscriptions: Gather Subscription Data
		
		// BEGIN opportunities: Gather Opportunity Data
		// Note, I don't check for the accounts deleted flag because we already checked in the $case_query above
		$opportunities_query =  "select opportunities.amount 'amount', \n".
								"       DATEDIFF(opportunities.date_closed, NOW()) 'days_diff' \n". 
								"from opportunities ".
								"  inner join opportunities_cstm on opportunities.id = opportunities_cstm.id_c\n".
								"  inner join accounts_opportunities on opportunities.id = accounts_opportunities.opportunity_id\n".
								"where accounts_opportunities.account_id = '{$case_account_data['account_id']}'".
								"  and opportunities_cstm.Revenue_Type_c = 'Renewal'".
								"  and opportunities.sales_stage not in ".getSugarInternalClosedStages('in_clause')."\n".
								"  and DATEDIFF(opportunities.date_closed, NOW()) <= 90\n".
								"  and DATEDIFF(opportunities.date_closed, NOW()) >= 0\n".
								"  and opportunities_cstm.opportunity_type in\n".
								"          ('Sugar Professional', 'Sugar Enterprise', 'Sugar On-Demand', 'Sugar Enterprise On-Demand',\n".
								"           'Sugar Cube', 'Support Services', 'Partner Fees')\n".
								"  and accounts_opportunities.deleted = 0".
								"  and opportunities.deleted = 0";
		// BEGIN: Logging
		//siLogThis('caseScoringLog.log', $opportunities_query);
		// END: Logging

		$res = $GLOBALS['db']->query($opportunities_query);
		$opportunity_sum = 0;
		$messages['opportunity_sum'] = "There were no upcoming opportunities for renewal, leaving the score unmodified";
		while($opportunity_data = $GLOBALS['db']->fetchByAssoc($res)){
			$multiplier = 1;
			$messages['opportunity_sum'] = "The Account has associated Opportunities up for renewal in the next ";
			if($opportunity_data['days_diff'] > 60){
				$multiplier = $constants['opp_renew']['61_to_90'];
				$messages['opportunity_sum'] .= "61 to 90";
			}
			else if($opportunity_data['days_diff'] > 30){
				$multiplier = $constants['opp_renew']['31_to_60'];
				$messages['opportunity_sum'] .= "31 to 60";
			}
			else{
				$multiplier = $constants['opp_renew']['0_to_30'];
				$messages['opportunity_sum'] .= "0 to 30";
			}
			$oppInfoUsers = array('jason', 'andy', 'kneilsen', 'lori', 'sadek');
			if(in_array($GLOBALS['current_user']->user_name, $oppInfoUsers)){
				$messages['opportunity_sum'] .= " days, making the multipler {$multiplier}, with an opportunity amount of {$opportunity_data['amount']}, modifying the score by {$opportunity_sum} ( (amount / 10) * multiplier )";
			}
			else{
				$messages['opportunity_sum'] .= " days, making the multipler {$multiplier}. Note: The opp amount is included in this calculation";
			}
			
			$opportunity_sum += (($opportunity_data['amount'] / 10) * $multiplier);
			break;
		}
		$components['opportunity_sum'] = $opportunity_sum;
		// END opportunities: Gather Opportunity Data
		
		// BEGIN casesurvey: Gather Case Survey information
		$case_survey_query =
			"select count(*) 'count'\n".
			"from csurv_surveyresponse surveys\n".
			"where surveys.acase_id in ('".implode("', '", $case_ids_for_surveys)."') and surveys.deleted = 0";
		// BEGIN: Logging
		//siLogThis('caseScoringLog.log', $case_survey_query);
		// END: Logging
		$res = $GLOBALS['db']->query($case_survey_query);
		$case_survey_data = $GLOBALS['db']->fetchByAssoc($res);
		$case_survey_count = 0;
		if($case_survey_data){
			$case_survey_count = $case_survey_data['count'];
		}
		$components['case_survey_total'] = $case_survey_count * $constants['per_survey'];
		$messages['case_survey_total'] = "There are {$case_survey_count} case surveys associated with the Account, with a survey multiplier of {$constants['per_survey']}, modifying the score by {$components['case_survey_total']}";
		// END casesurvey: Gather Case Survey information
		
		$components['final_score'] = 
			(
				($components['case_age'] + $components['note_sum'])
					* $components['case_priority_level']
					* $components['case_support_level']
					* $components['account_type']
			)
			+ $components['closed_defects']
			+ $components['cases_in_x_days']
			+ $components['account_open_cases']
			+ $components['opportunity_sum']
			+ $components['total_subscriptions']
			+ $components['subscriptions_perpetual']
			+ $components['case_survey_total'];
		
		if($action == 'update'){
			$case_update_query = "update cases set case_score = '{$components['final_score']}' where id = '{$case_id}'";
			$GLOBALS['db']->query($case_update_query);
		}
		
		// $caseScoreArray[$case_id] = $components;
		$caseScoreArray[$case_id] = $components['final_score'];
		
		if($process_messages){
			global $caseScoreMessages;
			$caseScoreMessages[$case_id] = $messages;
		}
	}
	
	return $caseScoreArray;
}


