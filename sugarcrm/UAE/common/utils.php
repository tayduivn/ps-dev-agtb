<?php
/**
 * UAE utils
 * Author: Felix Nilam
 * Date: 03/04/2010
 */
 
function logUAE($type, $msg)
{
	global $current_user;
	$log_file = "UAE/log/uae_".$type.".log";
	
	$local_time = time();
	$gmtime = gmdate("Y-m-d H:i:s");
	$hour_diff = ceil(($local_time - strtotime($gmtime)) / 3600);
	if($hour_diff > 0){
		$hour_diff = "+$hour_diff";
	}
	
	$log = date("Y-m-d H:i:s")."(GMT $hour_diff) - ".$current_user->first_name." ".$current_user->last_name."[".$current_user->id."]: ".$msg."\n";
	// only allow max of 500KB of log file
	// this is to troubleshoot immidiate problem.
	if(file_exists($log_file) && filesize($log_file) > 500000){
		$fh = fopen($log_file, 'w');
	} else {
		$fh = fopen($log_file, 'a');
	}
	fwrite($fh,$log);
	fclose($fh);
}

function logFONactivity($type)
{
	global $current_user;
	global $db;
	
	// get the pbx_settings
	require_once('modules/fonuae_PBXSettings/fonuae_PBXSettings.php');
	$pbx_setting = new fonuae_PBXSettings();
	$pbx_setting->retrieve_by_string_fields(array('assigned_user_id' => $current_user->id));
	if(!empty($pbx_setting->id)){
		$user = $db->quote($pbx_setting->username);
		$activity = $db->quote($type);
		$now = gmdate("Y-m-d H:i:s");
		$server_id = $db->quote($pbx_setting->server_id);
		
		// update the activity
		$query = "SELECT id, count FROM fonality_stats WHERE user = '$user' and activity = '$activity'";
		$res = $db->query($query);
		$row = $db->fetchByAssoc($res);
		if(!empty($row['id'])){
			$update = "UPDATE fonality_stats SET count = ". ($row['count'] + 1).", last_accessed = '$now' WHERE id = ".$row['id'];
		} else {
			$update = "INSERT INTO fonality_stats SET user = '$user', server_id = '$server_id', activity = '$activity', count = 1, last_accessed = '$now'";
		}
		
		$db->query($update);
		
		// also insert the info row
		require('fonality/include/fonality_version.php');
		require('sugar_version.php');
		$sugarv = $sugar_flavor.$sugar_version.".".$sugar_build;
		$query = "SELECT id FROM fonality_stats WHERE user = 'info'";
		$res = $db->query($query);
		$row = $db->fetchByAssoc($res);
		if(empty($row['id'])){
			$insert = "INSERT INTO fonality_stats SET user = 'info', server_id = '$server_id', activity = 'FON: $fonality_version Sugar: $sugarv', count = 0, last_accessed = '$now'";
			$db->query($insert);
		}
	}
}

function retrieveFONactivity()
{
	global $db;
	$query = "SELECT * FROM fonality_stats";
	$res = $db->query($res);
	$activities = array();
	while($row = $db->fetchByAssoc($res)){
		$activities[] = $row;
	}
	return $activities;
}
?>