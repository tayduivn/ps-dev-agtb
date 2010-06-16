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
require_once("data/Tracker.php");
require_once('modules/Threads/Thread.php');

require_once('include/ListView/ListView.php');

if(!ACLController::checkAccess('Threads', 'list', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}

global $app_strings;
global $app_list_strings;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Threads');

global $urlPrefix;

global $currentModule;

global $theme;

if (!isset($where))
  $where = "";

$seedThread =& new Thread();
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
	if (isset($_REQUEST['title']))
      $title = $_REQUEST['title'];

	if (isset($_REQUEST['body']))
      $body = $_REQUEST['body'];
    
	$where_clauses = Array();

	if(isset($title) && $title != "")
      array_push($where_clauses, "threads.title like '".$GLOBALS['db']->quote($title)."%'");

	if(isset($body) && $body != "")
      array_push($where_clauses, "threads.body like '".$GLOBALS['db']->quote($body)."%'"); 

	$seedThread->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	foreach($where_clauses as $clause)
	{
		if($where != "")
		$where .= " and ";
		$where .= $clause;
	}

}

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/Threads/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("IMAGE_PATH", $image_path);
	$search_form->assign("ADVANCED_SEARCH_PNG", get_image($image_path.'advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign("BASIC_SEARCH_PNG", get_image($image_path.'basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	if(isset($title)) $search_form->assign("TITLE", $title);
	if(isset($body)) $search_form->assign("BODY", $body);

	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	$header_text = '';

  // 01-14-2006 -- added condition to remove search form for 'include listview directly from forums'	
  if(!isset($_REQUEST['record']))
  	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE'], $header_text, false);

	if (isset($_REQUEST['advanced']) && $_REQUEST['advanced'] == 'true')
    {
		$seedThread->custom_fields->populateXTPL($search_form, 'search' );
        
        // 01-14-2006 -- added condition to remove search form for 'include listview directly from forums'	
        if(!isset($_REQUEST['record']))
        {
		  $search_form->parse("advanced");
		  $search_form->out("advanced");
		}
	}
	else 
	{
        // adding custom fields:
        $seedThread->custom_fields->populateXTPL($search_form, 'search' );

        // 01-14-2006 -- added condition to remove search form for 'include listview directly from forums'	
        if(!isset($_REQUEST['record']))
        {
  		  $search_form->parse("main");
	  	  $search_form->out("main");
	    }
	}
	
	
	echo get_form_footer();
	echo "\n<BR>\n";
}

$ListView = new ListView();

$ListView->show_select_menu = false;
$ListView->show_delete_button = false;
$ListView->show_export_button = false;

$ListView->initNewXTemplate( 'modules/Threads/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE']);

global $current_user;
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
	$ListView->setHeaderText("<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".get_image($image_path."EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>" );
}
$ListView->setQuery($where, "", "", "THREAD");
if(!isset($_REQUEST['record']))
	$ListView->processListView($seedThread, "main_thread_list", "THREAD");
else
	$ListView->processListView($seedThread, "main_for_forum", "THREAD");
?>
