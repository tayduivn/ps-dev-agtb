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
global $current_user;
global $urlPrefix;
global $currentModule;



$current_module_strings = return_module_language($current_language, 'TimePeriods');
if(!is_admin($current_user) && !is_admin_for_module($current_user,'Forecasts')&& !is_admin_for_module($current_user,'ForecastSchedule')) 
{
   sugar_die("Unauthorized access to administration.");
}


echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_TITLE'], true);

if (!isset($where)) $where = "";
$seedTimePeriod = new TimePeriod();
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
	if (isset($_REQUEST['name'])) $name = $_REQUEST['name'];
	if (isset($_REQUEST['parent_id'])) $parent_id = $_REQUEST['parent_id'];
	if (isset($_REQUEST['start_date'])) $start_date = $_REQUEST['start_date'];
	if (isset($_REQUEST['end_date'])) $end_date = $_REQUEST['end_date'];

	$where_clauses = Array();

	if(isset($name) && $name != "") 
        $where_clauses[] = "name like '".$seedTimePeriod->db->quote($name)."%'";
	if(isset($parent_id) && $parent_id != "") 
        $where_clauses[] = "parent_id like '".$seedTimePeriod->db->quote($parent_id)."%'";
	global $timedate;
	if (!empty($start_date)) {
		$start_date=$timedate->to_db_date($start_date);
		array_push($where_clauses, "start_date = '".$start_date."'");
	}
	if (!empty($end_date)) {
		$end_date=$timedate->to_db_date($end_date);
		array_push($where_clauses, "end_date = '".$end_date."'");
	}	

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
	$search_form=new XTemplate ('modules/TimePeriods/SearchForm.html');
	$search_form->assign("MOD", $current_module_strings);
	$search_form->assign("APP", $app_strings);
	$search_form->assign("ADVANCED_SEARCH_PNG", SugarThemeRegistry::current()->getImage('advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	if (isset($name)) $search_form->assign("NAME", $_REQUEST['name']);

	$fiscal_year_dom = TimePeriod::get_fiscal_year_dom();
	array_unshift($fiscal_year_dom, '');
	if (isset($fiscal_year)) $search_form->assign("FISCAL_OPTIONS", get_select_options_with_id($fiscal_year_dom,$fiscal_year));
	else $search_form->assign("FISCAL_OPTIONS", get_select_options_with_id($fiscal_year_dom, ''));

	$search_form->assign("JAVASCRIPT", get_clear_form_js());

	global $timedate;
	$search_form->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());
	$search_form->assign("USER_DATEFORMAT", '('. $timedate->get_user_date_format().')');

	echo get_form_header($current_module_strings['LBL_SEARCH_FORM_TITLE'], "", false);
	if (isset($_REQUEST['advanced']) && $_REQUEST['advanced'] == 'true') {
		if(isset($start_date)) $search_form->assign("STARTDATE", $start_date);
		if(isset($end_date)) $search_form->assign("ENDDATE", $end_date);
		//if(isset($fiscal_year)) $search_form->assign("FISCALYEAR", $fiscal_year);
		
		$search_form->parse("advanced");
		$search_form->out("advanced");
	}
	else {
		$search_form->parse("main");
		$search_form->out("main");
	}
	echo "\n<BR>\n";
}


$ListView = new ListView();
$ListView->initNewXTemplate('modules/TimePeriods/ListView.html',$current_module_strings);
$ListView->setHeaderTitle($current_module_strings['LBL_LIST_FORM_TITLE']);
$ListView->setQuery($where, "", "name", "TIMEPERIOD");
$ListView->processListView($seedTimePeriod, "main", "TIMEPERIOD");
?>
