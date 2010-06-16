<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
/*********************************************************************************
 * $Id
 * Description:
 ********************************************************************************/
require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Forums/Forum.php');
require_once('modules/Forums/Forms.php');
require_once('include/DetailView/DetailView.php');

/* Removed this code since only admins can see the detailview anyway.
if(!ACLController::checkAccess('Forums', 'view', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}
*/

global $mod_strings;
global $app_strings;
global $app_list_strings;
global $current_user;
global $gridline;

$log = LoggerManager::getLogger('forum_detailview');

$focus = new Forum();
$detailView = new DetailView();
$offset=0;

if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
    $result = $detailView->processSugarBean("FORUM", $focus, $offset);
    if($result == null) {
        sugar_die($app_strings['ERROR_NO_RECORD']);
    }
    $focus=$result;
} else {
    header("Location: index.php?module=Forums&action=index");
}

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->title, true);
echo "\n</p>\n";

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
    $focus->id = "";
}

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$xtpl=new XTemplate ('modules/Forums/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("THEME", $theme);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("IMAGE_PATH", $image_path); $xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("RETURN_MODULE", "Forums");
$xtpl->assign("RETURN_ACTION", "DetailView");
$xtpl->assign("ACTION", "EditView");
$xtpl->assign("CREATED_BY", $focus->created_by);
$xtpl->assign("MODIFIED_BY", $focus->modified_user_id);
$xtpl->assign("CREATED_BY_USER", get_assigned_user_name($focus->created_by));
$xtpl->assign("MODIFIED_BY_USER", get_assigned_user_name($focus->modified_user_id));
$xtpl->assign("CATEGORY", $focus->category);
$xtpl->assign("CATEGORY_RANKING", $focus->category_ranking);
$xtpl->assign("DATE_ENTERED", $focus->date_entered);
$xtpl->assign("DATE_MODIFIED", $focus->date_modified);
// BEGIN SUGARCRM flav=pro ONLY 
$xtpl->assign("TEAM", $focus->assigned_name);
// END SUGARCRM flav=pro ONLY 

/*
//used to access most recent thread from html to display in listview
$xtpl->assign("MOST_RECENT_THREAD", $mostRecentThread['id']);
*/

$xtpl->assign("TITLE", $focus->title);
$xtpl->assign("DESCRIPTION", nl2br($focus->description));

//Add Custom Fields
require_once('modules/DynamicFields/templates/Files/DetailView.php');

$num_rows = $focus->db->getRowCount(
                $GLOBALS['db']->query(
                   "select * ".
                   "from threads ".
                   "where forum_id='".$focus->id."' ".
                   "and deleted=0"
                )
            );

if($num_rows == 0)
{
  $xtpl->parse("main.can_delete");
}

if(is_admin($current_user))
{
  $xtpl->parse("main");
  $xtpl->out("main");
}

print "<INPUT type=\"button\" class=\"button\" value=\"".$mod_strings['LBL_CREATE_THREAD']."\" name=\"createthread\" onClick=\"window.location='index.php?module=Threads&action=EditView&return_module=Forums&return_action=DetailView&return_id=".$focus->id."'\">";

/*
require_once('include/SubPanel/SubPanelTiles.php');
$subpanel = new SubPanelTiles($focus, 'Forums');
echo $subpanel->display();
*/

include('modules/Threads/ListView.php');

?>
