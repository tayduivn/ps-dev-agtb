<?php
/*
** @author: Julian Ostrow, Sadek Baroudi
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (original is unknown), 2295
** Description: syncs heartbeats from our licensing server; creates/updates SugarUpdates and SugarInstallations records in Sugar Internal
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Hbv2/Process_hb_queue.php
*/

require_once("core.php");

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("memory_limit", "500M");

// BEGIN REPORTING CODE
$set_reporting_start = TRUE;
// END REPORTING CODE

$remote_db = mysql_connect($remote_db_host, $remote_db_user, $remote_db_pass) or handle_error(mysql_error(), TRUE);
mysql_select_db($remote_db_name, $remote_db) or handle_error(mysql_error(), TRUE);

$db = mysql_connect($db_host, $db_user, $db_pass) or handle_error(mysql_error(), TRUE);
mysql_select_db($db_name, $db) or handle_error(mysql_error(), TRUE);

$latest_id = get_latest_processed_id();

// get heartbeats to process from queue
$queue_res = mysql_query("SELECT * FROM log_info WHERE id > {$latest_id} ORDER BY id ASC LIMIT {$process_limit}", $remote_db)
	or handle_error(mysql_error(), TRUE);

while ($queue_row = mysql_fetch_assoc($queue_res)) {
	// BEGIN REPORTING CODE
	if ($set_reporting_start === TRUE) {
		$set_reporting_start = FALSE;
		$current_month = substr($queue_row['time_stamp'], 0, 10);
	}

	// new day, run the stats!
	if ($current_month != substr($queue_row['time_stamp'], 0, 10)) {
		handle_error("DEBUG: beginning to process stats for {$current_month}", FALSE);
		$edition_status_res = mysql_query("SELECT COUNT(*) AS total, sugar_flavor, status FROM sugar_installations
			GROUP BY sugar_flavor, status", $db) or handle_error(mysql_error(), TRUE);

		$installation_total = 0;
		while ($edition_status_row = mysql_fetch_assoc($edition_status_res)) {
			$installation_total += $edition_status_row['total'];
			$totals[$edition_status_row['status']][$edition_status_row['sugar_flavor']] = $edition_status_row['total'];
		}

		$total_installations_active = "";
		foreach ($totals['A'] as $edition => $t) {
			$total_installations_active .= "{$edition}:{$t} ";
		}

		$total_installations_silent = "";
		foreach ($totals['S'] as $edition => $t) {
			$total_installations_silent .= "{$edition}:{$t} ";
		}

		$total_installations_live = "";
		foreach ($totals['L'] as $edition => $t) {
			$total_installations_live .= "{$edition}:{$t} ";
		}

		$active_res = mysql_query("SELECT SUM(users) AS total FROM sugar_installations WHERE status = 'A'", $db)
			or handle_error(mysql_error(), TRUE);

		$active_row = mysql_fetch_assoc($active_res);

		mysql_query("INSERT INTO sugar_hb_stats (month, total_systems, total_live_systems, total_active_systems,
			total_users_active_systems, total_silent_systems) VALUES
			('{$current_month}', '{$installation_total}', '{$total_installations_live}',
			'{$total_installations_active}', '{$active_row['total']}', '{$total_installations_silent}')", $db)
			or handle_error(mysql_error(), TRUE);

		$current_month = substr($queue_row['time_stamp'], 0, 10);
	}
	// END REPORTING CODE

	// BEGIN jostrow -- ITRequest #2295

	// see the definition of merged_ip_check() in core.php for an explanation of what we're doing here
	$merged_ip = merged_ip_check($queue_row['soap_client_ip']);

	if ($merged_ip !== FALSE) {
		$queue_row['soap_client_ip'] = $merged_ip;
	}

	// END jostrow

	// can the heartbeat be linked to a new installation, or should an installation be created?
	$installation_id = find_installation($queue_row['application_key'], $queue_row['soap_client_ip']);

	// SADEK 11/29/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
	$create_sugar_updates_data = array();
	foreach ($sugar_updates_cols as $col) {
		if($col == "installation_id")
			continue;
		
		if (isset($queue_row[$col]) || is_null($queue_row[$col])) {
			$create_sugar_updates_data[$col] = $queue_row[$col];
		}
		else {
			handle_error("Warning: {$col} does not exist in queue_row when trying to create sugar update", FALSE);
		}
	}
	// SADEK 11/29/07: END ADDITIONAL CODE FOR UPDATE SCRIPT
		
	// no matching installation found-- create new installation
	if ($installation_id === FALSE) {
		$create_data = array();
		$create_data['date_created'] = $create_data['last_touch'] = $queue_row['time_stamp'];
		foreach ($sugar_installation_cols as $col) {
			if (isset($queue_row[$col]) || is_null($queue_row[$col])) {
				$create_data[$col] = $queue_row[$col];
			}
			else {
				handle_error("Warning: {$col} does not exist in queue_row when trying to create installation", FALSE);
			}
		}
		
		// SADEK 12/05/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
		$create_data['update_count'] = '1';
		// SADEK 12/05/07: END ADDITIONAL CODE FOR UPDATE SCRIPT
		
		$installation_id = create_installation($create_data);

		$queue_row['installation_id'] = $installation_id;
		// SADEK 11/29/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
		$create_sugar_updates_data['installation_id'] = $installation_id;
		archive_heartbeat($create_sugar_updates_data);
		// SADEK 11/29/07: END ADDITIONAL CODE FOR UPDATE SCRIPT

		update_installation_status($installation_id, STATUS_LIVE);
	}
	else {
		$queue_row['installation_id'] = $installation_id;
		// SADEK 11/29/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
		$create_sugar_updates_data['installation_id'] = $installation_id;
		archive_heartbeat($create_sugar_updates_data);
		// SADEK 11/29/07: END ADDITIONAL CODE FOR UPDATE SCRIPT

		$update_data = array();
		$update_data['last_touch'] = $queue_row['time_stamp'];
		foreach ($sugar_installation_cols as $col) {
			$update_data[$col] = $queue_row[$col];
		}
		
		// SADEK 12/05/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
		$update_data['update_count'] = 'update_count+1';
		// SADEK 12/05/07: END ADDITIONAL CODE FOR UPDATE SCRIPT
		touch_installation($installation_id, $update_data);

		// SADEK 02/29/08: BEGIN OPTIMIZATION OF THIS QUERY
		// get old heartbeats to use for calculating tracker hits/day average
		// $archive_res = xmysql_query("SELECT time_stamp, latest_tracker_id FROM sugar_updates
		//	WHERE installation_id = '{$installation_id}' ORDER BY time_stamp DESC LIMIT {$delta_limit}", $db) or handle_error(mysql_error(), TRUE);
		$archive_res = xmysql_query("SELECT time_stamp, latest_tracker_id FROM sugar_updates
			WHERE installation_id = '{$installation_id}' and time_stamp > DATE_SUB(CURDATE(),INTERVAL 30 DAY) ORDER BY time_stamp DESC LIMIT {$delta_limit}", $db) or handle_error(mysql_error(), TRUE);
		// SADEK 02/29/08: END OPTIMIZATION OF THIS QUERY

		$archive_row = mysql_fetch_assoc($archive_res);

		$time_stamp_baseline = $archive_row['time_stamp'];
		$latest_tracker_id_baseline = $archive_row['latest_tracker_id'];

		$deltas = array($archive_row);
		while ($archive_row = mysql_fetch_assoc($archive_res)) {
			// if the current heartbeat occured three days before the baseline AND the latest_tracker_id has not been
			// increased, add this heartbeat to the list of deltas and reset the baselines
			if (
				(strtotime($time_stamp_baseline) - strtotime($archive_row['time_stamp']) > SECONDS_3_DAYS)
				&& ($latest_tracker_id_baseline > $archive_row['latest_tracker_id'])
			) {
				$deltas[] = $archive_row;

				$time_stamp_baseline = $archive_row['time_stamp'];
				$latest_tracker_id_baseline = $archive_row['latest_tracker_id'];
			}

			// if we've retrieved four heartbeats to be used as deltas, no more are needed
			if (count($deltas) == 4) {
				break;
			}
		}

		if (calc_delta_average($deltas) >= $active_clicks_per_day) {
			update_installation_status($installation_id, STATUS_ACTIVE);
		}
		else {
			update_installation_status($installation_id, STATUS_LIVE);
		}
	}

	link_installation_to_account($installation_id, $queue_row['license_key']);

	set_latest_processed_id($queue_row['id']);

	unset($installation_id, $new_installation_id, $archive_res, $archive_row);
	unset($deltas, $time_stamp_baseline, $latest_tracker_id_baseline);
	unset($queue_row);
}

handle_runtime("Processed " . mysql_num_rows($queue_res) . " heartbeats in " . get_runtime());
?>
