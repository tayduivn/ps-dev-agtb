<?php

ini_set('display_errors', false);

if(!defined('sugarEntry')) define('sugarEntry', true);

$valid_ips = array(
	'10.8.5.100',
	'10.8.5.101',
	'10.8.5.102',
	);
// 10.13.5.130 is the non-public IP address of www.sugarcrm.com
//if ( ((isset($_POST['key0'])) && ($_POST['key0'] == "80720ec5-f69c-b698-afac-475b58c00681")) && in_array($_SERVER['REMOTE_ADDR'],$valid_ips) ) {
if ( ((isset($_POST['key0'])) && ($_POST['key0'] == "80720ec5-f69c-b698-afac-475b58c00681"))  ) {

	// load needed files to process incoming Survey information
	require_once('include/entryPoint.php');
	require_once('modules/csurv_SurveyResponse/csurv_SurveyResponse.php');
	require_once('modules/Cases/Case.php');

	global $current_user;
	$current_user->retrieve('24467ba5-7e4d-69b2-27c7-4773065a9dca'); // This is the survey_response user, and has to belong to the team that the case or contact belongs to

	// setup a new Survey record

	$survey = new csurv_SurveyResponse();

	$_POST['contact_id'] = $_POST['key1'];
	$_POST['acase_id'] = $_POST['key2'];
	$_POST['assigned_user_id'] = $_POST['key3'];

	$survey->fromArray($_POST);

	$survey->team_id = "1";

	// Get the number of the case to insert into the name field of the survey
	$my_case = new aCase();
	$my_case->retrieve($survey->acase_id);

	$survey->name = "Survey for Case " . $my_case->case_number; 

	$survey->save();


	// clean up open connections, etc.
	sugar_cleanup();

	// Redirect back to the Thank You page
		header("HTTP/1.0 200");	
	}
	else {
		header("HTTP/1.0 444"); 
	}

?>


