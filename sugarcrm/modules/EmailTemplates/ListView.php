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






require_once('modules/MySettings/StoreQuery.php');

global $list_max_entries_per_page;
global $urlPrefix;
global $currentModule;
global $db;
global $focus_list; // focus_list is the means of passing data to a ListView.
global $title;

$header_text = '';
$seedEmailTemplate = new EmailTemplate();
$storeQuery = new StoreQuery();
$list_form = new XTemplate ('modules/EmailTemplates/ListView.html');

if(empty($_POST['mass']) && empty($where) && empty($_REQUEST['query'])) {
	$_REQUEST['query']='true'; $_REQUEST['current_user_only']='checked'; 
}

if(!isset($_REQUEST['query'])) {
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
} else {
	$storeQuery->saveFromGet($currentModule);	
}

if(!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/EmailTemplates/SearchForm.html');
	$search_form->assign("MOD", $mod_strings);
	$search_form->assign("APP", $app_strings);

	if(isset($_REQUEST['query'])) {
		if(isset($_REQUEST['name'])) $search_form->assign("NAME", $_REQUEST['name']);
		if(isset($_REQUEST['description'])) $search_form->assign("DESCRIPTION", $_REQUEST['description']);
	}
	// adding custom fields:
	$seedEmailTemplate->custom_fields->populateXTPL($search_form, 'search' );
	$search_form->parse("main");
	echo "\n<p>\n";

	if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])) {	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
	}

	echo get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);
	$search_form->out("main");
	echo "\n</p>\n";
}

$list_form->assign("MOD", $mod_strings);
$list_form->assign("APP", $app_strings);
$list_form->assign("MODULE_NAME", $currentModule);

$where = "";

if(isset($_REQUEST['query'])) {
	// we have a query
	$name = '';
	$desc = '';
	if(isset($_REQUEST['name'])) { 
		$name = $_REQUEST['name'];
	}
	if(isset($_REQUEST['description'])) {
		$desc = $_REQUEST['description'];
	}

	$where_clauses = array();

	if(!empty($name)) {
		array_push($where_clauses, "email_templates.name like '%".$GLOBALS['db']->quote($name)."%'");
	}
	if(!empty($desc)) {
		array_push($where_clauses, "email_templates.description like '%".$GLOBALS['db']->quote($desc)."%'");
	}
	
	$seedEmailTemplate->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	

	//ensures workflow templates don't show up in the normal email list
	$where .= " base_module='' OR base_module IS NULL ";	


	if(isset($where_clauses)) {
		foreach($where_clauses as $clause) {
			if($where != "")
			$where .= " and ";
			$where .= $clause;
		}
	}
	$GLOBALS['log']->info("Here is the where clause for the list view: $where");
}


$display_title = $mod_strings['LBL_LIST_FORM_TITLE'];

if($title) {
	$display_title = $title;
}

$ListView = new ListView();

if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])) {	
	$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
}
$ListView->initNewXTemplate( 'modules/EmailTemplates/ListView.html',$mod_strings);
$ListView->setHeaderTitle($display_title . $header_text);
$ListView->setQuery($where, "", "email_templates.date_entered DESC", "EMAIL_TEMPLATE");
if ($db->dbType == 'mysql') {
	$ListView->createXTemplate();
	$sortURLBase = $ListView->getBaseURL("EMAIL_TEMPLATE"). "&".$ListView->getSessionVariableName("EMAIL_TEMPLATE","ORDER_BY")."=";
	$ListView->xTemplate->assign("ET_ORDER_BY", '<a href="'.$sortURLBase.'description" class="listViewThLinkS1">');
	$ListView->xTemplateAssign("et_arrow_start", $ListView->getArrowStart($ListView->local_image_path));
	$arrowInfo = $ListView->getOrderByInfo('EMAIL_TEMPLATE');
	if ($arrowInfo[0] == 'description') {
		$imgArrow = "_down";
		if($arrowInfo[1]) {
			$imgArrow = "_up";
		}
		$ListView->xTemplateAssign('et_description_arrow', $imgArrow);
	}
	$ListView->xTemplateAssign('et_arrow_end', $ListView->getArrowEnd($ListView->local_image_path).'</a>');
}
$ListView->processListView($seedEmailTemplate, "main", "EMAIL_TEMPLATE");
?>
