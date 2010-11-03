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
$runaway_stop = 100;

$pardot_logger = new pardotLogger();

if (!empty($argv[1])) {
    $touchpoint_id = $argv[1];
}
$touchpoint = new Touchpoint();
$success = $touchpoint->retrieve($touchpoint_id);
if (!$success) {
	$log_data = array('touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'update', 'success' => '0', 'message' => 'Could not retrieve touchpoint');
	$pardot_logger->writeLog($log_data);
    die(1);
}

if (!empty($argv[2]) && is_numeric($argv[2]) && intval($argv[2])) {
    $requested_prospect_id = intval($argv[2]);
} else {
	$log_data = array('touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'update', 'success' => '0', 'message' => 'Bad prospect id passed in');
	$pardot_logger->writeLog($log_data);
    die(1);
}

$prospect = $pardot->getProspectById($requested_prospect_id);

if ($prospect) {
    # echo "Harvested 1 prospect\n";
    $touchpoint->populate_touchpoint_from_array($prospect->getTouchpointData());

    $touchpoint->save();

    /*
    ** @author: jwhitcraft
    ** SUGARINTERNAL CUSTOMIZATION
    ** ITRequest #: 14580
    ** Description: Update the email addresses to set the opt_out value if the email is in the system.
    */
    $params = $prospect->getTouchpointData();
    $GLOBALS['db']->query('UPDATE email_addresses SET opt_out = ' . $params['email_opt_out'] . ' WHERE email_address = "' . $params['email1'] . '";');

    /* END SUGARINTERNAL CUSTOMIZATION */

	$log_data = array('pardot_id' => $requested_prospect_id, 'touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'update', 'success' => '1');
	$pardot_logger->writeLog($log_data);
} else {
	$log_data = array('pardot_id' => $requested_prospect_id, 'touchpoint_id' => $touchpoint_id, 'pardot_action' => 'read',
						'sugar_action' => 'update', 'success' => '0', 'message' => 'Failed to retrieve prospect from pardot');
	$pardot_logger->writeLog($log_data);
    die(1);
}

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
    echo $whatevs;
}


