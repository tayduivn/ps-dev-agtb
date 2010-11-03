<?php
require_once('custom/si_custom_files/meta/InsideTerritoryMap.php');

// awesome fcn
function reassign_user($account_id, $display) {
	global $stateToRegionMap;
	global $regionToRepMap;
	global $last_used;

	// get bean
	$a = new Account;
	$a->retrieve($account_id);

	if(empty($a->fetched_row)) {
		if($display==1) {
			echo "no row returned for account: {$account_id}\r\n";
		}
		return false;
	}

	$cons = $a->get_linked_beans('contacts','Contact',array(),0,-1,0);
	
	// get state/country if USA/Canada keep
	if(($a->billing_address_country != 'USA' && $a->billing_address_country != 'CANADA') && ($a->shipping_address_country != 'USA' && $a->shipping_address_country != 'CANADA')) {
		$msg = "not from us or canada account: {$account_id} :: \r\n";
		$msg .= print_r($a->fetched_row);
		if($display==1) {
			echo $msg;
		}
		
		$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

		
		return false;
	}
	
	// find mapping from state/province -> territory map region
	if(strlen($a->billing_address_state) == 2) {
		// get territory
		$territory = $stateToRegionMap[$a->billing_address_state];
	}
	elseif(strlen($a->billing_address_state) > 2) {
		$abbrev = $abbreviation_map[strtoupper($a->billing_address_state)];
		if(empty($abbrev)) {
			$territory = false;
		}
		else {
			$territory = $stateToRegionMap[$abbrev];
		}
	}
	elseif(strlen($a->shipping_address_state) == 2) {
		// get territory
		$territory = $stateToRegionMap[$a->shipping_address_state];
	}
	elseif(strlen($a->shipping_address_state) > 2) {
		// get abbrev
		$abbrev = $abbreviation_map[strtoupper($a->shipping_address_state)];
		if(empty($abbrev)) {
			$territory = false;
		}
		else {
			$territory = $stateToRegionMap[$abbrev];
		}
	}
	
	$msg = "Territory for account: {$account_id} --> {$territory}\r\nBilling State: {$a->billing_address_state}\r\nShipping State: {$a->shipping_address_state}\r\n";
	
	if($display==1){
		echo $msg;
	}
	
	$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
	
	// get sales territory users
	$inside_sales = $regionToRepMap[$territory];
	if(count($inside_sales) > 1) {
		if($last_used[$territory] === false) {
			$a->assigned_user_id = reset($inside_sales);
			$last_used[$territory] = 1;
		}
		elseif($last_used[$territory] == 2) {
			$last_used[$territory] = 1;
			$a->assigned_user_id = $inside_sales[0];
		}
		else{
			$last_used[$territory] = 2;
			$a->assigned_user_id = $inside_sales[1];
		}
	}
	elseif(count($inside_sales) == 1) {
		$a->assigned_user_id = reset($inside_sales);
	}
	else {}

	$msg = "Saved Assigned_user_id AS {$a->assigned_user_id}\r\n";
	
	if($display === 0) {
		$a->save();
	}
	else {
		echo $msg;
	}
	$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
	
	// loop through the contacts and assign them to the appropriate person [same as the account]
	foreach($cons AS $con) {
		$con->assigned_user_id = $a->assigned_user_id;
		$msg = "Setting Contact ID: {$con->id} to have Assigned user Id: {$a->assigned_user_id}\r\n";
		if($display == 0) {
			$con->save();
		}
		else {
			echo $msg;
		}
		$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);

	}
	
// :: end function
}


?>