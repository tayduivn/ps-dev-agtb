<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * The detailed view for a project
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

// $Id: DetailView.php 16705 2006-09-12 23:59:52 +0000 (Tue, 12 Sep 2006) jenny $

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('include/time.php');
require_once('modules/Project/Project.php');
require_once('include/DetailView/DetailView.php');

global $app_strings;
global $mod_strings;
global $theme;
global $current_user;

$GLOBALS['log']->info('Project Template detail view');
$focus = new Project();

// only load a record if a record id is given;
// a record id is not given when viewing in layout editor
$detailView = new DetailView();
$offset=0;
if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("PROJECT", $focus, $offset);
	if($result == null) {
	    sugar_die($app_strings['ERROR_NO_RECORD']);
	}
	$focus=$result;
} else {
	header("Location: index.php?module=Accounts&action=index");
}
echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'],
					  $mod_strings['LBL_PROJECT_TEMPLATE'] . ': ' . $focus->name, true);
echo "\n</p>\n";

$theme_path = 'themes/' . $theme . '/';
$image_path = $theme_path . 'images/';

require_once($theme_path.'layout_utils.php');

$xtpl = new XTemplate('modules/Project/ProjectTemplatesDetailView.html');

///
/// Assign the template variables
///

$xtpl->assign('MOD', $mod_strings);
$xtpl->assign('APP', $app_strings);
if(isset($_REQUEST['return_module']))
{
	$xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
}

if(isset($_REQUEST['return_action']))
{
	$xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
}

if(isset($_REQUEST['return_id']))
{
	$xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
}

$xtpl->assign('PRINT_URL', "index.php?".$GLOBALS['request_string']);
$xtpl->assign('THEME', $theme);
$xtpl->assign('GRIDLINE', $gridline);
$xtpl->assign('IMAGE_PATH', $image_path);
$xtpl->assign('id', $focus->id);
$xtpl->assign('name', $focus->name);
$xtpl->assign('status', $focus->status);
$xtpl->assign('start_date', $focus->estimated_start_date);
$xtpl->assign('end_date', $focus->estimated_end_date);
$xtpl->assign('priority', $focus->priority);
$xtpl->assign('assigned_user_name', $focus->assigned_user_name);
//$xtpl->assign('total_estimated_effort', $focus->total_estimated_effort);
//$xtpl->assign('total_actual_effort', $focus->total_actual_effort);
$xtpl->assign('description', nl2br(url2html($focus->description)));

$xtpl->assign('SAVE_AS', $mod_strings['LBL_SAVE_AS_PROJECT']);

if(is_admin($current_user)
	&& $_REQUEST['module'] != 'DynamicLayout'
	&& !empty($_SESSION['editinplace']))
{
	$xtpl->assign('ADMIN_EDIT',
		'<a href="index.php?action=index&module=DynamicLayout&from_action='
		. $_REQUEST['action'] . '&from_module=' . $_REQUEST['module']
		. '&record=' . $_REQUEST['record'] . '">'
		. get_image($image_path . 'EditLayout',
			 'border="0" alt="Edit Layout" align="bottom"') . '</a>');
}

$detailView->processListNavigation($xtpl, "PROJECT", $offset);
// adding custom fields
require_once('modules/DynamicFields/templates/Files/DetailView.php');

// BEGIN SUGARCRM flav=pro ONLY 
$xtpl->assign('team_name', $focus->team_name);
$xtpl->parse('main.pro');
/* comment out the non-pro code
// END SUGARCRM flav=pro ONLY 
$xtpl->parse('main.open_source');
// BEGIN SUGARCRM flav=pro ONLY 
*/
// END SUGARCRM flav=pro ONLY 

$xtpl->parse('main');
$xtpl->out('main');

$sub_xtpl = $xtpl;
$old_contents = ob_get_contents();
ob_end_clean();
ob_start();
echo $old_contents;

require_once('include/SubPanel/SubPanelTiles.php');
global $modules_exempt_from_availability_check;
$modules_exempt_from_availability_check = array('Holidays'=>'Holidays',
												'Calls'=>'Calls',
												'Meetings'=>'Meetings',
												'History'=>'History',
												'Notes'=>'Notes',
												'Emails'=>'Emails',
												'ProjectTask'=>'ProjectTask',
												'Users'=>'Users',
											   );
$subpanel = new SubPanelTiles($focus, 'ProjectTemplates');

echo $subpanel->display(true,true);

require_once('modules/SavedSearch/SavedSearch.php');
$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('Project')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;
?>