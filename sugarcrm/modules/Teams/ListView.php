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








global $app_strings;
global $app_list_strings;
global $current_language;
global $currentModule;
global $theme;
global $urlPrefix;
global $current_user;
$header_text = '';
$current_module_strings = return_module_language($current_language, 'Teams');


if (!is_admin($current_user)&& !is_admin_for_module($GLOBALS['current_user'],'Users')) sugar_die("Unauthorized access to administration.");

if (empty($where)) {
	$where = "";
}

$seedTeam = new Team();

if (isset($_REQUEST['query'])) {
	if (isset($_REQUEST['name'])) {
		$name = $_REQUEST['name'];
	}

	$where_clauses = Array();

	if (!empty($name)) {
		$where_clauses[] = "name like '".$seedTeam->db->quote($name)."%'";
	}

	$where = "";

	foreach ($where_clauses as $clause) {
		if (!empty($where)) {
			$where .= " AND ";
		}
		$where .= $clause;
	}

	$GLOBALS['log']->info("Here is the where clause for the list view: $where");
}

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	$search_form = new XTemplate("modules/Teams/SearchForm.html");
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	if (isset($name)) $search_form->assign("NAME", $_REQUEST['name']);
	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	if((is_admin($current_user)|| is_admin_for_module($current_user,'Users')) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
	}
	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);

	$search_form->parse("main");
	$search_form->out("main");
}


$ListView = new ListView();
$ListView->initNewXTemplate('modules/Teams/ListView.html',$current_module_strings);
if((is_admin($current_user)|| is_admin_for_module($current_user,'Users')) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
	}
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE']. $header_text);
$ListView->setQuery($where, "", "name", "TEAM");
$ListView->processListView($seedTeam, "main", "TEAM");
?>
