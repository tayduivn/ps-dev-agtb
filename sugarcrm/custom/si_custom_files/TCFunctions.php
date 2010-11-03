<?php

// SADEK: BEGIN TC CUSTOMIZATION --- ENTIRE FILE IS CUSTOM
/*
 * tc (training credits) handleSave function
 *
 * This function applies all the necessary logic that must execute in order
 * to apply the tc implementation for saving Opportunities
 * 
 * $new_focus - the values of the newly saved object
 */
function tcOpportunitiesHandleSave($new_focus){
	// if it's not sugar network, return false straight away so we don't have to hit the DB below when we do the retrieve
	if($new_focus->sales_stage != "Sales Ops Closed"){
		return false;
	}
	
	// get the data that was there before the save
	$old_focus = new Opportunity();
	$old_focus->retrieve($new_focus->id);
	global $current_user;

	// BEGIN jostrow customizations
	// See ITRequest #6871
	if (empty($old_focus->id)) {
		if ($new_focus->additional_training_credits_c > 0) {
			tcOpportunitiesUpdateDates($new_focus->account_id, $new_focus->date_closed);
			tcOpportunitiesUpdateCredits($old_focus, $new_focus, TRUE);
		}
	}
	elseif ($new_focus->sales_stage != $old_focus->sales_stage) {
		$old_focus->additional_training_credits_c = 0;

		if ($new_focus->additional_training_credits_c > $old_focus->additional_training_credits_c) {
			tcOpportunitiesUpdateDates($new_focus->account_id, $new_focus->date_closed);
			tcOpportunitiesUpdateCredits($old_focus, $new_focus, TRUE);
		}
		elseif ($new_focus->additional_training_credits_c < $old_focus->additional_training_credits_c) {
			tcOpportunitiesUpdateCredits($old_focus, $new_focus, FALSE);
		}

		if (
			$new_focus->additional_training_credits_c != $old_focus->additional_training_credits_c
			&& $new_focus->date_closed != $old_focus->date_closed
		) {
			tcOpportunitiesUpdateDates($new_focus->account_id, $new_focus->date_closed);
		}
	}
	// END jostrow customizations
}

// BEGIN jostrow customization
// See ITRequest #6871
// ...rearranged this logic into functions
// $add=TRUE means adding credits, $add=FALSE means subtracting credits

function tcOpportunitiesUpdateCredits($old_focus, $new_focus, $add) {
	global $current_user;

	$related_account = new Account();
	$related_account->retrieve($new_focus->account_id);

	$add_total = $new_focus->additional_training_credits_c - $old_focus->additional_training_credits_c;

	if(empty($related_account->training_credits_purchased_c)) {
		$related_account->training_credits_purchased_c = $add_total;
	}
	else {
		$related_account->training_credits_purchased_c += $add_total;
	}

	if(empty($related_account->remaining_training_credits_c)) {
		$related_account->remaining_training_credits_c = $add_total;
	}
	else {
		$related_account->remaining_training_credits_c += $add_total;
	}

	$related_account->save();
}

function tcOpportunitiesUpdateDates($account_id, $date_closed) {
    die("I'm HERE: ".__FILE__."(".__LINE__.")");
	global $current_user;

	$related_account = new Account();
	$related_account->retrieve($account_id);

	// begin adding of one year to the expected close date (date_closed)
	require_once("include/TimeDate.php");
	$timedate = new TimeDate();
	$current_user_format = $timedate->get_date_format($current_user);
	$standard_date = $date_closed;
	$user_date = $timedate->swap_formats($date_closed, "Y-m-d", $current_user_format);
	$datearr = explode("-", $standard_date);
	$plus_one_year = gmdate("Y-m-d", mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0] + 1));
	$plus_one_year = $timedate->swap_formats($plus_one_year, "Y-m-d", $current_user_format);
	// end adding of one year to the expected close date (date_closed)
		
	// begin conditionals to determine when training credit purchase date and expiration date gets updated
	if(empty($related_account->training_credits_pur_date_c)){
		$related_account->training_credits_pur_date_c = $user_date;
	}
	else{
		$standard_date_related = $timedate->swap_formats($related_account->training_credits_pur_date_c, $current_user_format, "Y-m-d");
		$datearr_related = explode("-", $standard_date_related);

		$ts = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
		$ts_related_exp = mktime(0, 0, 0, $datearr_related[1], $datearr_related[2], $datearr_related[0]);

		if($ts > $ts_related_exp) {
			$related_account->training_credits_pur_date_c = $user_date;
		}
	}

	if(empty($related_account->training_credits_exp_date_c)){
		$related_account->training_credits_exp_date_c = $plus_one_year;
	}
	else{
		$today = strtotime("today");
		$ts = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0] + 1);
			
		if($ts > $today) {
			$related_account->training_credits_exp_date_c = $plus_one_year;
		}
	}

	$related_account->save();
}

// SADEK: END TC CUSTOMIZATION
