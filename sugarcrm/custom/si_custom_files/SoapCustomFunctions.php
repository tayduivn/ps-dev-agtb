<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('soap/SoapHelperFunctions.php');
require_once('soap/SoapTypes.php');
require_once('config.php');
require_once('modules/Administration/SessionManager.php');

// please be careful when modifying this method-- it's also being used by developers.sugarcrm.com
$server->register(
		'get_subscription_expiration',
		array('subscription_id'=>'xsd:string',
			  'auth_key'=>'xsd:string'),
		array('return'=>'xsd:string'),
		$NAMESPACE);

function get_subscription_expiration($subscription_id, $auth_key){
	if($auth_key != "imp6ninaznor0fatmnahe5noooavam8t")
		return '';

	$return_expiration = '';
	$query = "select UNIX_TIMESTAMP(expiration_date) as expiration_date, perpetual ".
			 "from subscriptions ".
			 "where subscriptions.subscription_id = '$subscription_id' and ".
				   "subscriptions.deleted = '0' and ".
				   "subscriptions.status = 'enabled' ";

	$res = $GLOBALS['db']->query($query);
	if($res){
		$row = $GLOBALS['db']->fetchByAssoc($res);
		if(!empty($row['expiration_date'])){
			if($row['perpetual'] == '1'){
				// If perpetual, current expiration is current time + some large number to permit access
				$return_expiration = time() + 100000000;
			}
			else{
				$return_expiration = $row['expiration_date'];
			}
        }
	}

	return $return_expiration;
}

// please be careful when modifying this method-- it's also being used by developers.sugarcrm.com
$server->register(
		'get_subscription_files',
		array('subscription_id'=>'xsd:string',
			  'auth_key'=>'xsd:string'),
		array('return'=>'xsd:string'),
		$NAMESPACE);

function get_subscription_files($subscription_id, $auth_key){
	if($auth_key != "imp6ninaznor0fatmnahe5noooavam8t")
		return '';

	$return_files = '';
	$query = "select distgroups.name 'distgroup_name' ".
			 "from subscriptions ".
			 "     inner join subscriptions_distgroups on subscriptions.id = subscriptions_distgroups.subscription_id ".
			 "     inner join distgroups on subscriptions_distgroups.distgroup_id = distgroups.id ".
			 "where subscriptions.subscription_id = '$subscription_id' and ".
				   "subscriptions.deleted = '0' and ".
				   "subscriptions_distgroups.deleted = '0' and ".
				   "distgroups.deleted = '0' and ".
				   "subscriptions.status = 'enabled' ";

	$res = $GLOBALS['db']->query($query);
	if($res){
		$first = true;
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			if(!$first){
				$return_files .= ",";
			}
			else{
				$first = false;
			}
			$return_files .= $row['distgroup_name'];
		}
	}

	return $return_files;
}

$server->register(
        'get_account_rep',
        array('email_address'=>'xsd:string',
			  'default_email_address'=>'xsd:string',
			  'auth_key'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);


/* returns:
 */
function get_account_rep($email_address, $default_email_address, $auth_key){
	if($auth_key != "ded7tuchoa6axcagin5hayrati9povan")
		return '';

	$email_address = trim($email_address);
	$return_email = 'sales-ops@sugarcrm.com';
	if(!empty($default_email_address)){
		$return_email = $default_email_address;
	}
	if(empty($email_address))
		return $return_email;
    $query =
        "SELECT user_email.email_address as email1
                FROM contacts
                            INNER JOIN accounts_contacts
                              ON contacts.id=accounts_contacts.contact_id and accounts_contacts.deleted=0
                            INNER JOIN accounts
                              ON accounts_contacts.account_id=accounts.id
                            INNER JOIN users
                              ON accounts.assigned_user_id = users.id
                            INNER JOIN email_addr_bean_rel con_email_rel
                              ON con_email_rel.bean_id = contacts.id and con_email_rel.bean_module = 'Contacts' and con_email_rel.deleted=0
                            INNER JOIN email_addresses con_email
                              ON con_email_rel.email_address_id = con_email.id and con_email.deleted=0
                            INNER JOIN email_addr_bean_rel user_email_rel
                              ON user_email_rel.bean_id = users.id and user_email_rel.bean_module = 'Users' and user_email_rel.deleted=0
                            INNER JOIN email_addresses user_email
                              ON user_email_rel.email_address_id = user_email.id and user_email.deleted=0
                where con_email.email_address = '$email_address'
                  AND contacts.deleted=0  AND accounts.deleted=0 AND user_email_rel.primary_address = '1'
                order by accounts_contacts.date_modified desc";
	$res = $GLOBALS['db']->query($query);
	if($res){
		$row = $GLOBALS['db']->fetchByAssoc($res);
		if(!empty($row['email1'])){
			$return_email = $row['email1'];
		}
	}

	return $return_email;
}

$server->register(
        'auto_close_opp_completed_order',
        array('opportunity_id'=>'xsd:string',
		'opportunity_type' => 'xsd:string',
		'order_number' => 'xsd:string',
		'seats_purchased' => 'xsd:string',
		'purchase_subtotal' => 'xsd:string',
		'order_status' => 'xsd:string',
			  'auth_key'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);


/* returns:
 */
function auto_close_opp_completed_order($opportunity_id, $opportunity_type, $order_number, $seats_purchased, $purchase_subtotal, $order_status, $auth_key){
	$set_cc_order_sales_stage = 'Closed Won';

	if($auth_key != "d379ecd518c11i72ba621d346zcd59dc683a")
		return 'bad_auth';

	if(empty($opportunity_id)){
		return 'empty_opp_id_passed';
	}

	require_once('modules/Subscriptions/Subscription.php');
	require_once('modules/DistGroups/DistGroup.php');
	require_once('modules/Opportunities/Opportunity.php');
	require_once('custom/si_custom_files/MoofCartHelper.php');

	$app_list_strings = return_app_list_strings_language('en_us');

	$old_current_user = $GLOBALS['current_user'];

	$GLOBALS['current_user'] = new User();
	$GLOBALS['current_user']->getSystemUser();

	$opportunity = new Opportunity();
	$opportunity->disable_row_level_security = true;
	$opportunity->retrieve($opportunity_id);

	if(empty($opportunity->id)){
		return 'no_opp_exists';
	}

	$opp_description = "";

	if (!empty($opportunity->description)) {
		$opp_description .= "\n\n";
	}

	$opp_description .= "(" . date('Y-m-d') . ") MoofCart made the following updates to this Opportunity:\n";
	$opp_description .= " * Customer payment method: {$opportunity_type}\n";

	$opp_description .= " * Order Status: ";
	$opp_description .= MoofCartHelper::$xcart_order_statuses[$order_status] . "\n";

	if($opportunity_type == 'credit_card'){
		$opportunity->sales_stage = $set_cc_order_sales_stage;

		$opp_description .= " * Sales Stage: " . $app_list_strings['sales_stage_dom'][$set_cc_order_sales_stage] . "\n";
	}

	$opportunity->order_number = $order_number;
	$opp_description .= " * Order Number: {$order_number}\n";

	// the Opportunity Amount does not match the purchased amount; update it
	if (!empty($purchase_subtotal) && round($opportunity->amount, 2) != round($purchase_subtotal, 2)) {
		$opportunity->amount = $purchase_subtotal;
		$opp_description .= " * Amount: {$purchase_subtotal}\n";
	}

	// the Opportunity 'Subscriptions' field does not match the number of seats purchased; update it
	if (!empty($seats_purchased) && $opportunity->users != $seats_purchased) {
		$opportunity->users = $seats_purchased;
		$opp_description .= " * Subscriptions: {$seats_purchased}\n";
	}


	$subscription_data = MoofCartHelper::getLastExpirationDate($opportunity->account_id);

	if (empty($subscription_data)){
		$opp_description .= " * No Subscriptions were found linked to this Account; could not update\n";
	}
	else {
		// See ITRequests #10528 and #10899
		// Check the DistributionGroup(s) related to the Subscription we've found; there should only be one linked DistributionGroup
		// 1) If we're dealing with a Converge type Opportunity:
		//      a) If we find a replacement in our non-Converge => Converge mapping array, change the DistributionGroup
		//      b) also update the DistributionGroup Quantity
		// 2) If we're NOT dealing with a Converge type Opportunity: just update the DistributionGroup Quantity

		$sub = new Subscription();
		$sub->retrieve($subscription_data['id']);

		// get the DistributionGroups linked to this Subscription
		$distgroups = $sub->get_linked_beans('distgroups', 'DistGroup', array(), 0, -1, 0);

		if (empty($distgroups)) {
			$opp_description .= " * Subscription {$sub->subscription_id} DistributionGroup was not updated because no DistributionGroups are linked to it\n";
		}
		elseif (count($distgroups) > 1) {
			$opp_description .= " * Subscription {$sub->subscription_id} DistributionGroup was not updated because more than one DistributionGroup is linked to it\n";
		}
		else {
			if (in_array($opportunity->opportunity_type, MoofCartHelper::$converge_opportunity_types)) {
					$distgroup = $distgroups[0];

					if (in_array($distgroup->id, array_keys(MoofCartHelper::$distgroup_converge_map))) {
						$new_distgroup = MoofCartHelper::$distgroup_converge_map[$distgroup->id];
						$new_distgroup_bean = new DistGroup();
						$new_distgroup_bean->retrieve($new_distgroup);

						$opp_description .= " * Subscription {$sub->subscription_id} DistributionGroup was changed to {$new_distgroup_bean->name} (Quantity: {$seats_purchased})\n";

						$sub->distgroups->delete($sub->id, $distgroup->id);
						$sub->distgroups->add($new_distgroup, array('quantity' => $seats_purchased));
					}
					elseif ($distgroup->quantity != $seats_purchased) {
						$opp_description .= " * Subscription {$sub->subscription_id} DistributionGroup Quantity was changed to {$seats_purchased}\n";

						$sub->distgroups->add($distgroup->id, array('quantity' => $seats_purchased));
					}
			}
			else {
					$distgroup = $distgroups[0];

					if ($distgroup->quantity != $seats_purchased) {
						$opp_description .= " * Subscription {$sub->subscription_id} DistributionGroup Quantity was changed to {$seats_purchased}\n";

						$sub->distgroups->add($distgroup->id, array('quantity' => $seats_purchased));
					}
			}
		}


		if($opportunity_type == 'credit_card'){
		    /*
		     * Sadek: Doing this next update via a direct database
		     * query because I don't trust that the TimeDate class will
		     * not break in the future(Since this is a very important
		     * update)
		     */
		    /*
		     * jmullan: adding in a switch for the new terms. We could
		     * add in 3 MONTH and 6 MONTH for quarterly and
		     * semi-annually, respectively. Yes/no?
		     */
		    switch ($opportunity->Term_c) {
		    case 'Two Year' :
			$interval = '2 YEAR';
			break;
		    case 'Three Year' :
			$interval = '3 YEAR';
			break;
		    case 'Four Year' :
			$interval = '4 YEAR';
			break;
		    case 'Five Year' :
			$interval = '5 YEAR';
			break;
		    default :
			$interval = '1 YEAR';
			break;
		    }
			$update_query = "update subscriptions set ignore_expiration_date = 0, expiration_date = adddate(expiration_date, INTERVAL $interval ) where id = '{$subscription_data['id']}'";
			$GLOBALS['db']->query($update_query);

			$sub_date_query = $GLOBALS['db']->query("SELECT expiration_date FROM subscriptions WHERE id = '{$subscription_data['id']}'");
			$sub_date_row = $GLOBALS['db']->fetchByAssoc($sub_date_query);

			$opp_description .= " * Subscription {$sub->subscription_id} Expiration Date was changed to {$sub_date_row['expiration_date']}\n";

			// Sadek: We therefore also have to insert audit data into the subscriptions_audit table
			$audit_query = "insert into subscriptions_audit set id = '".create_guid()."', parent_id = '{$subscription_data['id']}', date_created = NOW(), created_by = '1', ".
						"field_name = 'expiration_date', data_type = 'date', before_value_string = '{$subscription_data['expiration_date']}', after_value_string = adddate('{$subscription_data['expiration_date']}', INTERVAL $interval )";
			$GLOBALS['db']->query($audit_query);
		}
	}

	$opportunity->description = $opportunity->description . $opp_description;

	$opportunity->processed_by_moofcart_c = 1;

	$opportunity->save(TRUE);

	$GLOBALS['current_user'] = $old_current_user;

	return 'success';
}

function subscription_direct_download($license_key, $portal_user, $download_name, $auth_key){
    if($auth_key != "1fa3b1f1e67fa45d8a06a1b9d76e07d8")
        return 'bad_auth';
	
	if(empty($portal_user)){
		return 'no_portal_user';
	}
	
	if(empty($download_name)){
		return 'no_download_name';
	}
	
	$subscription_query = "select id from subscriptions where subscription_id = '{$license_key}' and deleted = 0 and status = 'enabled' and expiration_date >= date_format(NOW(), '%Y-%m-%d')";
	
	$res = $GLOBALS['db']->query($subscription_query);
	$row = $GLOBALS['db']->fetchByAssoc($res);
	$insert_log_query = "insert into subscriptions_downloads set id = '".create_guid()."', date_entered = NOW(), deleted = 0, portal_user = '{$portal_user}', download_name = '{$download_name}', subscription_id = '{$row['id']}', success = ";
	if(empty($row)){
		$insert_log_query .= "0";
		$GLOBALS['db']->query($insert_log_query);
		return 'no_subscription_found';
	}
	else{
		$insert_log_query .= "1";
		$GLOBALS['db']->query($insert_log_query);
	}
	
	return 'success';
}
