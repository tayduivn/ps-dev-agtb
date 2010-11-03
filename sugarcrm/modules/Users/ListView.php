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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('include/ListView/ListViewSmarty.php');
if(file_exists('custom/modules/Users/metadata/listviewdefs.php')){
	require_once('custom/modules/Users/metadata/listviewdefs.php');	
}else{
	require_once('modules/Users/metadata/listviewdefs.php');
}

require_once('include/SearchForm/SearchForm.php');


global $mod_strings;
global $app_strings;
global $app_list_strings;

global $urlPrefix;


global $currentModule;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Users');


global $theme;

// only admin is authorized.
if (!is_admin($current_user)&& !is_admin_for_module($GLOBALS['current_user'],'Users')) sugar_die("Unauthorized access to administration.");

// clear the display columns back to default when clear query is called
if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true')  
    $current_user->setPreference('ListViewDisplayColumns', array(), 0, 'Users');

$savedDisplayColumns = $current_user->getPreference('ListViewDisplayColumns', 'Users'); // get user defined display columns

$json = getJSONobj();

$seedUser = new User();
$thisMod = 'Users';
$searchForm = new SearchForm($thisMod, $seedUser); // new searchform instance
$searchForm->tabs = array(array('title'  => $app_strings['LNK_BASIC_SEARCH'],
                          'link'   => $thisMod . '|basic_search',
                          'key'    => $thisMod . '|basic_search'),
                    array('title'  => $app_strings['LNK_ADVANCED_SEARCH'],
                          'link'   => $thisMod . '|advanced_search',
                          'key'    => $thisMod . '|advanced_search'));

// setup listview smarty
$lv = new ListViewSmarty();
if(isset($_REQUEST['Users2_USER_offset'])) {//if you click the pagination button, it will poplate the search criteria here
    if(!empty($_REQUEST['current_query_by_page'])) {//The code support multi browser tabs pagination
    
        $blockVariables = array('mass', 'uid', 'massupdate', 'delete', 'merge', 'selectCount', 'request_data', 'current_query_by_page','Users2_USER_ORDER_BY');
        if(isset($_REQUEST['lvso'])){
        	$blockVariables[] = 'lvso';
        }
        $current_query_by_page = unserialize(base64_decode($_REQUEST['current_query_by_page']));
        foreach($current_query_by_page as $search_key=>$search_value) {
            if($search_key != 'Users2_USER_offset' && !in_array($search_key, $blockVariables)) {
				if (!is_array($search_value)) {
                	$_REQUEST[$search_key] = $GLOBALS['db']->quoteForEmail($search_value);
				}
                else {
            		foreach ($search_value as $key=>&$val) {
            			$val = $GLOBALS['db']->quoteForEmail($val);
            		}
            		$_REQUEST[$search_key] = $search_value;
                }                
            }
        }
    }
}
if(!empty($_REQUEST['saved_search_select']) && $_REQUEST['saved_search_select']!='_none') {
    if(empty($_REQUEST['button']) && (empty($_REQUEST['clear_query']) || $_REQUEST['clear_query']!='true')) {
        $saved_search = loadBean('SavedSearch');
        $saved_search->retrieveSavedSearch($_REQUEST['saved_search_select']);
        $saved_search->populateRequest();
    }
    elseif(!empty($_REQUEST['button'])) { // click the search button, after retrieving from saved_search
        $_SESSION['LastSavedView'][$_REQUEST['module']] = '';
        unset($_REQUEST['saved_search_select']);
        unset($_REQUEST['saved_search_select_name']);
    }
}
        
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
    $storeQuery->loadQuery('Users');
    $storeQuery->populateRequest();
}else{
    $storeQuery->saveFromRequest('Users');   
}

$displayColumns = array();
// check $_REQUEST if new display columns from post
if(!empty($_REQUEST['displayColumns'])) {
    foreach(explode('|', $_REQUEST['displayColumns']) as $num => $col) {
        if(!empty($listViewDefs['Users'][$col])) 
            $displayColumns[$col] = $listViewDefs['Users'][$col];
    }    
}
elseif(!empty($savedDisplayColumns)) { // use user defined display columns from preferences 
    $displayColumns = $savedDisplayColumns;
}
else { // use columns defined in listviewdefs for default display columns
    foreach($listViewDefs['Users'] as $col => $params) {
        if(!empty($params['default']) && $params['default'])
            $displayColumns[$col] = $params;
    }
} 
$params = array('massupdate' => true); // setup ListViewSmarty params
if(!empty($_REQUEST['orderBy'])) { // order by coming from $_REQUEST
    $params['orderBy'] = $_REQUEST['orderBy'];
    $params['overrideOrder'] = true;
    if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
}
$lv->displayColumns = $displayColumns;
$lv->delete= false;
if(!empty($_REQUEST['search_form_only']) && $_REQUEST['search_form_only']) { // handle ajax requests for search forms only
    switch($_REQUEST['search_form_view']) {
        case 'basic_search':
            $searchForm->setup();
            $searchForm->displayBasic(false);
            break;
        case 'advanced_search':
            $searchForm->setup();
            $searchForm->displayAdvanced(false, false, $listViewDefs, $lv);
            break;
        case 'saved_views':
            echo $searchForm->displaySavedViews($listViewDefs, $lv, false);
            break;
    }
    return;
}

// use the stored query if there is one
if (!isset($where)) $where = "";

if(isset($_REQUEST['query']))
{
    // we have a query
    // first save columns 
    $current_user->setPreference('ListViewDisplayColumns', $displayColumns, 0, 'Users'); 
    if(!empty($_SERVER['HTTP_REFERER']) && preg_match('/action=EditView/', $_SERVER['HTTP_REFERER'])) { // from EditView cancel
        $searchForm->populateFromArray($storeQuery->query);
    }
    else {
        $searchForm->populateFromRequest();
    }
    $where_clauses = $searchForm->generateSearchWhere(true, "Users"); // builds the where clause from search field inputs
    if (count($where_clauses) > 0 )$where = '('. implode(' ) AND ( ', $where_clauses) . ')';
    $GLOBALS['log']->info("Here is the where clause for the list view: $where");
}

// start display
// which tab of search form to display
if(!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
    $searchForm->setup();
    if(isset($_REQUEST['searchFormTab']) && $_REQUEST['searchFormTab'] == 'advanced_search') {
        $searchForm->displayAdvanced(true, false, $listViewDefs, $lv);
    }else {
        $searchForm->displayBasic();
    }
}

$lv->setup($seedUser, 'include/ListView/ListViewGeneric.tpl', $where, $params);

$savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
echo get_form_header($current_module_strings['LBL_LIST_FORM_TITLE'] . $savedSearchName, '', false);

//Module administrators with user management can't update any user to "system administrator" with massupdate
if(!is_admin($GLOBALS['current_user']) && is_admin_for_module($GLOBALS['current_user'],'Users'))$lv->lvd->seed->field_defs['is_admin']['massupdate']='';

echo $lv->display();

$savedSearch = new SavedSearch();
$json = getJSONobj();
// fills in saved views select box on shortcut menu
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('Users')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);\n";

if(!empty($current_user) && is_admin($current_user)) {
    $str .= "var current_admin_id = '".$current_user->id."';";
}
$str .= "</script>";
echo $str;

?>
