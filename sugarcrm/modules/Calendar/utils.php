<?php

function cal_handle_link($url){
	if(function_exists("ajaxLink"))
		return ajaxLink($url);
	else
		return $url;
}

function check_owt($i,$j,$r_start,$r_end){
	if($i*60+$j < $r_start || $i*60+$j >= $r_end)
		return "owt";
	else
		return "";
}

function timestamp_to_user_formated2($t,$format = false){
	global $timedate;
	if($format == false)
		$f = $timedate->get_date_time_format();
	else
		$f = $format;			
	return date($f,$t - date('Z',$t) );	
}

function to_timestamp_from_uf($d){	
	$db_d = $GLOBALS['timedate']->swap_formats($d,$GLOBALS['timedate']->get_date_time_format(),'Y-m-d H:i:s');
	$ts_d = to_timestamp($db_d);	
	return $ts_d;	
}

function to_timestamp($db_d){
	$date_parsed = date_parse($db_d);
	$date_unix = gmmktime($date_parsed['hour'],$date_parsed['minute'],$date_parsed['second'],$date_parsed['month'],$date_parsed['day'],$date_parsed['year']);
	return $date_unix;
}

function add_zero($t){
	if($t < 10)
		return "0" . $t;
	else
		return $t;
}

function get_invitees_list($bean,$type){
			$userInvitees = array();
			$q = 'SELECT mu.user_id, mu.accept_status FROM '.$type.'s_users mu WHERE mu.'.$type.'_id = \''.$bean->id.'\' AND mu.deleted = 0 ';
			$r = $bean->db->query($q);
			while($a = $bean->db->fetchByAssoc($r))
				$userInvitees[] = $a['user_id'];			
					
			return $userInvitees;					
}

function remove_recurrence($bean,$table_name,$jn,$record){

	global $db;
	
	if($table_name == "meetings")
		$type = "meeting";
	else if($table_name == "calls")
		$type = "call";
			
	$qu = " 
		SELECT id FROM	".$table_name." t
		JOIN 	".$table_name."_cstm c ON t.id = c.id_c
		WHERE c.".$jn." = '".addslashes($record)."'
	";
	$re = $db->query($qu);
	while($ro = $db->fetchByAssoc($re)){
		$qu = " 
			UPDATE	".$table_name."_users t
			SET t.deleted = 1 	 
			WHERE t.".$type."_id = '".addslashes($ro['id'])."'
		";	

		$db->query($qu);
	}
	
	
	$qu = "SELECT id_c FROM ".$table_name."_cstm WHERE ".$jn." = '".addslashes($record)."'";
	$re = $db->query($qu);
	while($ro = $db->fetchByAssoc($re)){
		$qu = "UPDATE ".$table_name." SET deleted = 1 WHERE id = '".$ro['id_c']."'";
		$db->query($qu);
	}
	
}

function get_fields(){	
	return array(
		'Meetings' => array(
			'name',
			//'assigned_user_name',
			//'assigned_user_id',
			'date_start',
			'duration_hours',
			'duration_minutes',
			//'reminder_time',
			'status',
			//'location',
			'description',
			'parent_type',
			'parent_name',
			'parent_id',
		),
		'Calls' => array(
			'name',
			//'assigned_user_name',
			//'assigned_user_id',
			'date_start',
			'duration_hours',
			'duration_minutes',
			//'reminder_time',
			'status',
			//'direction',
			'description',
			'parent_type',
			'parent_name',
			'parent_id',
		),		
		'Tasks' => array(
			'name',
			//'assigned_user_name',
			//'assigned_user_id',
			'date_start',
			'date_due',
			'status',
			//'priority',
			'description',
			'parent_type',
			'parent_name',
			'parent_id',
		),
	);
}


?>
