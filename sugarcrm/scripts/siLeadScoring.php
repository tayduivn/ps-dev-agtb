<?php

chdir('..');

$leadRunFile = 'custom/si_custom_files/meta/leadScoringRun.php';
// If they haven't updated the leadScoringMeta.php through the UI, we don't rerun.
if(file_exists($leadRunFile)){
    $contents = file_get_contents($leadRunFile);
    if($contents != '1' && $contents != 1){
	// TEMPORARY CHANGE BY JULIAN
	// removed the message so ckelly doesn't get e-mails from cron all the time
        die("File $leadRunFile hasn't been updated, not executing this script\n");
    }
}
else{
    die("File $leadRunFile doesn't exist. Not executing this script\n");
}

define('sugarEntry', true);
require_once('include/entryPoint.php');

global $leadScoringMeta;
require('custom/si_custom_files/meta/leadScoringMeta.php');

$limit = '0';

$continue = true;

while($continue){
	$query = "select * from leads inner join leads_cstm on leads.id = leads_cstm.id_c limit $limit,100";
	$res = $GLOBALS['db']->query($query);
	$foundOne = false;
	while($row = $GLOBALS['db']->fetchByAssoc($res)){
		$foundOne = true;
		$last_id = $row['id'];
		leadScore($row);
	}
	
	if(!$foundOne){
		$continue = false;
	}
	$limit += 100;
}

$fp = fopen($leadRunFile, 'w');
fwrite($fp, '0');
fclose($fp);

function leadScore($row){
	global $leadScoringMeta;
	$multiplier = 1;
	$lead_score = 0;
	foreach($leadScoringMeta as $field_name => $value_array){
		$arr_index = '';
		if(isset($row[$field_name]) && array_key_exists($row[$field_name], $value_array)){
			$arr_index = $row[$field_name];
		}
		else if(array_key_exists('_OTHER_', $value_array)){
			$arr_index = '_OTHER_';
		}
		
		if(!empty($arr_index) && !empty($value_array[$arr_index]['value'])){
			switch($value_array[$arr_index]['type']){
				case 'multiplier': $multiplier *= $value_array[$arr_index]['value']; break;
				case 'division': $multiplier /= $value_array[$arr_index]['value']; break;
				case 'addition': $lead_score += $value_array[$arr_index]['value']; break;
				case 'subtraction': $lead_score -= $value_array[$arr_index]['value']; break;
				default; break;
			}
		}
	}
	
	//DEE CUSTOMIZATION - ITREQUEST 4502
        if(isset($row->parent_lead_id) && !empty($row->parent_lead_id))
        { $lead_score = $lead_score + 50; }
        //END DEE CUSTOMIZATION - ITREQUEST 4502	
	
	$final_lead_score = $lead_score * $multiplier;
		
	if($final_lead_score != $row['lead_score']){
		$update = "update leads set lead_score = '$final_lead_score' where id = '{$row['id']}'";
		$GLOBALS['db']->query($update);
	}
}

