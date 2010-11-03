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
 * $Id: ListView.php 19107 2007-01-06 00:58:36Z wayne $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('XTemplate/xtpl.php');
require_once("data/Tracker.php");
require_once('modules/DistGroups/DistGroup.php');
// REMOVED require theme file
require_once('include/ListView/ListViewSmarty.php');
if(file_exists('custom/modules/DistGroups/metadata/listviewdefs.php')){
	require_once('custom/modules/DistGroups/metadata/listviewdefs.php');	
}else{
	require_once('modules/DistGroups/metadata/listviewdefs.php');
}
require_once('modules/SavedSearch/SavedSearch.php');
require_once('include/SearchForm/SearchForm.php');


global $app_strings;
global $app_list_strings;
global $current_language;
$current_module_strings = return_module_language($current_language, 'DistGroups');
global $urlPrefix;
global $currentModule;
global $current_user;
global $theme;

require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();

// clear the display columns back to default when clear query is called
if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true')
    $current_user->setPreference('ListViewDisplayColumns', array(), 0, $currentModule);

$savedDisplayColumns = $current_user->getPreference('ListViewDisplayColumns', 'DistGroups'); // get user defined display columns

$json = getJSONobj();

$seedDistGroup = new DistGroup();
$searchForm = new SearchForm('DistGroups', $seedDistGroup);

// setup listview smarty
$lv = new ListViewSmarty();

$displayColumns = array();
if(!empty($_REQUEST['displayColumns'])) {
    foreach(explode('|', $_REQUEST['displayColumns']) as $num => $col) {
        if(!empty($listViewDefs['DistGroups'][$col])) 
            $displayColumns[$col] = $listViewDefs['DistGroups'][$col];
    }    
}
elseif(!empty($savedDisplayColumns)) { // use user defined display columns from preferences
    $displayColumns = $savedDisplayColumns;
}
else {
    foreach($listViewDefs['DistGroups'] as $col => $params) {
        if(!empty($params['default']) && $params['default'])
            $displayColumns[$col] = $params;
    }
} 
$params = array('massupdate' => true);
if(!empty($_REQUEST['orderBy'])) {
    $params['orderBy'] = $_REQUEST['orderBy'];
    $params['overrideOrder'] = true;
    if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
}

$lv->displayColumns = $displayColumns;

if(!empty($_REQUEST['search_form_only']) && $_REQUEST['search_form_only']) {
    switch($_REQUEST['search_form_view']) {
        case 'basic_search':
            $searchForm->setup();
            $searchForm->displayBasic(false);
            break;
        case 'advanced_search':
            $searchForm->setup();
            $searchForm->displayAdvanced(false);
            break;
        case 'saved_views':
            echo $searchForm->displaySavedViews($listViewDefs, $lv, false);
            break;
    }
    return;
}

if (!isset($where)) $where = "";

require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);	
}
if(isset($_REQUEST['query']))
{
    // we have a query
    if(!empty($_SERVER['HTTP_REFERER']) && preg_match('/action=EditView/', $_SERVER['HTTP_REFERER'])) { // from EditView cancel
        $searchForm->populateFromArray($storeQuery->query);
    }
    else {
        $searchForm->populateFromRequest();
    } 

    $where_clauses = $searchForm->generateSearchWhere($_REQUEST, true, "DistGroups");
    $where = "";
    if (count($where_clauses) > 0 )$where= implode(' and ', $where_clauses);
    $GLOBALS['log']->info("Here is the where clause for the list view: $where");
}

if(!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
    $searchForm->setup();
    if(isset($_REQUEST['searchFormTab']) && $_REQUEST['searchFormTab'] == 'advanced_search') {
        $searchForm->displayAdvanced();
    }
    elseif(isset($_REQUEST['searchFormTab']) && $_REQUEST['searchFormTab'] == 'saved_views'){
        $searchForm->displaySavedViews($listViewDefs, $lv);
    }
    else {
        $searchForm->displayBasic();
    }
}

echo $qsd->GetQSScripts();
$lv->setup($seedDistGroup, 'include/ListView/ListViewGeneric.tpl', $where, $params);

// display 
$savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
echo get_form_header($current_module_strings['LBL_LIST_FORM_TITLE'] . $savedSearchName, '', false);
echo $lv->display();

$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('DistGroups')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;

?>
