<?php
/**
 * This file creates a touchpoint based on a prospect id
 */

ob_start();
chdir(dirname(__FILE__));
require_once('pardotApi.class.php');
require_once('pardotLogger.class.php');

chdir('../..');
define('sugarEntry', true);



require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');

global $app_list_strings;
$app_list_strings = return_app_list_strings_language('en_us');

////////////////////////////////////
// Note: We will need to update the logic to assign to different users based on data later
////////////////////////////////////
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

if (!empty($argv[1]) && is_numeric($argv[1]) && intval($argv[1])) {
	$requested_prospect_id = intval($argv[1]);
} else {
	echo "Invalid prospect id.\n";
	echo "Usage: php insertProspect.php prospect_id\n";
	die(1);
}

$pardot_logger = new pardotLogger();

$prospect = $pardot->getProspectById($requested_prospect_id);
if (!$prospect) {
	$log_data = array('pardot_id' => $requested_prospect_id, 'pardot_action' => 'read', 
						'sugar_action' => 'insert', 'success' => '0', 'message' => 'Failed to retrieve prospect from pardot');
	$pardot_logger->writeLog($log_data);
	die(1);
}
if ($prospect) {
	$touchpoints_query = "
SELECT
	`touchpoints_cstm`.`id_c`,
	`touchpoints_cstm`.`prospect_id_c`
FROM
	`touchpoints_cstm`
WHERE
	`touchpoints_cstm`.`prospect_id_c` = $requested_prospect_id
";
	
	$ids_to_touchpoints = array();
	
	$res = $GLOBALS['db']->query($touchpoints_query);
	if (!$res) {
		/*
		 * If there is a problem with the query, quick early and make a lot of
		 * noise in the logs.
		 */
		$log_data = array('pardot_id' => $requested_prospect_id, 'pardot_action' => 'read', 
							'sugar_action' => 'insert', 'success' => '0', 'message' => 'Touchpoint query failed');
		$pardot_logger->writeLog($log_data);
		$GLOBALS['db']->checkError('Error with query');
		$GLOBALS['log']->fatal("----->siProspectsToTouchpoints.php query failed: " . $touchpoints_query);
		echo "Error with query\n";
		exit(1);
	} else {
		/*
		 * The query did not bomb, so put all of the opportunity ids into an
		 * array for future processing.
		 */
		while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
			$ids_to_touchpoints[$row['prospect_id_c']] = $row['id_c'];
		}
	}

	
	if (isset($ids_to_touchpoints[$requested_prospect_id])) {
		# echo "$requested_prospect_id already exists as " . $ids_to_touchpoints[$requested_prospect_id] . "\n";
	} elseif (empty($prospect->first_name) || empty($prospect->last_name)) {
		$log_data = array('pardot_id' => $requested_prospect_id, 'pardot_action' => 'read', 
							'sugar_action' => 'insert', 'success' => '0', 'message' => 'Prospect missing first or last name');
		$pardot_logger->writeLog($log_data);
	} else {
		// While creating this, we make sure the campaign ids exist for the activities
		$first_campaign = array();
		if(isset($prospect->visitor_activities)){
			foreach($prospect->visitor_activities as $activity_object){
				if(isset($activity_object->form_handler_id)){
					require_once('scripts/pardot/PardotHelper.php');
					$campaign_id = PardotHelper::verifyCampaignExistsOrCreate($activity_object->form_handler_id, $activity_object->details);
					
					$activity_unix_created = strtotime($activity_object->created_at);
					// If the first_campaign array isn't set, or it is set and the current campaign happened befored this one, we set the first_campaign
					if(empty($first_campaign) || $activity_unix_created < $first_campaign['unix_created']){
						$first_campaign['campaign_id'] = $campaign_id;
						$first_campaign['unix_created'] = $activity_unix_created;
					}
				}
			}
		}
		$touchpoint = new Touchpoint();
		$touchpoint->assigned_user_id = $assigned_user_id;
		$touchpoint->populate_touchpoint_from_array($prospect->getTouchpointData());
		if(!empty($first_campaign['campaign_id'])){
			$touchpoint->campaign_id = $first_campaign['campaign_id'];
		}

		//ER CUSTOMIZATION - ITREQUEST 12810 - integrate leadcapture functionality into insertProspect.php
		//populate array to pass in to process reassignment
	  	require_once('scripts/pardot/reassignTouchpointValues.php');
		$params = array();
		if(isset($prospect->user)) $params['user'] = $prospect->user;
		if(isset($prospect->company)) $params['company']= $prospect->company;
		if(isset($prospect->campaign_name)) $params['campaign_name']= $prospect->campaign_name;
		if(isset($prospect->campaign_id)) $params['campaign_id']= $prospect->campaign_id;
		if(isset($prospect->contactPartner)) $params['contactPartner']= $prospect->contactPartner;
		if(isset($prospect->Call_Back_c)) $params['Call_Back_c']= $prospect->Call_Back_c;
		if(isset($prospect->trial_name)) $params['trial_name']= $prospect->trial_name;
		if(isset($prospect->registered_eval_c)) $params['registered_eval_c']= $prospect->registered_eval_c;
		if(isset($prospect->first_name)) $params['first_name']= $prospect->first_name;
		if(isset($prospect->email)) $params['email1']= $prospect->email;

		//call function to process reassign touchpoint logic
		reassign_touchpoint($touchpoint, $params, false);

		$touchpoint->save();

        /*
        ** @author: jwhitcraft
        ** SUGARINTERNAL CUSTOMIZATION
        ** ITRequest #: 14580
        ** Description: Update the email addresses to set the opt_out value if the email is in the system.
        */
        $GLOBALS['db']->query('UPDATE email_addresses SET opt_out = ' . $params['email_opt_out'] . ' WHERE email_address = "' . $params['email1'] . '";');

        /* END SUGARINTERNAL CUSTOMIZATION */

		$log_data = array();
		if(!empty($touchpoint->id)){
			$log_data = array('pardot_id' => $requested_prospect_id, 'touchpoint_id' => $touchpoint->id, 'pardot_action' => 'read', 
								'sugar_action' => 'insert', 'success' => '1');
		}
		else{
			$log_data = array('pardot_id' => $requested_prospect_id, 'pardot_action' => 'read', 
								'sugar_action' => 'insert', 'success' => '0', 'message' => 'Sugar failed to save touchpoint record');
		}
		$pardot_logger->writeLog($log_data);
	}
}

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
	echo $whatevs;
}


