<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************

 * Description:  
 ********************************************************************************/








$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);	
}

global $app_strings, $app_list_strings, $current_language, $current_user;
$current_module_strings = return_module_language($current_language, 'QueryBuilder');
$header_text = '';
global $currentModule;
global $theme;
global $urlPrefix;


echo getClassicModuleTitle("Query_Builder", array($mod_strings['LBL_MODULE_TITLE']), true); 
if (!isset($where)) $where = "";

$seed = new QueryBuilder();
if(!empty($_REQUEST['query']))
{
	// we have a query
	if (!empty($_REQUEST['name'])) $name = $_REQUEST['name'];
	
	$where_clauses = Array();

	if(!empty($name)) array_push($where_clauses, "$seed->table_name.name like '%$name%'");

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
	$search_form=new XTemplate ('modules/QueryBuilder/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("ADVANCED_SEARCH_PNG", SugarThemeRegistry::current()->getImage('advanced_search','  border="0"',null,null,'.gif',$mod_strings['LBL_ADVANCED_SEARCH']));
	$search_form->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search','  border="0"',null,null,'.gif',$app_strings['LNK_BASIC_SEARCH']));
	if (!empty($name)) $search_form->assign("NAME", $_REQUEST['name']);

	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>";
	}
	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);

	               // adding custom fields:
		$seed->custom_fields->populateXTPL($search_form, 'search' );

		$search_form->parse("main");
		$search_form->out("main");

	echo "\n<BR>\n";
}

$ListView = new ListView();

if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'"
,null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>";
}
$ListView->initNewXTemplate( 'modules/QueryBuilder/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE'] . $header_text);
$ListView->setQuery($where, "", "name", "QUERY_BUILDER");
$ListView->processListView($seed, "main", "QUERY_BUILDER");
?>
