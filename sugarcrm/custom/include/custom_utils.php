<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//custom functions for Sugar Internal
function get_partner_array($add_blank = TRUE, $current_partner = "") {
	global $log;

	$partner_array = get_register_value("partner_array", $add_blank, $current_partner);

	if (!$partner_array) {
		require_once("include/database/PearDatabase.php");
		$db = PearDatabase::getInstance();

		// this block prevents the script from failing before the database is populated during installation
		$check_result = $db->query("SHOW TABLES LIKE 'accounts'", "Error checking for accounts table");
		$check_row = $db->fetchByAssoc($check_result);
		if ($check_row === FALSE) {
			return array();
		}
		// end table check block

		$temp_result = array();

		$query = "SELECT id, name FROM accounts WHERE deleted = 0 AND account_type in ('Partner', 'Past Partner', 'Partner-Pro', 'Partner-Ent') ";

		if (!empty($current_partner)) {
			$query .= " AND id = '{$current_partner}'";
		}

		$query .= " ORDER BY name ASC";

		$log->debug("get_partner_array query: {$query}");

		$result = $db->query($query, TRUE, "Error filling in partner array: ");

		if ($add_blank) {
			$temp_result[''] = "";
		}

		while ($row = $db->fetchByAssoc($result)) {
			$temp_result[$row['id']] = $row['name'];
		}

		$partner_array = $temp_result;

		set_register_value("partner_array", $add_blank, $current_partner, $temp_result);

	}

	return $partner_array;
}
function get_campaign_array($add_blank = TRUE, $current_campaign = "") {
	
	include('custom/si_logic_hooks/Campaigns/campaign_list.php');
	
	if(!empty($current_campaign)){
		if(isset($campaign_list[$current_campaign])){
			return array($current_campaign => $campaign_list[$current_campaign]);
		}
		else{
			return array();
		}
	}

	if (!$add_blank){
		unset($campaign_list['']);
	}
	
	return $campaign_list;
}

function get_team_array_special($add_blank = FALSE, $where="") {
    global  $current_user;
    $team_array = get_register_value('team_array_special', $add_blank.'ADDBLANK');
 
    if(!empty($team_array))
    {
      return $team_array;
    }
 
    require_once('include/database/PearDatabase.php');
    $db = & PearDatabase::getInstance();
 
    if(is_admin($current_user))
    {
            $query = 'SELECT t1.id, t1.name FROM teams t1 where t1.deleted = 0 '.$where.' ORDER BY t1.private,t1.name  ASC';
    }
    else
    {
            $query = 'SELECT t1.id, t1.name FROM teams t1, team_memberships t2 where t1.deleted = 0 and t2.deleted = 0 and t1.id=t2.team_id and t2.user_id = '."'".$current_user->id."'".' '.$where.' ORDER BY t1.private,t1.name ASC';
    }
 
    $GLOBALS['log']->debug("get_team_array query: $query");
   
    $result = $db->query($query, true, "Error filling in team array: ");
 
    if ($add_blank) {
        $team_array[""] = "";
    }
 
    while ($row = $db->fetchByAssoc($result)) {
        $team_array[$row['id']] = $row['name'];
    }
 
    set_register_value('team_array_special', $add_blank.'ADDBLANK', $team_array);
    return $team_array;
}
//END CUSTOMIZATIONS - jgreen

// BEGIN SADEK DEBUG FUNCTIONS
function si_debug_backtrace($echo = false){
	$inclusion_list = array('file', 'line', 'function');
	$data = debug_backtrace();
	foreach($data as $idx => $stack_array){
		foreach($stack_array as $type => $value){
			if(!in_array($type, $inclusion_list)){
				unset($data[$idx][$type]);
			}
		}
	}
	unset($data[0]);
	if($echo){
		echo "<PRE>\n";
		var_dump($data);
		echo "\n</PRE>\n";
	}
	return $data;
}

function siLogThis($filename, $message){
	if(is_array($message) || is_object($message)){
		$message = var_export($message, true);
	}
	$date = date('Y-m-d H:i:s');
	$message = "[$date] $message\n";
	if ('/' != substr($filename, 0, 1)) {
	    $filename = '/var/www/sugarinternal/logs/' . $filename;
	}
	$fp = fopen($filename, 'a');
	fwrite($fp, $message);
	fclose($fp);
}

// END SADEK DEBUG FUNCTIONS


function clone_record(
	$tables = array(),
	$from = array(),
	$to = array()
	)
{
	global $db;

	foreach($tables as $table)
	{
		$query = "SELECT * FROM $table WHERE deleted = '0'";
		foreach ($from as $field => $value)
			$query .= " AND $field = '$value'";

		$results = $db->query($query);
		while($row = $db->fetchByAssoc($results)) {
			$names = '';
			$values = '';
			foreach ($to as $field => $value)
				$row[$field] = $value;
			$row['id'] = create_guid();

			foreach ($row as $name => $value) {
				if(empty($names)) {
					$names .= $name;
					$values .= "'$value'";
				}
				else {
					$names .= ', '. $name;
					$values .= ", '$value'";
				}
			}
			$db->query("INSERT INTO $table ($names) VALUES ($values)");
		}
	}
}
