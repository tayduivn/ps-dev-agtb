<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*****************************************
 * Normalize all phone fields
 * 
 * Resave records so that normalize phone fields will 
 * be populated by the logic hooks
 *
 * Author: Felix Nilam
 * Date: 23/08/2007
 ********************************************/

require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Prospects/Prospect.php');
require_once('fonality/include/normalizePhone/normalizePhone.php');
require_once('fonality/include/normalizePhone/utils.php');

if(file_exists('cache/uae_setup.php')){
	require('cache/uae_setup.php');
} else {
	$uae_setup_delay = 0;
}

if(file_exists('cache/normalize_status.php')){
	unlink('cache/normalize_status.php');
}

// get total phone numbers
global $db;
$total = 0;
$tquery = "SELECT count(id) as count from accounts where deleted = 0";
$trow = $db->fetchByAssoc($db->query($tquery));
$total += $trow['count'];
$tquery = "SELECT count(id) as count from contacts where deleted = 0";
$trow = $db->fetchByAssoc($db->query($tquery));
$total += $trow['count'];
$tquery = "SELECT count(id) as count from leads where deleted = 0";
$trow = $db->fetchByAssoc($db->query($tquery));
$total += $trow['count'];

// save initial progress
$fh = fopen('cache/normalize_status.php','w');
$str = "<?php\n\$accounts = 0;\n\$contacts = 0;\n\$leads = 0;\n\$total = ".$total.";\n\$total_phone = 0;\n?>";
fwrite($fh, $str);
fclose($fh);

global $beanList;

$bean_array = array("Accounts", "Contacts", "Leads"); //, "Prospects");

$phone_numbers = 0;

// Resave Beans
foreach($bean_array as $bean){
	$focus = new $beanList[$bean]();
	$db = &$focus->db;

	// Get Phone fields
	$phone_keys = array();
	$all_field_def_names = array();
	foreach($focus->field_defs as $key => $def){
		$all_field_def_names[] = $def['name'];
	}
	$phone_keys = getAllPhoneFields($focus);

	$query = "SELECT * from ".$focus->table_name." left join ".$focus->table_name."_cstm on id = id_c where deleted = 0";
	$res = $db->query($query);
	while($row = $db->fetchByAssoc($res)){
		$check = "SELECT * from ".$focus->table_name."_cstm where id_c = '".$row['id']."'";
		$res_chk = $db->query($check) or die("Error running query: $check");
		$row_chk = $db->fetchByAssoc($res_chk);
		$custom_query = array();
		foreach($phone_keys as $orig){
			$custom  = $orig."_normalized_c";
			if(in_array($custom, $all_field_def_names) && !empty($row[$orig])){
				$custom_query[] = $custom. " = '".normalizePhone($row[$orig])."'";
				$phone_numbers++;
			}
		}
		
		if(!empty($custom_query)){
		if(!empty($row_chk['id_c'])){
				$update_query = "UPDATE ".$focus->table_name."_cstm set ".implode(",",$custom_query)." where id_c = '".$row['id']."'";
			} else {
				$update_query = "INSERT INTO ".$focus->table_name."_cstm set id_c = '".$row['id']."',".implode(",",$custom_query);
			}
			
			$focus->db->query($update_query) or die ("Error running query: $update_query");
		}
		
		// save progress count
		save_norm_progress($bean, $phone_numbers);
	}

	// delay the process if specified
	sleep($uae_setup_delay);
}

function save_norm_progress($type, $phone_numbers){
	if(file_exists('cache/normalize_status.php')){
		require('cache/normalize_status.php');
	} else {
		$accounts = 0;
		$contacts = 0;
		$leads = 0;
		$total = 0;
	}
	
	$var = strtolower($type);
	$$var++;

	$fh = fopen('cache/normalize_status.php','w');
	$str = "<?php\n
\$accounts = $accounts;\n
\$contacts = $contacts;\n
\$leads = $leads;\n
\$total = $total;\n
\$total_phone = $phone_numbers;\n
?>";
	fwrite($fh, $str);
	fclose($fh);
}
?>
