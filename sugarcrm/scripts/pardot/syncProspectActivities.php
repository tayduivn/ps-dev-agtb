<?php
/**
 * This file updates a touchpoint based on a touchpoint id and prospect id
 */
ob_start();
chdir(dirname(__FILE__));
require_once('pardotApi.class.php');
require_once('pardotLogger.class.php');

chdir('../..');
define('sugarEntry', true);

// BEGIN jostrow customization
// Temporarily log memory consumption
require_once('scripts/jostrow_log_memory_usage.php');
// END jostrow customization


require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');

global $app_list_strings;
$app_list_strings = return_app_list_strings_language('en_us');

$current_user = new User();
$user_id = $current_user->retrieve_user_id('admin');
$current_user->retrieve($user_id);

$pardot_logger = new pardotLogger();

if (!empty($argv[1])) {
    $touchpoint_id = $argv[1];
}
$touchpoint = new Touchpoint();
$success = $touchpoint->retrieve($touchpoint_id);
if (!$success) {
	$log_data = array('touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'activity', 'success' => '0', 'message' => 'Could not retrieve touchpoint');
	$pardot_logger->writeLog($log_data);
    die(1);
}
if(empty($touchpoint->prospect_id_c)){
	$log_data = array('touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'activity', 'success' => '0', 'message' => 'Touchpoint has no prospect id');
	$pardot_logger->writeLog($log_data);
	die(1);
}

// Do all the activity updating
require_once('scripts/pardot/PardotHelper.php');
$update_success = PardotHelper::updateProspectActivities($touchpoint_id);

if ($update_success == 'success') {
	$log_data = array('pardot_id' => $touchpoint->prospect_id_c, 'touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'activity', 'success' => '1');
	$pardot_logger->writeLog($log_data);
} else {
	$log_data = array('pardot_id' => $touchpoint->prospect_id_c, 'touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'activity', 'success' => '0', 'message' => "PardotHelper::updateProspectActivities bool($update_success)");
	$pardot_logger->writeLog($log_data);
    die(1);
}

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
    echo $whatevs;
}


