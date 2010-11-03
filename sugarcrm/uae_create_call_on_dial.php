<?php
if(!defined('sugarEntry')) define('sugarEntry', true);
/**********************************************
 * Create call on dial and redirect to the Call
 * EditView screen
 *
 * Author: Felix Nilam
 * Date: 23/11/2007
 **********************************************/

require_once('include/entryPoint.php');
require_once('fonality/include/normalizePhone/normalizePhone.php');

global $current_user;
global $sugar_config;
global $timedate;
global $sugar_version;

session_cache_limiter("public");
session_start();

if(empty($_SESSION['authenticated_user_id'])){
        die("Not a Valid Entry Point");
}

$current_user->retrieve($_SESSION['authenticated_user_id']);
$current_language = $_SESSION['authenticated_user_language'];
$app_list_strings = return_app_list_strings_language($current_language);

$phone = $_REQUEST['phone'];
$parent_type = $_REQUEST['parent_type'];
$parent_id = $_REQUEST['parent_id'];
$contact_id = $_REQUEST['contact_id'];
if(empty($phone)){
	echo "Phone number not specified!";
} else {
	require_once('modules/Calls/Call.php');
	$newcall = new Call();
	$newcall->team_id = $currrent_user->default_team;
	$newcall->assigned_user_id = $current_user->id;
	$newcall->name = "New Call to $phone";
	if($sugar_version <= '6.0.0'){
		$dt = gmdate("Y-m-d H:i:s");
	} else {
		$dt = date("Y-m-d H:i:s");
	}
	$newcall->date_start = $timedate->to_display_date_time($dt);
	$newcall->duration_hours = 0;
	$newcall->duration_minutes = 0;
	$newcall->phone_number_c = normalizePhone($phone);
	$newcall->status = "Held";
	$newcall->direction = "Outbound";
	$newcall->parent_type = $parent_type;
	$newcall->parent_id = $parent_id;
	$newcall->save();
	$new_call_id = $newcall->id;
	if(!empty($contact_id)){
		$newcall->retrieve($new_call_id);
		$newcall->load_relationship('contacts');
		$newcall->contacts->add($contact_id);
	}

	header("Location: index.php?module=Calls&action=EditView&record=".$new_call_id."&status=Held&duration_minutes=15");
	exit();	
}
?>
