<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if(!ACLController::checkAccess('Calendar', 'list', true)){
	ACLController::displayNoAccess(true);
}

require_once('modules/Calendar/Calendar.php');
require_once('modules/Calendar/CalendarDisplay.php');
require_once("modules/Calendar/CalendarGrid.php");

global $cal_strings, $app_strings, $app_list_strings, $current_language, $timedate, $sugarConfig;
$cal_strings = return_module_language($current_language, 'Calendar');

if(empty($_REQUEST['view'])){
	$_REQUEST['view'] = SugarConfig::getInstance()->get('calendar.default_view','week');
}

$args = array();
$args['view'] = $_REQUEST['view'];
$args['cal'] = new Calendar($args['view']);


if($_REQUEST['view'] == 'day' || $_REQUEST['view'] == 'week' || $_REQUEST['view'] == 'month'){
	$args['cal']->add_activities($GLOBALS['current_user']);	
}else if($_REQUEST['view'] == 'shared'){
	$args['cal']->init_shared();	
	global $shared_user;				
	$shared_user = new User();	
	foreach($args['cal']->shared_ids as $member){
		$shared_user->retrieve($member);
		$args['cal']->add_activities($shared_user);
	}
}

if(in_array($args['cal']->view, array("day","week","month","shared"))){
	$args['cal']->load_activities();
}

$ed = new CalendarDisplay($args);
$ed->display_title();

if(in_array($args['cal']->view, array("day","week","month","shared","year"))){
	if($args['cal']->view == "shared")
		$ed->display_shared_html();
	$ed->display_calendar_header();
	$ed->display();
	$ed->display_calendar_footer();
}	

?>
