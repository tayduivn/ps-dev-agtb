<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * The detailed view for a ProjectTask
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

// $Id: DetailView.php 17070 2006-10-13 22:09:18 +0000 (Fri, 13 Oct 2006) awu $

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('include/time.php');
require_once('modules/ProjectTask/ProjectTask.php');
require_once('include/DetailView/DetailView.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $current_user;
global $theme;

$GLOBALS['log']->info("ProjectTask detail view");
$theme_path = "themes/$theme/";
$image_path = "{$theme_path}images/";
$focus = new ProjectTask();

$detailView = new DetailView();
$offset=0;
if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("PROJECT_TASK", $focus, $offset);
	if($result == null) {
	    sugar_die($app_strings['ERROR_NO_RECORD']);
	}
	$focus=$result;
} else {
	header("Location: index.php?module=Accounts&action=index");
}
echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'],
	$mod_strings['LBL_MODULE_NAME'].": ".$focus->name, true);
echo "\n</p>\n";

require_once("{$theme_path}layout_utils.php");

$xtpl = new XTemplate('modules/ProjectTask/DetailView.html');

if (isset($_REQUEST['return_module'])) $xtpl->assign('return_module', $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign('return_action', $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign('return_id', $_REQUEST['return_id']);
$xtpl->assign('MOD', $mod_strings);
$xtpl->assign('APP', $app_strings);
$xtpl->assign('THEME', $theme);
$xtpl->assign('GRIDLINE', $gridline);
$xtpl->assign('IMAGE_PATH', $image_path);
$xtpl->assign('PRINT_URL', "index.php?".$GLOBALS['request_string']);
$xtpl->assign('id', $focus->id);
$xtpl->assign('name', $focus->name);
$xtpl->assign('assigned_user_name', $focus->assigned_user_name);
$xtpl->assign('actual_duration', $focus->actual_duration);
$xtpl->assign('duration_unit', $app_list_strings['project_duration_units_dom'][$focus->duration_unit]);

//$xtpl->assign('status', $app_list_strings['project_task_status_options'][$focus->status]);
//$xtpl->assign('date_due', $focus->date_due);
//$xtpl->assign('time_due', $focus->time_due);
$xtpl->assign('date_start', $focus->date_start);
//$xtpl->assign('time_start', $focus->time_start);
//$xtpl->assign('parent_id', $focus->parent_id);
$xtpl->assign('parent_name', $focus->parent_name);
//$xtpl->assign('priority', $app_list_strings['project_task_priority_options'][$focus->priority]);
/*
$xtpl->assign('task_number', $focus->task_number);
$xtpl->assign('depends_on_id', $focus->depends_on_id);
$xtpl->assign('depends_on_name', $focus->depends_on_name);
$xtpl->assign('order_number', $focus->order_number);
*/
$xtpl->assign('project_name', $focus->project_name);
$xtpl->assign('project_id', $focus->project_id);
$xtpl->assign('task_number', $focus->project_task_id);
$xtpl->assign('priority', $focus->priority);
$xtpl->assign('duration', $focus->duration);
$xtpl->assign('start_date', $focus->date_start);
$xtpl->assign('end_date', $focus->date_finish);
$xtpl->assign('resource', $focus->getResourceName());
if (!empty($focus->priority))
    $xtpl->assign('priority', $app_list_strings['project_task_priority_options'][$focus->priority]);
else
    $xtpl->assign('priority', $app_list_strings['project_task_priority_default']);

if(!empty($focus->milestone_flag) && $focus->milestone_flag == '1')
{
    $xtpl->assign('milestone_checked', 'checked="checked"');
}
if(empty($focus->percent_complete)) {
    $xtpl->assign('percent_complete', '0');
}
else 
    $xtpl->assign('percent_complete', $focus->percent_complete);

//$xtpl->assign('estimated_effort', $focus->estimated_effort);
$xtpl->assign('actual_effort', $focus->actual_effort);
//$xtpl->assign('utilization', $focus->utilization);
$xtpl->assign('percent_complete', $focus->percent_complete);
$xtpl->assign('description', nl2br(url2html($focus->description)));

if(is_admin($current_user)
	&& $_REQUEST['module'] != 'DynamicLayout'
	&& !empty($_SESSION['editinplace']))
{
	$xtpl->assign('ADMIN_EDIT',
		"<a href='index.php?action=index&module=DynamicLayout&from_action="
			.$_REQUEST['action']
			."&from_module=".$_REQUEST['module'] ."&record="
			.$_REQUEST['record']. "'>"
			.get_image($image_path."EditLayout",
				"border='0' alt='Edit Layout' align='bottom'")."</a>");
}

$detailView->processListNavigation($xtpl, "PROJECT_TASK", $offset, $focus->is_AuditEnabled());
// adding custom fields:
require_once('modules/DynamicFields/templates/Files/DetailView.php');

// BEGIN SUGARCRM flav=pro ONLY 
$xtpl->assign('TEAM', $focus->team_name);
$xtpl->parse('main.pro');
/* comment out the non-pro code
// END SUGARCRM flav=pro ONLY 
$xtpl->parse("main.open_source");
// BEGIN SUGARCRM flav=pro ONLY 
*/
// END SUGARCRM flav=pro ONLY 
$xtpl->assign('TAG', $focus->listviewACLHelper());
$xtpl->parse('main');
$xtpl->out('main');

$sub_xtpl = $xtpl;
$old_contents = ob_get_contents();
ob_end_clean();
ob_start();
echo $old_contents;

require_once('include/SubPanel/SubPanelTiles.php');
$subpanel = new SubPanelTiles($focus, 'ProjectTask');
echo $subpanel->display();

require_once('modules/SavedSearch/SavedSearch.php');
$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('ProjectTask')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;

?>
