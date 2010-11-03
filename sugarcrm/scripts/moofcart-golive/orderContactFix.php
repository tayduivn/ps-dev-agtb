<?php

/*****
Fix Xcart orders who's contact is missing from the contact relationship table [contacts_orders_c] by updating or inserting a record into that table based on the username [portal_name] of the user who purchased the order
ITR 19888
jbartek
******/


chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');

global $current_user;
$current_user = new User();
$current_user->getSystemUser();


// GET ALL ORDERS LESS THAN 40000
$query = "SELECT * FROM orders WHERE order_id < 40000";

$qry = $GLOBALS['db']->query($query);

logIt($query);

$fp = fopen('./scripts/moofcart-golive/no_matching_account.csv', 'w+');
fwrite($fp,"'Order ID','Order_Id','Current_Contact_Id','Current_Account_Id','New_Contact_id','New_account_id'\r\n");
$display = true;
$run = true;
$updated_count = 0;
$ignored_count = 0;
$problem_count = 0;
$portal_inactive = 0;
// LOOP
while($row = $GLOBALS['db']->fetchByAssoc($qry)) {
	if(empty($row['username'])) {
		continue;
	}
	// GET THE CONTACT ASSOCIATED WITH THE ORDER
        $account_id = getAccountId($row['id']);	
	$current_contact_info = getContact($row['id'], $account_id);
	// GET USERNAME'S CONTACTS
	$username_info = getUsernameContact($row['username'], $account_id);
	// LOG THE DIFFERENCE IF THERE IS ONE
	if($username_info['contact_id'] != $current_contact_info['contact_id']) {
		// VERIFY THE USERNAME'S CONTACT ACCOUNT.ID
		if((empty($current_contact_info['contact_id'])) || ($username_info['account_id'] == $current_contact_info['account_id'] && !empty($username_info['contact_id']))) {
			// IF IT IS MAKE THE SWITCH
			logIt("Updating the Order: {$row['id']} Switching Contact_id FROM {$current_contact_info['contact_id']} TO {$username_info['contact_id']}");
			updateContact($row['id'], $username_info['contact_id']);
		}
		else {
			// IF ITS NOT RUN TO THE HILLS SCREAMING [LOG IT TO A TXT FILE]
			$info = array(	'id'				=>	$row['id'],
						'order_id' 			=>	$row['order_id'],
						'current_contact_id'		=>	$current_contact_info['contact_id'],
						'current_account_id'	=>	$current_contact_info['account_id'],
						'new_contact_id'		=>	$username_info['contact_id'],
						'new_account_id'		=>	$username_info['account_id'],
					);
			runScreamingLog($info);
			logIt(print_r($info,true));
			$problem_count++;
		}
	}
	else {
		$ignored_count++;
	}

}

fclose($fp);

echo "Updated: {$updated_count}\r\n";
echo "Ignored: {$ignored_count}\r\n";
echo "Problem: {$problem_count}\r\n";
echo "Portal Inactive: {$portal_inactive}\r\n";


function runScreamingLog($info)  {
	global $fp;
	$line = "'" . implode("','", $info) . "'\n";
	fwrite($fp, $line);
}

function getContact($order_id, $account_id) {
	$query = "SELECT accounts_contacts.contact_id, accounts_contacts.account_id FROM contacts_orders_c LEFT JOIN accounts_contacts ON contacts_orders_c.contacts_o7603ontacts_ida = accounts_contacts.contact_id WHERE contacts_orders_c.contacts_o95f4sorders_idb = '{$order_id}' AND accounts_contacts.deleted=0 AND accounts_contacts.account_id = '{$account_id}'";
	logIt($query);
	$qry = $GLOBALS['db']->query($query);
	$return = array();
	while($row = $GLOBALS['db']->fetchByAssoc($qry)) {
		$return = array(
					'contact_id'	=>	$row['contact_id'],
					'account_id'	=>	$row['account_id'],
				);
	}
	logIt(print_r($return,true));
	return $return;
}

function getAccountId($order_id) {
	$query = "SELECT accounts_od749ccounts_ida AS account_id FROM accounts_orders_c WHERE accounts_o0f8dsorders_idb = '{$order_id}'";
	logIt($query);
        $qry = $GLOBALS['db']->query($query);
        $return = array();
        while($row = $GLOBALS['db']->fetchByAssoc($qry)) {
		$account_id = $row['account_id'];
	}
	
	return $account_id;
}

function getUsernameContact($username, $account_id) {
	global $portal_inactive;
	$query = "SELECT accounts_contacts.contact_id, accounts_contacts.account_id, contacts.portal_active FROM contacts LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id WHERE contacts.portal_name = '{$username}' AND contacts.deleted=0 AND accounts_contacts.deleted=0 AND accounts_contacts.account_id = '{$account_id}' LIMIT 1";
	logIt($query);
	$qry = $GLOBALS['db']->query($query);
	$return = array();
	while($row = $GLOBALS['db']->fetchByAssoc($qry)) {
		$return = array(
					'contact_id'	=>	$row['contact_id'],
					'account_id'	=>	$row['account_id'],
				);
		if($row['portal_active'] == 0) {
			$portal_inactive++;
		}
	}
	logIt(print_r($return,true));
	return $return;	
}

function updateContact($order_id, $new_contact_id) {
	global $inserted_count;
	global $updated_count;
	global $run;
	$query = "UPDATE contacts_orders_c SET contacts_o7603ontacts_ida = '{$new_contact_id}' WHERE contacts_o95f4sorders_idb = '{$order_id}'";
	logIt($query);
	if($run == true) {
		$GLOBALS['db']->query($query);
	}
	$updated_count++;
	return true;
}

function logIt($msg) {
	global $display;
	$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
	if($display == true) {
		echo $msg . "\r\n";
	}
}

/*
function create_guid()
{
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(" ", $microTime);

        $dec_hex = dechex($a_dec* 1000000);
        $sec_hex = dechex($a_sec);

        ensure_length($dec_hex, 5);
        ensure_length($sec_hex, 6);

        $guid = "";
        $guid .= $dec_hex;
        $guid .= create_guid_section(3);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= create_guid_section(6);

        return $guid;

}

function create_guid_section($characters)
{
        $return = "";
        for($i=0; $i<$characters; $i++)
        {
                $return .= dechex(mt_rand(0,15));
        }
        return $return;
}

function ensure_length(&$string, $length)
{
        $strlen = strlen($string);
        if($strlen < $length)
        {
                $string = str_pad($string,$length,"0");
        }
        else if($strlen > $length)
        {
                $string = substr($string, 0, $length);
        }
}

*/
