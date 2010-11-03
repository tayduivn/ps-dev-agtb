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
global $mod_strings;

global $urlPrefix;
global $currentModule;


global $theme;
if(!is_admin($current_user)&& !is_admin_for_module($GLOBALS['current_user'],'Campaigns')){
	sugar_die('Admin Only Section');	
}
$seed = new EmailMan();
// focus_list is the means of passing data to a ListView.
global $focus_list;
$header_text = '';
$sugar_config['disable_export'] = true;
require_once('modules/MySettings/StoreQuery.php');
$storeQuery = new StoreQuery();
if(!isset($_REQUEST['query'])){
	$storeQuery->loadQuery($currentModule);
	$storeQuery->populateRequest();
}else{
	$storeQuery->saveFromGet($currentModule);	
}
if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/EmailMan/SearchForm.html');
	$search_form->assign("MOD", $mod_strings);
	$search_form->assign("APP", $app_strings);
	if(isset($_REQUEST['query'])) {
		if(isset($_REQUEST['to_email'])) $search_form->assign("TO_EMAIL", $_REQUEST['to_email']);
		if(isset($_REQUEST['to_name'])) $search_form->assign("TO_NAME", $_REQUEST['to_name']);
		if(isset($_REQUEST['campaign_name'])) $search_form->assign("CAMPAIGN_NAME", $_REQUEST['campaign_name']);
	}
	                // adding custom fields:
$seed->custom_fields->populateXTPL($search_form, 'search' );
  $search_form->assign("SEARCH_ACTION", 'index');
	$search_form->assign("JAVASCRIPT", get_clear_form_js());
	$search_form->parse("main");
	if((is_admin($current_user)||is_admin_for_module($GLOBALS['current_user'],'Campaigns')) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
	}
	echo get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);	
	$search_form->out("main");
	echo "\n<BR>\n";
}



$where = "";



if(isset($_REQUEST['query']))
{
	// we have a query
	
	if (isset($_REQUEST['campaign_name'])) $campaign_name = $_REQUEST['campaign_name'];
	if (isset($_REQUEST['to_name'])) $to_name = $_REQUEST['to_name'];
	if (isset($_REQUEST['to_email'])) $to_email = $_REQUEST['to_email'];

	
	
	$where_clauses = Array();
	if(isset($campaign_name) && $campaign_name != '')
	{
        $where_clauses[] = " campaigns.name like '".$GLOBALS['db']->quote($campaign_name)."%' ";
	}
	if(isset($to_name) && $to_name != '')
	{
		$where_clauses[] = " (contacts.first_name like '".$GLOBALS['db']->quote($to_name)."%' OR contacts.last_name like '".$GLOBALS['db']->quote($to_name)."%' or leads.first_name like '".$GLOBALS['db']->quote($to_name)."%' OR leads.last_name like '".$GLOBALS['db']->quote($to_name)."%' or prospects.first_name like '".$GLOBALS['db']->quote($to_name)."%' OR prospects.last_name like '".$GLOBALS['db']->quote($to_name)."%') ";
	}
	if(isset($to_email) && $to_email != '')
	{
        
		$where_clauses[] = " email_addr_bean_rel.primary_address = 1  and email_addr_bean_rel.deleted = 0 and email_addr_bean_rel.email_address_id in (select id from email_addresses where email_address like '".$GLOBALS['db']->quote($to_email)."%') ";
	}

	$seed->custom_fields->setWhereClauses($where_clauses);

	$where = "";
	if (isset($where_clauses)) {
		foreach($where_clauses as $clause)
		{
			if($where != "")
			$where .= " and ";
			$where .= $clause;
		}
	}
	$GLOBALS['log']->info("Here is the where clause for the list view: $where");

}


	$display_title = $mod_strings['LBL_LIST_FORM_TITLE'];
	/*cn: necessary to inline this form because MassUpdate form wraps this and the listview rows
	 * nesting the form below
	 */
	echo "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-bottom: 2px;\">
	<form action=\"index.php\" method=\"post\" name=\"EmailManDelivery\" id=\"form\">
				<input type=\"hidden\" name=\"module\" value=\"EmailMan\">
				<input type=\"hidden\" name=\"action\">
				<input type=\"hidden\" name=\"return_module\">
				<input type=\"hidden\" name=\"return_action\">
				<input type=\"hidden\" name=\"manual\" value=\"true\">
				<input	title=\"".$app_strings['LBL_CAMPAIGNS_SEND_QUEUED']."\" 
						accessKey=\"".$app_strings['LBL_SAVE_BUTTON_KEY']."\" class=\"button\" 
						onclick=\"this.form.return_module.value='EmailMan'; this.form.return_action.value='index'; this.form.action.value='EmailManDelivery'\" 
						type=\"submit\" name=\"Send\" value=\"".$app_strings['LBL_CAMPAIGNS_SEND_QUEUED']."\">
	</form></td></tr></table>";

$ListView = new ListView();
$ListView->initNewXTemplate( 'modules/EmailMan/ListView.html',$mod_strings);
$ListView->setHeaderTitle($display_title . $header_text );
$ListView->setQuery($where, "", "send_date_time", "EMAILMAN");
$ListView->processListView($seed, "main", "EMAILMAN");
?>
