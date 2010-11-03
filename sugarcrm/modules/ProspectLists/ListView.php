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











$header_text = '';
global $app_strings;
global $app_list_strings;
global $current_language;
$current_module_strings = return_module_language($current_language, 'ProspectLists');

global $urlPrefix;


global $currentModule;

global $theme;

if (!isset($where)) $where = "";
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if($_REQUEST['action'] == 'index')
{
	if(!isset($_REQUEST['query'])){
		$storeQuery->loadQuery($currentModule);
		$storeQuery->populateRequest();
	}else{
		$storeQuery->saveFromGet($currentModule);	
	}
}
$seedProspectLists = new ProspectList();

if(isset($_REQUEST['query']))
{
	// we have a query
	if (isset($_REQUEST['name'])) $name = $_REQUEST['name'];
	if (isset($_REQUEST['current_user_only'])) $current_user_only = $_REQUEST['current_user_only'];
	if (isset($_REQUEST['assigned_user_id'])) $assigned_user_id = $_REQUEST['assigned_user_id'];
	if (isset($_REQUEST['list_type'])) $list_type = $_REQUEST['list_type'];

	$where_clauses = array();

	if(isset($name) && $name != "") array_push($where_clauses, "prospect_lists.name like '".$GLOBALS['db']->quote($name)."%'");	
	if(isset($current_user_only) && $current_user_only != "") array_push($where_clauses, "prospect_lists.assigned_user_id='$current_user->id'");
	if(!empty($list_type)) array_push($where_clauses, "prospect_lists.list_type like '".$GLOBALS['db']->quote($list_type)."%'");	

	$seedProspectLists->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	foreach($where_clauses as $clause)
	{
		if($where != "")
		$where .= " and ";
		$where .= $clause;
	}

	if (isset($assigned_user_id) && is_array($assigned_user_id))
	{
		$count = count($assigned_user_id);
		if ($count > 0 ) {
			if (!empty($where)) {
				$where .= " AND ";
			}
			$where .= "prospect_lists.assigned_user_id IN(";
			foreach ($assigned_user_id as $key => $val) {
				$where .= "'$val'";
				$where .= ($key == count($assigned_user_id) - 1) ? ")" : ", ";
			}
		}
	}
	$GLOBALS['log']->info("Here is the where clause for the list view: $where");
}

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/ProspectLists/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("ADVANCED_SEARCH_PNG", SugarThemeRegistry::current()->getImage('advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	$prospect_list_type_dom= array_merge(array(''=>''),$app_list_strings['prospect_list_type_dom']);
	if (!empty($list_type)) {
		$search_form->assign("LIST_OPTIONS", get_select_options_with_id($prospect_list_type_dom, $list_type));
	}
	else {
		$search_form->assign("LIST_OPTIONS", get_select_options_with_id($prospect_list_type_dom, ''));
	}

	if (isset($name)) $search_form->assign("NAME", $name);
	$search_form->assign("JAVASCRIPT", get_clear_form_js());

	if(isset($current_user_only)) $search_form->assign("CURRENT_USER_ONLY", "checked");
	if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
	}
	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);
	        // adding custom fields:
		$seedProspectLists->custom_fields->populateXTPL($search_form, 'search' );
		$search_form->parse("main");
		$search_form->out("main");
	echo "\n<BR>\n";
}


$ListView = new ListView();

if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
}
$ListView->initNewXTemplate( 'modules/ProspectLists/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE']. $header_text );
$ListView->setQuery($where, "", "name", "PROSPECT_LIST");
$ListView->processListView($seedProspectLists, "main", "PROSPECT_LIST");
?>
