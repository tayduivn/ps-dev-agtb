<?php

// BEGIN jostrow customization
// See ITRequest #15964 -- temporary fix to prevent Pardot scripts from running indefinitely

set_time_limit(3600 * 4);

// END jostrow customization

chdir(dirname(__FILE__));
require_once('pid_functions.php');
script_make_pid('pardot_prospects_update');
register_shutdown_function('script_clear_pid', 'pardot_prospects_update');

$get_existing_max = false;
$dry_run = false;
ob_start();
require_once('pardotApi.class.php');


chdir('../..');
define('sugarEntry', true);

// BEGIN jostrow customization
// Temporarily log memory consumption
require_once('scripts/jostrow_log_memory_usage.php');
// END jostrow customization


require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');
require('scripts/pardot/pardot_config.php');

global $app_list_strings;
$app_list_strings = return_app_list_strings_language('en_us');


/* pluto is used by all forms on sugarcrm.com */
$assigned_user_name = 'Leads_HotMktg';
$assigned_user_pass = '9139a90b1bd94dc57d8b57a5815a2353';
$assigned_user = new User();
$assigned_user_id = $assigned_user->retrieve_user_id($assigned_user_name);

$current_user = new User();
$user_id = $current_user->retrieve_user_id('admin');
$current_user->retrieve($user_id);
		  
$pardot = pardotApi::magic();

$max_id = 0;
$downloaded = 0;
$lastResultCount = 0;
$runaway_stop = 1000;
$error = 0;

$get_last_prospect_update = "
SELECT
    value
FROM
    config
WHERE
    category = 'pardot' and name = 'last_prospect_update'
";

$last_prospect_update = '2009-12-08 19:00:00';
$prospect_update = true;
if($res = $GLOBALS['db']->query($get_last_prospect_update)){
	$row = $GLOBALS['db']->fetchByAssoc($res);
	if(!empty($row['value'])){
		$last_prospect_update = $row['value'];
		$prospect_update = false;
	}
}

$ids_to_add = array();
$ids_to_touchpoints = array();
do {
	/*
	 * Get the first group of 200
	 */
	$criteria = array('score_greater_than' => $pardot_config['min_score_to_sync'],
			  'last_activity_after' => $last_prospect_update);
	if ($max_id) {	
		$criteria['id_greater_than'] = $max_id;
	}
	$prospects = $pardot->getProspectsWhere($criteria, 'mobile', array('id'));
	
	$lastResultCount = $pardot->getLastResultCount();
	$retrieved_prospects = count($prospects);

	if ($retrieved_prospects) {
		$downloaded += $retrieved_prospects;
		$prospect_ids = array();
		foreach ($prospects as $prospect) {
			$prospect_ids[] = intval($prospect['id']);

            unset($prospect);
		}
        // set the max_id before we unset prospects
        $max_id = max(array_keys($prospects));
        unset($prospects);
	
		$touchpoints_query = "
SELECT
	`touchpoints_cstm`.`id_c`,
	`touchpoints_cstm`.`prospect_id_c`
FROM
	`touchpoints_cstm`
INNER JOIN
    `touchpoints` ON
        `touchpoints`.id = `touchpoints_cstm`.`touchpoints_id`
    AND
        `touchpoints`.`scrubbed` = 1
    AND
        `touchpoints`.`assigned_user_id` <> '21030676-7f66-df76-8afb-44adcda44c25'
    AND
        `touchpoints`.`deleted` = 0
WHERE
	`touchpoints_cstm`.`prospect_id_c` IN (" . join(', ', $prospect_ids) . ")
";
	
		$res = $GLOBALS['db']->query($touchpoints_query);
		if (!$res) {
			/*
			 * If there is a problem with the query, quick early and make a lot of
			 * noise in the logs.
			 */
			$GLOBALS['db']->checkError('Error with query');
			$GLOBALS['log']->fatal("----->siProspectsToTouchpoints.php query failed: " . $touchpoints_query);
			echo "Error with query {$touchpoints_query}\n";
			exit(1);
		} else {
			/*
			 * The query did not bomb, so put all of the opportunity ids into an
			 * array for future processing.
			 */
			while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
				$ids_to_touchpoints[$row['prospect_id_c']] = $row['id_c'];
			}
			$GLOBALS['db']->query('select 1'); // Hack used just to maintain the database connection. without it, if this loop takes too long, we lose it
		}
	} else {
		# echo "No prospects retrieved\n";
	}
	// echo "downloaded: $downloaded lastResultCount $lastResultCount retrieved_prospects $retrieved_prospects\n";
} while ($lastResultCount && $retrieved_prospects && (0 < $runaway_stop--));

if($prospect_update){
	$prospect_query = "insert into config set category = 'pardot', name = 'last_prospect_update', value = FROM_UNIXTIME(UNIX_TIMESTAMP(CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 10 MINUTE), 'SYSTEM', '-5:00')))";
}
else{
	$prospect_query = "update config set value = FROM_UNIXTIME(UNIX_TIMESTAMP(CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 10 MINUTE), 'SYSTEM', '-5:00'))) where category = 'pardot' and name = 'last_prospect_update'";
}
$GLOBALS['db']->query($prospect_query);

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
	echo $whatevs;
}

if (!$dry_run) {
	foreach ($ids_to_touchpoints as $prospect_id_c => $touchpoint_id) {
		$command = 'php scripts/pardot/updateTouchpointFromProspect.php '
			. escapeshellarg($touchpoint_id) . ' ' . escapeshellarg($prospect_id_c);
		// echo "$command\n";
		$output = exec($command);
		$GLOBALS['db']->query('select 1'); // Hack used just to maintain the database connection. without it, if this loop takes too long, we lose it
		
		if (!empty($output)) {
			echo "Error updating $prospect_id_c\n";
			echo $output."\n";
			$error = 1;
		}
	}
}

if ($error) {
	die($error);
}
