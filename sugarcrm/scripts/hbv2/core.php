<?php
/*
** @author: Julian Ostrow, Sadek Baroudi
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (unknown)
** Description: Acts as a helper file for process_hb_queue.php and set_silent.php
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Hbv2/Core.php
*/

/***** CONFIGURATION VARIABLES *****/
$db_host = 'si-db1';
$db_user = 'sugarinternal';
$db_pass = 'rI3pSTukiD6D';
$db_name = 'sugarinternal';

$remote_db_host = 'license-web1'; // online-updates
$remote_db_user = 'internalbeat';
$remote_db_pass = 'jt45ggg3';
$remote_db_name = 'sugarbeat';

$log_err_path = "/var/www/sugarinternal/logs/hbsync_errors.log";
$log_run_path = "/var/www/sugarinternal/logs/hbsync_runtimes.log";
$log_lastprocessed_path = "/var/www/sugarinternal/logs/last_processed.log";

$process_limit = 2000; // limits of number of heartbeats in queue to process during one runtime
$delta_limit = 1000; // limits of number of heartbeats to use when determining the status of an installation
$active_clicks_per_day = 3; // the number of tracker hits per day required to deem an installation 'Active'

define("MAX_LEN_IP", 15); // maximum number of characters the 'soap_client_ip' column in the 'installations' table can hold
define("MAX_LEN_KEY", 64); // maximum number of characters the 'application_key' column in the 'installations' table can hold

define("STATUS_ACTIVE", "A");
define("STATUS_SILENT", "S");
define("STATUS_LIVE", "L");

define("SECONDS_3_DAYS", 259200);

// BEGIN jostrow -- ITRequest #2295

$ondemand_ips = array(
	'10.13.20.128',
	'10.13.20.129',
	'10.13.20.130',
	'10.13.20.131',
	'10.13.20.132',
	'10.13.20.133',
	'10.13.20.134',
	'10.13.20.135',
	'10.13.20.136',
	'10.13.20.137',
	'10.13.20.138',
	'10.13.20.139',
	'10.13.20.140',
	'10.13.20.141',
	'10.13.20.142',
	'10.13.20.143',
	'10.13.20.144',
	'10.13.20.145',
);

// all of the above ondemand_ips will be merged/set to this one; this is to avoid
// multiple Sugar Installations being created in Sugar Internal
$ondemand_merge_ip = '10.13.20.137';

// END jostrow

$sugar_installation_cols = array(
	'application_key',
	'soap_client_ip',
	'sugar_flavor',
	'sugar_version',
	'users',
	'ip_address',
	'sugar_db_version',
	'db_type',
	'db_version',
	'admin_users',
	'registered_users',
	'users_active_30_days',
	'latest_tracker_id',
	'license_users',
	'license_expire_date',
	'license_key',
	'php_version',
	'license_num_lic_oc',
	'server_software',
	'conflict',
	'auth_level',
	'users_elm',
	'oc_active_30_days',
	'oc_active',
	'oc_all',
	'oc_br_all',
	'oc_br_active_30_days',
	'oc_br_active',
	'license_portal_ex',
	'license_portal_max',
	'license_num_portal_users',
	'system_name',
	'is_depot',
	'license_portal_count',
	'os',
	'os_version',
	'distro_name',
	'timezone',
	'timezone_u',
);

// SADEK 11/29/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
$sugar_updates_cols = array(
	'id',
	'time_stamp',              
	'application_key',         
	'ip_address',              
	'sugar_version',           
	'sugar_db_version',        
	'sugar_flavor',            
	'db_type',                 
	'db_version',              
	'users',                   
	'admin_users',             
	'registered_users',        
	'users_active_30_days',
	'latest_tracker_id',       
	'license_users',           
	'license_expire_date',     
	'license_key',             
	'soap_client_ip',          
	'php_version',             
	'license_num_lic_oc',      
	'server_software',         
	'conflict',                
	'auth_level',              
	'users_elm',               
	'oc_active_30_days',
	'oc_active',               
	'oc_all',                  
	'oc_br_all',               
	'oc_br_active_30_days',
	'oc_br_active',            
	'license_portal_ex',       
	'license_portal_max',      
	'license_num_portal_users',
	'system_name',             
	'is_depot',                
	'license_portal_count',
	'installation_id',
	'os',
	'os_version',
	'distro_name',
	'timezone',
	'timezone_u',
);
// SADEK 11/29/07: END ADDITIONAL CODE FOR UPDATE SCRIPT

/***** BEGIN INITIALIZATION *****/
set_time_limit(0);
$start_time = microtime_float();
/***** END INITIALIZATION *****/

/***** FUNCTION DECLARATIONS *****/

/** BEGIN UTIL FUNCTIONS **/
function handle_error($str, $fatal = TRUE) {
        global $log_err_path;

        return write_log(__FUNCTION__, $log_err_path, $str, $fatal);
}

function handle_runtime($str) {
        global $log_run_path;

        return write_log(__FUNCTION__, $log_run_path, $str, FALSE);
}

function write_log($calling_function, $log_file, $str, $fatal) {
        $caller_details = get_backtrace($calling_function);

        $fp = fopen($log_file, "ab");
        fwrite($fp, create_log_prefix($caller_details) . $str . "\n");
        fclose($fp);

        if ($fatal) {
                die();
        }

        return TRUE;
}

function get_backtrace($calling_function) {
        $backtrace = debug_backtrace();

        for ($i = 1; $i < count($backtrace); $i++) {
                if ($backtrace[$i]['function'] == $calling_function) {
                        $out['file'] = basename($backtrace[$i]['file']);
                        $out['line'] = $backtrace[$i]['line'];
                        $out['function'] = (count($backtrace) == $i + 1) ? "main" : $backtrace[$i + 1]['function'];
                }
        }

        return $out;
}

function create_log_prefix($caller_details) {
        global $start_time;

        return date("[Y-m-d H:i:s]") . " {$caller_details['file']}:{$caller_details['line']} {$caller_details['function']}() -- ";
}

function get_runtime() {
        global $start_time;

        return round(microtime_float() - $start_time, 1) . "s";
}

function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
}

function add_quotes($str) {
        return "'" . $str . "'";
}

// BEGIN jostrow -- ITRequest #2295

// giving this function a generic name for now-- we may decide to merge other IPs together later
// right now we're only merging OnDemand IPs -- demo/eval/training systems could come later
// this function returns FALSE is the given IP is not in the merge list
// if the given IP is in the merge list, it will return the correct merge IP to use
function merged_ip_check($ip) {
	global $ondemand_ips, $ondemand_merge_ip;

	if (in_array($ip, $ondemand_ips)) {
		return $ondemand_merge_ip;
	}

	return FALSE;
}

// END jostrow

/** END UTIL FUNCTIONS **/

/** BEGIN QUEUE PROCESSING FUNCTIONS **/
function find_installation($app_key, $soap_client_ip) {
        global $db;

        $app_key = mysql_real_escape_string(substr($app_key, 0, MAX_LEN_KEY), $db);
        $soap_client_ip = mysql_real_escape_string(substr($soap_client_ip, 0, MAX_LEN_IP), $db);

        $res = xmysql_query("SELECT id FROM sugar_installations
        	WHERE application_key = '{$app_key}' AND soap_client_ip = '{$soap_client_ip}'", $db) or handle_error(mysql_error(), TRUE);

        if (mysql_num_rows($res) >= 1) {
                $row = mysql_fetch_assoc($res);
                return $row['id'];
        }

        return FALSE;
}

function create_installation($data) {
	global $db;

	foreach ($data as $col => $value) {
		$statements[] = "{$col} = '" . mysql_real_escape_string($value, $db) . "'";
	}

	xmysql_query("INSERT INTO sugar_installations SET " . implode(", ", $statements), $db) or handle_error(mysql_error(), TRUE);

        return mysql_insert_id($db);
}

function archive_heartbeat($data) {
        global $db;

        $fields = array_keys($data);
        $data = array_map("mysql_real_escape_string", $data);
        $data = array_map("add_quotes", $data);

        $fields_list = implode(", ", $fields);
        $values_list = implode(", ", $data);

        mysql_query("INSERT INTO sugar_updates ({$fields_list}) VALUES ({$values_list})", $db) or handle_error(mysql_error(), TRUE);

}

function update_installation_status($installation_id, $status) {
        global $db;

        $res = xmysql_query("UPDATE sugar_installations SET status = '{$status}' WHERE id = '{$installation_id}'", $db)
                or handle_error(mysql_error(), TRUE);
}

function calc_delta_average($deltas) {
        if (count($deltas) == 1) {
                return -1;
        }

        for ($i = 0; $i < count($deltas) - 1; $i++) {
                $avgs[] = ($deltas[$i]['latest_tracker_id'] - $deltas[$i + 1]['latest_tracker_id']) /
                        ((strtotime($deltas[$i]['time_stamp']) - strtotime($deltas[$i + 1]['time_stamp'])) / 86400);
        }

        return array_sum($avgs) / count($avgs);
}

function touch_installation($installation_id, $data) {
        global $db;

        foreach ($data as $col => $value) {
				$statement = "{$col} = '" . mysql_real_escape_string($value, $db) . "'";
				// SADEK 12/05/07: BEGIN ADDITIONAL CODE FOR UPDATE SCRIPT
				if($col == 'update_count')
					$statement = "{$col} = {$value}";
				// SADEK 12/05/07: END ADDITIONAL CODE FOR UPDATE SCRIPT
                $statements[] = $statement;
        }

	xmysql_query("UPDATE sugar_installations SET " . implode(", ", $statements) . " WHERE id = '{$installation_id}'", $db)
		or handle_error(mysql_error(), TRUE);
}

function link_installation_to_account($installation_id, $license_key) {
        global $db;

        if (!empty($license_key)) {
                $license_key = mysql_real_escape_string($license_key, $db);
				
				// SADEK - 2008-04-03 - CHANGED THE QUERY TO RUN OFF OF THE SUBSCRIPTIONS TABLE INSTEAD OF THE DOWNLOAD_KEYS TABLE
                //$res = xmysql_query("SELECT account_id FROM download_keys WHERE download_key = '{$license_key}'", $db)
                $res = xmysql_query("SELECT account_id FROM subscriptions WHERE subscription_id = '{$license_key}'", $db)
                        or handle_error(mysql_error(), TRUE);

                if (mysql_num_rows($res) >= 1) {
                        $row = mysql_fetch_assoc($res);

                        xmysql_query("UPDATE sugar_installations SET account_id = '{$row['account_id']}' WHERE id = '{$installation_id}'", $db)
                                or handle_error(mysql_error(), TRUE);
                }
        }
}

function set_latest_processed_id($id) {
	global $log_lastprocessed_path;

	$fp = fopen($log_lastprocessed_path, "wb");
	fwrite($fp, $id);
	fclose($fp);
}

function get_latest_processed_id() {
	global $log_lastprocessed_path;

	return file_get_contents($log_lastprocessed_path);
}

function xmysql_query($sql, $db) {
	return mysql_query($sql, $db);
}
/** END QUEUE PROCESSING FUNCTIONS **/
?>
