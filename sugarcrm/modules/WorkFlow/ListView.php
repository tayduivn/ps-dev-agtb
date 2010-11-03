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

 * Description:  
 ********************************************************************************/
global $app_strings, $app_list_strings, $current_language, $current_user;
$workflow_modules = get_workflow_admin_modules_for_user($current_user);
if (!is_admin($current_user) && empty($workflow_modules))
{
   sugar_die("Unauthorized access to WorkFlow.");
}









require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);	
}

$current_module_strings = return_module_language($current_language, 'WorkFlow');
$header_text = '';
global $currentModule;
global $urlPrefix;


echo get_module_title("WorkFlow", $mod_strings['LBL_MODULE_TITLE'], true); 
if (!isset($where)) $where = "";

$seed = new WorkFlow();
if(!empty($_REQUEST['query']))
{
	// we have a query
	if (!empty($_REQUEST['name_basic'])) $name = $_REQUEST['name_basic'];
	if (!empty($_REQUEST['base_module_basic'])) $base_module = $_REQUEST['base_module_basic'];
	// BEGIN SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN
	if (!empty($_REQUEST['status'])) $status = $_REQUEST['status'];
	// END SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN
	
	$where_clauses = Array();

	if(!empty($name)) array_push($where_clauses, "$seed->table_name.name like '$name%'");

	// BEGIN SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN
	if(!empty($status)) array_push($where_clauses, "$seed->table_name.status = '$status'");
	// END SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN

	if(!empty($base_module)){ 
		$module_in = "(";
		foreach($base_module as $module){
			$module_in .= "'$module', ";
		}
		$module_in = substr($module_in, 0, -2);
		$module_in .= ")";
		array_push($where_clauses, "$seed->table_name.base_module in $module_in");
	}
	
	$seed->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	foreach($where_clauses as $clause)
	{
		if($where != "")
		$where .= " and ";
		$where .= $clause;
	}

	$GLOBALS['log']->info("Here is the where clause for the list view: $where");

}

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/WorkFlow/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("ADVANCED_SEARCH_PNG", SugarThemeRegistry::current()->getImage('advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	if (!empty($name)) $search_form->assign("NAME", $_REQUEST['name_basic']);
	if (!empty($base_module)) $search_form->assign("BASE_MODULE", $_REQUEST['base_module_basic']);
	// BEGIN SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN
	if (!empty($status)) $search_form->assign("STATUS", $_REQUEST['status']);
	// END SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN

	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	$search_form->assign("OPTIONS_BASE_MODULE", get_select_options_with_id($seed->get_module_array(), isset($base_module) ? $base_module : ''));
	// BEGIN SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN
	$search_form->assign("OPTIONS_STATUS", get_select_options_with_id(array('', 'Active', 'Inactive'), isset($status) ? $status : ''));
	// END SUGARINTERNAL CUSTOMIZATION - WILL BECOME OBSOLETE AFTER UPGRADE TO 6.0 WHEN WORKFLOW IS REWRITTEN

	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);
	
		// adding custom fields:
		$seed->custom_fields->populateXTPL($search_form, 'search' );

		$search_form->parse("main");
		$search_form->out("main");

	echo "\n<BR>\n";
}




$ListView = new ListView();
$ListView->show_export_button = false;
$ListView->show_delete_button = false;
$ListView->show_select_menu = false;
$ListView->initNewXTemplate( 'modules/WorkFlow/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE'] . $header_text);
$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=ListView");
$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="'.$app_strings['LNK_REMOVE'].'" border="0"'));
$ListView->setQuery($where, "", "name", "WORKFLOW");
$ListView->show_mass_update = false;
$ListView->processListView($seed, "main", "WORKFLOW");


?>
