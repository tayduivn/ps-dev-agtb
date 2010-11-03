<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id$
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/DistGroups/DistGroup.php');
require_once('include/TimeDate.php');
require_once('include/DetailView/DetailView.php');
$timedate = new TimeDate();

global $mod_strings;
global $app_strings;

$focus = new DistGroup();
$detailView = new DetailView();
$offset = 0;
if (isset($_REQUEST['offset']) || isset($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("DISTGROUP", $focus, $offset);
	if($result == null) {
	    sugar_die("Error retrieving record.  You may not be authorized to view this record.");
	}
	$focus = $result;
}
else {
	header("Location: index.php?module=DistGroups&action=index");
}

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->name, true);
echo "\n</p>\n";

global $theme;
global $current_user;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$GLOBALS['log']->info("DistGroup detail view");

$xtpl=new XTemplate('modules/DistGroups/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("THEME", $theme);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("IMAGE_PATH", $image_path);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);

$xtpl->assign("ID", $focus->id);
$xtpl->assign("NAME", $focus->name);
$xtpl->assign("DATE_ENTERED", $focus->date_entered);
$xtpl->assign("DATE_MODIFIED", $focus->date_modified);
$xtpl->assign("CREATED_BY", $focus->created_by);
$xtpl->assign("CREATED_USER_NAME", $focus->created_user_name);
$xtpl->assign("ASSIGNED_USER_ID", $focus->assigned_user_id);
$xtpl->assign("ASSIGNED_USER_NAME", $focus->assigned_user_name);
$xtpl->assign("MODIFIED_USER_ID", $focus->modified_user_id);
$xtpl->assign("MODIFIED_USER_NAME", $focus->modified_user_name);
$xtpl->assign("CREATED_BY", $focus->created_by);
$xtpl->assign("TEAM_ID", $focus->team_id);
$xtpl->assign("TEAM_NAME", $focus->team_name);

$detailView->processListNavigation($xtpl, "DISTGROUP", $offset, $focus->is_AuditEnabled());

$xtpl->parse("main");
$xtpl->out("main");
$sub_xtpl = $xtpl;

require_once('include/SubPanel/SubPanelTiles.php');
$subpanel = new SubPanelTiles($focus, 'DistGroups');
echo $subpanel->display();

require_once('modules/SavedSearch/SavedSearch.php');
$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('DistGroups')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;
?>
