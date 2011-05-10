<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 * *******************************************************************************/
/*********************************************************************************

 * Description:
 * Portions created by SugarCRM are Copyright(C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/







global $app_strings;
global $app_list_strings;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Feeds');
global $urlPrefix;
global $currentModule;
global $theme;

echo get_module_title($mod_strings['LBL_MODULE_ID'],$mod_strings['LNK_FEED_LIST'], true); 

if (!isset($where)) $where = "";

$seedFeed = new Feed();
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);	
}
if(isset($_REQUEST['current_user_only']) && $_REQUEST['current_user_only'] != "")
{
		$seedFeed->my_favorites = true;
}

	// we have a query
	if(isset($_REQUEST['title'])) {
		$test = clean_xss($_REQUEST['title']);
		if(!empty($test))
			die("XSS attack detected in title.");
		else 
			$title = $_REQUEST['title'];
	}


	$where_clauses = Array();


	if(isset($_REQUEST['title']) && $_REQUEST['title'] != "") 
        $where_clauses[] = "feeds.title like '%".$GLOBALS['db']->quote($_REQUEST['title'])."%'";

	if(isset($_REQUEST['current_user_only']) && $_REQUEST['current_user_only'] != "") 
        $where_clauses[] = " users_feeds.user_id='{$current_user->id}' ";



	$where = "";
	foreach($where_clauses as $clause)
	{
		if($where != "")
		$where .= " and ";
		$where .= $clause;
	}

	$GLOBALS['log']->info("Here is the where clause for the list view: $where");


if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE'], '', false);
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/Feeds/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("JAVASCRIPT", get_clear_form_js());

	if(isset($_REQUEST['title']) && $_REQUEST['title'] != "")
	{
		$search_form->assign("TITLE", $_REQUEST['title']);
	}

	if(isset($_REQUEST['current_user_only']) && $_REQUEST['current_user_only'] != "")
	{
		$search_form->assign("CURRENT_USER_ONLY", "CHECKED");
	}


		$search_form->parse("main");
		$search_form->out("main");
	echo "\n<BR>\n";
}


$ListView = new ListView();

$ListView->initNewXTemplate( 'modules/Feeds/ListView.html',$current_module_strings);
if(isset($_REQUEST['current_user_only']) && $_REQUEST['current_user_only'] != "")
{
$ListView->setHeaderTitle($current_module_strings['LBL_MY_LIST_FORM_TITLE'] );
}
else
{
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE'] );
}
$ListView->setQuery($where, "", "title", "FEED");
$ListView->processListView($seedFeed, "main", "FEED");
?>
