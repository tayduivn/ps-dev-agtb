<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: TasksListView.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/





require_once('include/ListView/ListViewSmarty.php');
if(file_exists('custom/modules/Calendar/metadata/listviewdefs.php')){
	require_once('custom/modules/Calendar/metadata/listviewdefs.php');
}else{
	require_once('modules/Calendar/metadata/listviewdefs.php');
}


global $mod_strings;
global $app_strings;
global $app_list_strings;

global $urlPrefix;


global $currentModule;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Tasks');


global $theme;

// clear the display columns back to default when clear query is called
if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true')
    $current_user->setPreference('ListViewDisplayColumns', array(), 0, 'Calendar');

$savedDisplayColumns = $current_user->getPreference('ListViewDisplayColumns', 'Calendar'); // get user defined display columns

$json = getJSONobj();
$seedTask = new Task();

// setup listview smarty
$lv = new ListViewSmarty();

$displayColumns = array();
// check $_REQUEST if new display columns from post
if(!empty($_REQUEST['displayColumns'])) {
    foreach(explode('|', $_REQUEST['displayColumns']) as $num => $col) {
        if(!empty($listViewDefs['Tasks'][$col]))
            $displayColumns[$col] = $listViewDefs['Tasks'][$col];
    }
}
elseif(!empty($savedDisplayColumns)) { // use user defined display columns from preferences
    $displayColumns = $savedDisplayColumns;
}
else { // use columns defined in listviewdefs for default display columns
    foreach($listViewDefs['Calendar'] as $col => $params) {
        if(!empty($params['default']) && $params['default'])
            $displayColumns[$col] = $params;
    }
}
$params = array('massupdate' => false); // setup ListViewSmarty params
if(!empty($_REQUEST['orderBy'])) { // order by coming from $_REQUEST
    $params['orderBy'] = $_REQUEST['orderBy'];
    $params['overrideOrder'] = true;
    if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
}
$params['orderBy'] = '';
$lv->displayColumns = $displayColumns;

// use the stored query if there is one
if (!isset($where)) $where = "";
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
    $storeQuery->loadQuery('Calendar');
    $storeQuery->populateRequest();
}else{
    $storeQuery->saveFromGet('Calendar');
}
global $timedate;

//jc: bug 14616 - dates need to specificy the end of the current date in order to get tasks
// that are scheduled to start today
$today = $timedate->getNow(true)->get_day_end_time()->asDb();
//end bug 14616

$where = "(tasks.assigned_user_id='$current_user->id' and tasks.status<>'Completed' and tasks.status<>'Deferred'";
$where .= "and (tasks.date_start is NULL or tasks.date_start <= '$today'))";

$lv->export = false;
$lv->delete = false;
$lv->select = false;
$lv->mailMerge = false;
$lv->multiSelect = false;
$lv->showMassupdateFields = false;
$lv->setup($seedTask, 'include/ListView/ListViewNoMassUpdate.tpl', $where, $params);
echo getClassicModuleTitle($current_module_strings['LBL_MODULE_NAME'], array($current_module_strings['LBL_LIST_FORM_TITLE']), false);


echo $lv->display();
//Fake Mass Update
$form = "<form action='index.php' id='MassUpdate' method='post' name='MassUpdate'><input type='hidden' id='uid' name='uid'><input name='action' type='hidden' value='index' /><input name='module' type='hidden' value='Project'></form>";
echo $form;
?>
