<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Save functionality for ProjectTask
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: Save.php 16622 2006-09-05 23:03:50 +0000 (Tue, 05 Sep 2006) awu $
require_once('modules/ProjectTask/ProjectTask.php');

$project = new ProjectTask();
if(!empty($_POST['record']))
{
	$project->retrieve($_POST['record']);
}
////
//// save the fields to the ProjectTask object
////

if(isset($_REQUEST['email_id'])) $project->email_id = $_REQUEST['email_id'];

//if($_POST['order_number'] == '') $_POST['order_number'] = '1';

foreach($project->column_fields as $field)
{
	if(isset($_REQUEST[$field]))
	{
		$project->$field = $_REQUEST[$field];
	}

	if(!isset($_REQUEST['milestone_flag']))
	{
		$project->milestone_flag = '0';
	}
}
//$project->time_start = str_replace('.',':',$_REQUEST['time_start']);
//$project->time_due = str_replace('.',':',$_REQUEST['time_due']);
// Get GMT clean values
/*
if(!empty($_REQUEST['date_start']) && !empty($_REQUEST['time_start'])){
	$time_start_meridiem = "";
	if(isset($_REQUEST['time_start_meridiem'])){
		$time_start_meridiem = $_REQUEST['time_start_meridiem'];
	}
	
	$project->date_start = $_REQUEST['date_start'];
	$project->time_start = $_REQUEST['time_start'].$time_start_meridiem;
}
if(!empty($_REQUEST['date_due']) && !empty($_REQUEST['time_due'])){
	$time_due_meridiem = "";
	if(isset($_REQUEST['time_due_meridiem'])){
		$time_due_meridiem = $_REQUEST['time_due_meridiem'];
	}

	$project->date_due = $_REQUEST['date_due'];
	$project->time_due =  $_REQUEST['time_due'].$time_due_meridiem;
}

// lets SugarBean handle date processing
$project->process_save_dates = true;
*/
$GLOBALS['check_notify'] = false;
if (!empty($_POST['assigned_user_id']) && ($focus->assigned_user_id != $_POST['assigned_user_id']) && ($_POST['assigned_user_id'] != $current_user->id)) {
	$GLOBALS['check_notify'] = true;
}

	if(!$project->ACLAccess('Save')){
		ACLController::displayNoAccess(true);
		sugar_cleanup(true);
	}
$project->save($GLOBALS['check_notify']);
if(isset($_REQUEST['form']))
{
	// we are doing the save from a popup window
	echo '<script>opener.window.location.reload();self.close();</script>';
	die();
}
else
{
	// need to refresh the page properly

	$return_module = empty($_REQUEST['return_module']) ? 'ProjectTask'
		: $_REQUEST['return_module'];

	$return_action = empty($_REQUEST['return_action']) ? 'index'
		: $_REQUEST['return_action'];

	$return_id = empty($_REQUEST['return_id']) ? $project->id
		: $_REQUEST['return_id'];
header("Location: index.php?module=$return_module&action=$return_action&record=$return_id");

}
?>
