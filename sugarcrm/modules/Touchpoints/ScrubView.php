<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Leads/Forms.php');
require_once('include/JSON.php');
require_once('include/utils.php');

global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;
global $sugar_version, $sugar_config;

if(!isset($_REQUEST['record'])){
	sugar_die("There must be a record id to process the lead");
}

// BEGIN IT Request 8052 - Try to scrub the lead before loading the page, and redirect if it was scrubbed
if(!empty($_REQUEST['return_action']) && $_REQUEST['return_action'] == 'LeadQualScoredLead'){
	$touchpoint = new Touchpoint();
	$touchpoint->retrieve($_REQUEST['record']);
	if(!empty($touchpoint->id)){
		$scrub_result_array = $touchpoint->scrub();
		if(!empty($scrub_result_array) && $scrub_result_array['scrubResultAction'] != 'no_match'){
			require_once('include/MVC/SugarApplication.php');
			$module = (!empty($_REQUEST['return_module']) ? "module={$_REQUEST['return_module']}" : "");
			$action = (!empty($_REQUEST['return_action']) ? "&action={$_REQUEST['return_action']}" : "&action=index");
			$record = (!empty($_REQUEST['return_id']) ? "&record={$_REQUEST['return_id']}" : "");
			$user_queue = (!empty($_SESSION['lead_qual_bucket']) && !empty($_SESSION['lead_qual_bucket']['user']) ? "&user={$_SESSION['lead_qual_bucket']['user']}" : "");
			SugarApplication::redirect("index.php?{$module}{$action}{$record}{$user_queue}");
		}
	}
}
// END IT Request 8052 - Try to scrub the lead before loading the page, and redirect if it was scrubbed


$focus = new Touchpoint();
$focus->retrieve($_REQUEST['record']);

if(isset($focus->scrubbed) && $focus->scrubbed && (!isset($_REQUEST['rescrub']) || $_REQUEST['rescrub'] != 'true')){
	SugarApplication::redirect("index.php?module=Touchpoints&action=DetailView&record={$_REQUEST['record']}");
}

if($focus->deleted) sugar_die("This record has been deleted.");

if(empty($focus->id)){
	sugar_die('There is no record with the passed id');
}

// Add to tracker so lead qual reps can view
require_once('modules/Touchpoints/ScrubFunctions.php');
scrubProcessToTracker($focus, true);

$json = getJSONobj();

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME']." Scrub View: ".$focus->first_name." ".$focus->last_name, true);
echo "\n<p>\n";
global $theme;
global $theme_path;
global $image_path;
require_once('include/utils/layout_utils.php');

$GLOBALS['log']->info("Touchpoint scrub view");

$xtpl=new XTemplate ('modules/Touchpoints/ScrubView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

$connector_text=<<<EOQ
<p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
<tr>
<td>
<div id="connector_panel">
<span id="connector_panel_span">{$GLOBALS['app_strings']['LBL_LOADING_PAGE']} <img src="themes/default/images/sqsWait.gif"></span>
</div>
</td>
</tr>
</table>
<p>

<script type="text/javascript">
YAHOO.util.Event.onDOMReady( function() {
    var callback =  {
            success: function(data) {
                
                var newdiv = document.createElement('div');
                newdiv.innerHTML = data.responseText;
                var container = document.getElementById('connector_panel');
                container.removeChild(document.getElementById('connector_panel_span'));
                container.appendChild(newdiv);
                SUGAR.util.evalScript(data.responseText);
            },
            failure: function(data) {

            }
    }

    if(typeof document.getElementById('connector_panel') != 'undefined') {
        postData = 'module=Touchpoints&action=RenderConnectorPanel&to_pdf=true&record={$focus->id}';
        YAHOO.util.Connect.asyncRequest('GET','index.php?'+postData, callback);
    }
});
</script>

EOQ;

$xtpl->assign('CONNECTOR', $connector_text);

/// Users Popup
$popup_request_data = array(
	'call_back_function' => 'set_return',
	'form_name' => 'ScrubView',
	'field_to_name_array' => array(
		'id' => 'assigned_user_id',
		'user_name' => 'assigned_user_name',
		),
	);
$json = getJSONobj();
$xtpl->assign('encoded_users_popup_request_data', $json->encode($popup_request_data));


$popup_request_data = array(
	'call_back_function' => 'set_return',
	'form_name' => 'ScrubView',
	'field_to_name_array' => array(
		'id' => 'team_id',
		'name' => 'team_name',
		),
	);
$xtpl->assign('encoded_team_popup_request_data', $json->encode($popup_request_data));


// Unimplemented until jscalendar language files are fixed
// $xtpl->assign("CALENDAR_LANG", ((empty($cal_codes[$current_language])) ? $cal_codes[$default_language] : $cal_codes[$current_language]));
$xtpl->assign("CALENDAR_LANG", "en");$xtpl->assign("CALENDAR_DATEFORMAT", parse_calendardate($app_strings['NTC_DATE_FORMAT']));

$xtpl->assign("RETURN_MODULE", (isset($_REQUEST['return_module']) ? $_REQUEST['return_module'] : 'Touchpoints'));
$xtpl->assign("RETURN_ACTION", (isset($_REQUEST['return_action']) ? $_REQUEST['return_action'] : 'index'));
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
// handle Create $module then Cancel
if (empty($_REQUEST['return_id']) && isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != 'LeadQualScoredLead') {
	$xtpl->assign("RETURN_ACTION", 'index');
}
$xtpl->assign("THEME", $theme);
$xtpl->assign("IMAGE_PATH", $image_path);$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);


require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$qsd->setFormName('ScrubView');
echo $qsd->getQSScripts();

$sqs_objects = array(
	'assigned_user_name' => $qsd->getQSUser(),
	'team_name' => $qsd->getQSTeam()
);
$quicksearch_js = '<script type="text/javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';

$xtpl->assign("JAVASCRIPT", get_set_focus_js(). $quicksearch_js);
$xtpl->assign("ID", $focus->id);
if ( isset($_REQUEST['user']) )
    $xtpl->assign("USER", $_REQUEST['user']);
$xtpl->assign("COMPANY_NAME", $focus->company_name);
$xtpl->assign("HEADER", get_module_title("Contacts", "{MOD.LBL_CONTACT}  ".$focus->first_name." ".$focus->last_name, true));
if (isset($focus->first_name)) $xtpl->assign("FIRST_NAME", $focus->first_name);
else $xtpl->assign("FIRST_NAME", "");
$xtpl->assign("LAST_NAME", $focus->last_name);
$xtpl->assign("DEFAULT_SEARCH", "&query=true&company_name=".urlencode($focus->company_name));
$xtpl->assign("PHONE_MOBILE", $focus->phone_mobile);
$xtpl->assign("PHONE_WORK", $focus->phone_work);
$xtpl->assign("EMAIL1", $focus->email1);
$xtpl->assign("TRIAL_NAME_C", $focus->trial_name_c);
$xtpl->assign("TRIAL_EXPIRATION_C", $focus->trial_expiration_c);
$xtpl->assign("EMAIL2", (isset($focus->email2) ? $focus->email2 : ""));
$xtpl->assign("PRIMARY_ADDRESS_STREET", $focus->primary_address_street);
$xtpl->assign("PRIMARY_ADDRESS_CITY", $focus->primary_address_city);
$xtpl->assign("PRIMARY_ADDRESS_STATE", $focus->primary_address_state);
$xtpl->assign("PRIMARY_ADDRESS_POSTALCODE", $focus->primary_address_postalcode);
$xtpl->assign("PRIMARY_ADDRESS_COUNTRY", $focus->primary_address_country);
$xtpl->assign("PRIMARY_ADDRESS_COUNTRY_OPTIONS", get_select_options_with_id($app_list_strings['countries_dom'], $focus->primary_address_country));
$xtpl->assign("REFERED_BY", $focus->referred_by);
$xtpl->assign("ALT_ADDRESS_STREET", $focus->alt_address_street);
$xtpl->assign("ALT_ADDRESS_CITY", $focus->alt_address_city);
$xtpl->assign("ALT_ADDRESS_STATE", $focus->alt_address_state);
$xtpl->assign("ALT_ADDRESS_POSTALCODE", $focus->alt_address_postalcode);
$xtpl->assign("ALT_ADDRESS_COUNTRY_OPTIONS", get_select_options_with_id($app_list_strings['countries_dom'], $focus->alt_address_country));
$xtpl->assign("DESCRIPTION", $focus->description);
$xtpl->assign("THIRD_PARTY_CHECKED", !empty($focus->third_party_validation_c) ? "checked" : "");

if(!empty($_REQUEST['rescrub']) && $_REQUEST['rescrub'] == true && $focus->scrubbed == 1){
	$xtpl->assign("RESCRUB", "true");
    rescrubTouchpoint($focus->id);
/*	$rescrub_warning = "<font color=red>The following information should be cleaned up and deleted before rescrubbing this record:";
	$found = false;
	if(!empty($focus->new_leadaccount_id)){
		require_once('modules/LeadAccounts/LeadAccount.php');
		$leadAcc = new LeadAccount();
		$leadAcc->retrieve($focus->new_leadaccount_id);
		$rescrub_warning .= "\n<br>* Lead Company <a href='index.php?module=LeadAccounts&action=DetailView&record={$leadAcc->id}'>{$leadAcc->name}</a>";
		$found = true;
	}
	if(!empty($focus->new_leadcontact_id)){
		require_once('modules/LeadContacts/LeadContact.php');
		$leadCon = new LeadContact();
		$leadCon->retrieve($focus->new_leadcontact_id);
		$rescrub_warning .= "\n<br>* Lead Person <a href='index.php?module=LeadAccounts&action=DetailView&record={$leadCon->id}'>{$leadCon->name}</a>";
		$found = true;
	}
	$interactions_query = "select interactions.parent_id, interactions.parent_type, concat(contacts.first_name, ' ', contacts.last_name) contact_name ".
						  "from interactions inner join contacts on interactions.parent_id = contacts.id ".
						  "where interactions.source_id = '{$focus->id}' and interactions.parent_type = 'Contacts' and ".
						  "      interactions.deleted = 0 and contacts.deleted = 0";
	$interactions_resource = $GLOBALS['db']->query($interactions_query);
	while($interactions_row = $GLOBALS['db']->fetchByAssoc($interactions_resource)){
		$rescrub_warning .= "\n<br>* Contact <a href='index.php?module=Contacts&action=DetailView&record={$interactions_row['parent_id']}'>{$interactions_row['contact_name']}</a>";
		//$rescrub_warning .= "\n<br>* {$GLOBALS['app_list_strings']['moduleList'][$interactions_row['parent_type']]} <a href='index.php?module={$interactions_row['parent_type']}&action=DetailView&record={$interactions_row['parent_id']}'>a</a>";
		$found = true;
	}
	$rescrub_warning .= "</font>\n<br><br>\n";
	
	if($found)
		$xtpl->assign("RESCRUB_WARNING", $rescrub_warning);*/
}
else{
	$xtpl->assign("RESCRUB", "false");
}

// SADEK - BEGIN ADDING BACK IN CUSTOM FIELDS FROM LEADS MODULE
// Sadek - migrated from lead bean - Customization by Julian
if (!isset($app_list_strings['partner_assigned_to'])) {
	$app_list_strings['partner_assigned_to'] = get_partner_array(TRUE);
}
if (!isset($app_list_strings['lead_owner_options'])) {
	$app_list_strings['lead_owner_options'] = get_user_array(TRUE);
}
if (!isset($app_list_strings['campaign_list'])) {
	$app_list_strings['campaign_list'] = get_campaign_array(TRUE);
}
// Sadek - migrated from lead bean - End customization by Julian

$xtpl->assign("STATUS_OPTIONS", get_select_options_with_id($app_list_strings['lead_status_dom'], ''));
$xtpl->assign("OPTIONS_CAMPAIGN_NAME", get_select_options_with_id($app_list_strings['campaign_list'], (isset($focus->campaign_id) ? $focus->campaign_id : "")));
$xtpl->assign("SALUTATION_OPTIONS", get_select_options_with_id($app_list_strings['salutation_dom'], (isset($focus->salutation) ? $focus->salutation : "")));
$xtpl->assign("OPTIONS_LEAD_OWNER_C", get_select_options_with_id($app_list_strings['lead_owner_options'], (isset($focus->lead_owner_c) ? $focus->lead_owner_c : "")));
$xtpl->assign("OPTIONS_PARTNER_ASSIGNED_TO_C", get_select_options_with_id($app_list_strings['partner_assigned_to'], (isset($focus->partner_assigned_to_c) ? $focus->partner_assigned_to_c : "")));
$xtpl->assign("OPTIONS_ANNUAL_REVENUE2_C", get_select_options_with_id($app_list_strings['lead_annual_rev_dom'], (isset($focus->annual_revenue) ? $focus->annual_revenue : "")));
$xtpl->assign("OPTIONS_LEAD_GROUP_C", get_select_options_with_id($app_list_strings['lead_group_dom'], (isset($focus->lead_group_c) ? $focus->lead_group_c : "")));
$xtpl->assign("OPTIONS_POTENTIAL_USERS_C", get_select_options_with_id($app_list_strings['potential_users_dom'], (isset($focus->potential_users_c) ? $focus->potential_users_c : "")));
$xtpl->assign("OPTIONS_EMPLOYEE_QTY_C", get_select_options_with_id($app_list_strings['employee_qty_dom'], (isset($focus->employees) ? $focus->employees : "")));
// SADEK - END ADDING BACK IN CUSTOM FIELDS FROM LEADS MODULE

$xtpl->assign("TEAM_OPTIONS", get_select_options_with_id(get_team_array(), $focus->team_id));
$xtpl->assign("TEAM_NAME", get_assigned_team_name($focus->team_id));
$xtpl->assign("TEAM_ID", $focus->team_id);
$xtpl->parse("main.pro");

if (empty($focus->assigned_user_id) && empty($focus->id))  $focus->assigned_user_id = $current_user->id;
if (empty($focus->assigned_name) && empty($focus->id))  $focus->assigned_user_name = $current_user->user_name;
$xtpl->assign("ASSIGNED_USER_OPTIONS", get_select_options_with_id(get_user_array(TRUE, "Active", $focus->assigned_user_id), $focus->assigned_user_id));
$xtpl->assign("ASSIGNED_USER_NAME", get_assigned_user_name($focus->assigned_user_id));
$xtpl->assign("ASSIGNED_USER_ID", $focus->assigned_user_id );

// BEGIN SUGARINTERNAL CUSTOMIZATION - SUPPORT LEAD SCORE DISPLAY FOR TEMPLATE
$xtpl->assign("LEAD_SCORE",(empty($focus->lead_score) ? "" : $focus->lead_score));
require_once('custom/si_custom_files/custom_functions.php');
$domainExclusionList = getDomainExclusionList();
if(!empty($focus->email1)){
	$target_email_domain = substr_replace($focus->email1, '', 0, strpos($focus->email1, '@'));
    $target_email_domain_no_at = substr($target_email_domain, 1);
    if(in_array($target_email_domain_no_at, $domainExclusionList)){
		$xtpl->assign('PS_EMAIL', $focus->email1);
    }
	else{
		$xtpl->assign('PS_EMAIL', $target_email_domain);
	}
}
if(!empty($focus->first_name))
	$xtpl->assign('PS_FIRST_NAME', $focus->first_name);
if(!empty($focus->last_name))
	$xtpl->assign('PS_LAST_NAME', $focus->last_name);
if(!empty($focus->company_name))
	$xtpl->assign('PS_COMPANY_NAME', $focus->company_name);
// END SUGARINTERNAL CUSTOMIZATION - SUPPORT LEAD SCORE DISPLAY FOR TEMPLATE
$xtpl->assign("LEAD_SOURCE_DESCRIPTION", $focus->lead_source_description);
$xtpl->assign("REFERED_BY", $focus->referred_by);

$xtpl->assign("LEAD_SOURCE_OPTIONS", get_select_options_with_id($app_list_strings['lead_source_dom'], $focus->lead_source));
//BEGIN SUGARINTERNAL CUSTOMIZATIONS - sadek
    //just setup some values
    $xtpl->assign('TARGET_FIRST_NAME', trim($focus->first_name));
    $xtpl->assign('TARGET_LAST_NAME', trim($focus->last_name));
    $xtpl->assign('TARGET_NAME', $focus->full_name);
    $xtpl->assign('TARGET_COMPANY_NAME', $focus->company_name);
    $xtpl->assign('TARGET_EMAIL1', $focus->email1);
    $email1_domain = substr_replace($focus->email1, '', 0, strpos($focus->email1, '@'));
    $domain_label = '(domain)';
    if(!empty($email1_domain)){
        $focus->email1_domain = $email1_domain;
        $xtpl->assign('TARGET_EMAIL1_DOMAIN', $focus->email1_domain);
        $xtpl->assign('LABEL_EMAIL1_DOMAIN', $domain_label);
    }

if(!empty($focus->website_c)){
	$xtpl->assign('COMPANY_WEBSITE_LINK', "<a href='{$focus->website_c}' target=_blank>{$focus->website_c}</a>");
}
if(!empty($email1_domain)){
	$email1_domain_no_at = substr($email1_domain, 1);
	$xtpl->assign('EMAIL_DOMAIN_WEBSITE_LINK', "<a href='http://www.{$email1_domain_no_at}' target=_blank>http://www.{$email1_domain_no_at}</a>");
}

$xtpl->parse("main.full_ps_section");
$xtpl->parse("main.parent_search_js");
//END SUGARINTERNAL CUSTOMIZATIONS - sadek
$xtpl->parse("main");
$xtpl->out("main");

require_once('include/javascript/javascript.php');
$javascript = new javascript();
$javascript->setFormName('ScrubView');
$javascript->setSugarBean($focus);
$javascript->addAllFields('');
echo $javascript->getScript();
