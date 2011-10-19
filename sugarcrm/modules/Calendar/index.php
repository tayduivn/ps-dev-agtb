<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

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
