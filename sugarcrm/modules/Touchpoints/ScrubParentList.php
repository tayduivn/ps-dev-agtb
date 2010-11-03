<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $app_list_strings;
global $beanList;
global $dictionary;

global $theme;

require_once('XTemplate/xtpl.php');

require_once('custom/si_custom_files/jgreenLead.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/LeadContacts/LeadContact.php');
require_once('modules/LeadAccounts/LeadAccount.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('include/javascript/javascript.php');

require_once('custom/si_custom_files/LQListView.php');

global $current_user;
global $mod_strings;
global $app_list_strings;
global $app_strings;

global $odd_bg;
global $even_bg;

$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";
require_once('include/utils/layout_utils.php');

global $urlPrefix;

$contact_module_strings = return_module_language($current_language, 'Contacts');
$account_module_strings = return_module_language($current_language, 'Accounts');

require_once('custom/si_custom_files/custom_functions.php');
global $domainExclusionList;
$domainExclusionList = getDomainExclusionList();

///TODO!: Do we want to pull from the parent editview or from the stored record?
$lead_object = new jgreenLead();
$target_touchpoint = new Touchpoint();
$target_lead = new Lead();
//$target_lead_account = new LeadAccount();
$target_lead_contact = new LeadContact();
$target_contact = new Contact();
$target_account = new Account();

/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:16800
** Description: increased record limit from 5 to 20
*/

$records_per_page = 20;

/* END SUGARINTERNAL CUSTOMIZATION */

if (isset($_REQUEST['lead_id']) && isset($_REQUEST['lead_id'])) {
    $target_touchpoint->retrieve($_REQUEST['lead_id']);
    $target_lead->retrieve($_REQUEST['lead_id']);
    $target_lead_contact->retrieve($_REQUEST['lead_id']);
} else {

    die("you shouldn't be here");
}

$manual_query = false;
$where = "";
$contact_where = "";
$account_where = "";


$target_email = $target_touchpoint->email1;
$target_email_domain = substr_replace($target_touchpoint->email1, '', 0, strpos($target_touchpoint->email1, '@'));

if (isset($_REQUEST['query']) && $_REQUEST['query'] == "yes") {
    // we have a query
    $manual_query = true;

    $first_name = isset($_REQUEST['ps_first_name']) ? $_REQUEST['ps_first_name'] : '';
    $last_name = isset($_REQUEST['ps_last_name']) ? $_REQUEST['ps_last_name'] : '';
    $company_name = isset($_REQUEST['ps_company_name']) ? $_REQUEST['ps_company_name'] : '';
    $email = isset($_REQUEST['ps_email']) ? $_REQUEST['ps_email'] : '';

    $manual_query = false;

    $at_symbol = strpos($email, '@');
    $is_email_domain = false;
    $searcher_email = $email;
    if ($at_symbol == 0 || $at_symbol === false) {
        $is_email_domain = true;
        $email_domain_no_at = ($at_symbol === false) ? $email : substr($email, 1);
        $searcher_email = $email_domain_no_at;
    }

    require_once('custom/si_custom_files/LeadScrubSearcher.php');
    $searcher = new LeadScrubSearcher();
    $record_ids = $searcher->getRecordIds($target_touchpoint->id, $searcher_email, $is_email_domain, $first_name, $last_name, $company_name, $records_per_page);
    $GLOBALS['log']->fatal(var_export($record_ids, true));
    //end a manual query was found
}
else if (!empty($target_email) || !empty($target_touchpoint->first_name) || !empty($target_touchpoint->last_name) || !empty($target_touchpoint->company_name)) {
    $manual_query = false;
    $first_name = isset($target_touchpoint->first_name) ? $target_touchpoint->first_name : "";
    $last_name = isset($target_touchpoint->last_name) ? $target_touchpoint->last_name : "";
    $company_name = (isset($target_touchpoint->company_name) ? $target_touchpoint->company_name : "");

    $email = $target_email_domain;
    $is_email_domain = true;
    $target_email_domain_no_at = substr($target_email_domain, 1);
    $searcher_email = $target_email_domain_no_at;
    if (in_array($target_email_domain_no_at, $domainExclusionList)) {
        $email = $target_email;
        $searcher_email = $email;
        $is_email_domain = false;
    }

    require_once('custom/si_custom_files/LeadScrubSearcher.php');
    $searcher = new LeadScrubSearcher();
    $record_ids = $searcher->getRecordIds($target_touchpoint->id, $searcher_email, $is_email_domain, $first_name, $last_name, $company_name, $records_per_page);

}
else {
    $noEmailNoQuery = true;
}


$xtpl = new XTemplate ('modules/Touchpoints/ScrubParentList.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("THEME", $theme);
//$xtpl->assign("RETURN_PREFIX", $exp_object->return_prefix);
$xtpl->assign("IMAGE_PATH", $image_path);
$xtpl->assign("PRINT_URL", "index.php?" . $GLOBALS['request_string']);
insert_popup_header($theme);

//just setup some values
$xtpl->assign('TARGET_FIRST_NAME', trim($target_touchpoint->first_name));
$xtpl->assign('TARGET_LAST_NAME', trim($target_touchpoint->last_name));
$xtpl->assign('TARGET_NAME', $target_touchpoint->full_name);
$xtpl->assign('TARGET_COMPANY_NAME', $target_touchpoint->company_name);
$xtpl->assign('TARGET_EMAIL1', $target_touchpoint->email1);
$email1_domain = substr_replace($target_touchpoint->email1, '', 0, strpos($target_touchpoint->email1, '@'));
$domain_label = '(domain)';
if (!empty($email1_domain)) {
    $target_lead->email1_domain = $email1_domain;
    $xtpl->assign('TARGET_EMAIL1_DOMAIN', $target_lead->email1_domain);
    $xtpl->assign('LABEL_EMAIL1_DOMAIN', $domain_label);
}

$xtpl->parse("main_start");
$xtpl->out("main_start");


if (isset($noEmailNoQuery) && $noEmailNoQuery == true) {
    sugar_die("<i>Please use the search form above</i><BR>");
}


////////////////////////////////////////////////////////////////////////////////////////

//set max per page
$sugar_config['list_max_entries_per_page'] = $records_per_page;

$_SESSION['last_search_mod'] = 'Touchpoints';
$_SESSION['last_form_state'] = 'basic';

//Potential Parent Leads
$first_section_found = false;

if (!isset($record_ids)) {
    echo "Error 2942: Error from LeadScrubSearcher class.<BR>";
    sugar_die('');
}

require_once('include/ListView/ListViewData.php');

// Process Potential Contacts
foreach ($record_ids['Contacts'] as $index => $record_array) {
    $record_count += count($record_array);
    $div_id = "ps_contact_$index";
    $div_id_xtpl = $first_section_found ? "$div_id style='display:none'" : $div_id;
    $image_div = "img_contact_$index";
    $show_style = $first_section_found ? "style='display:inline'" : "style='display:none'";
    $hide_style = $first_section_found ? "style='display:none'" : "style='display:inline'";
    $image_xtpl = "<span id='show_$image_div' $show_style><img src='/themes/Sugar5/images/advanced_search.gif'></span><span id='hide_$image_div' $hide_style><img src='/themes/Sugar5/images/basic_search.gif'></span>";
    $contact_in_string = $searcher->getInString($record_array);
    $where = "contacts.id in $contact_in_string";
    $CListView = new LQListView();
    $CListView->initNewXTemplate('modules/Touchpoints/ScrubParentList.html', $mod_strings);
    $CListView->xTemplateAssign('DIV_ID', $div_id_xtpl);
    $list_header = "$image_xtpl&nbsp;<a href='javascript:void(0);' onclick='psShowHide(\"$div_id\", \"$image_div\");'>" . count($record_array) . " Contacts with matching " . $searcher->translateLabel($index) . "</a>";
    $CListView->setHeaderTitle($list_header);
    $CListView->setQuery($where, "", "" /*CHANGED FROM "contacts.last_name, contacts.first_name"*/, "CONTACT");
    $CListView->setAdditionalDetails(false);
    $CListView->show_export_button = false;
    $CListView->setRecordsPerPage($records_per_page);
    $CListView->processListView($target_contact, "potential_parent_contact", "CONTACT");

    if (!$first_section_found)
        $first_section_found = true;
}

// Process Potential Accounts
foreach ($record_ids['Accounts'] as $index => $record_array) {
    $record_count += count($record_array);
    $div_id = "ps_account_$index";
    $div_id_xtpl = $first_section_found ? "$div_id style='display:none'" : $div_id;
    $image_div = "img_account_$index";
    $show_style = $first_section_found ? "style='display:inline'" : "style='display:none'";
    $hide_style = $first_section_found ? "style='display:none'" : "style='display:inline'";
    $image_xtpl = "<span id='show_$image_div' $show_style><img src='/themes/Sugar5/images/advanced_search.gif'></span><span id='hide_$image_div' $hide_style><img src='/themes/Sugar5/images/basic_search.gif'></span>";
    $account_in_string = $searcher->getInString($record_array);
    $where = "accounts.id in $account_in_string";
    $AListView = new LQListView();
    $AListView->initNewXTemplate('modules/Touchpoints/ScrubParentList.html', $mod_strings);
    $AListView->xTemplateAssign('DIV_ID', $div_id_xtpl);
    $list_header = "$image_xtpl&nbsp;<a href='javascript:void(0);' onclick='psShowHide(\"$div_id\", \"$image_div\");'>" . count($record_array) . " Accounts with matching " . $searcher->translateLabel($index) . "</a>";
    $AListView->setHeaderTitle($list_header);
    $AListView->setQuery($where, "", "" /*CHANGED FROM "accounts.last_name, accounts.first_name"*/, "ACCOUNT");
    $AListView->setAdditionalDetails(false);
    $AListView->show_export_button = false;
    $AListView->setRecordsPerPage($records_per_page);
    $AListView->processListView($target_account, "potential_parent_account", "ACCOUNT");

    if (!$first_section_found)
        $first_section_found = true;
}


if (isset($record_count) && $record_count == 0) {
    echo "<h3>No records found via Smart Search.</h3>";
}
$xtpl->parse("set_as_parent");
$xtpl->out("set_as_parent");

$xtpl->parse("main_end");
$xtpl->out("main_end");

if (isset($record_ids)) {
    $label_map = $searcher->getSmartSearchDefinitions();
    echo "<BR>";

    $full_email = false;
    $search_was_full_email = false;
    if (strpos($email, '@') !== false) {
        $full_email = true;
    }
    if (inDomainExclusionList($email, $full_email)) {
        $search_was_full_email = true;
    }

    foreach ($record_ids as $module_type => $record_id_array) {
        echo "<h3>" . $app_list_strings['moduleList'][$module_type] . " searches with " . count($record_id_array) . " result(s)</h3>";
        foreach ($label_map as $index => $message) {
            if (($module_type == 'Accounts' || $module_type == 'LeadAccounts') && $index != "accountname" && $index != "email" && $index != "website") {
                continue;
            }

            if (!array_key_exists($index, $record_ids[$module_type])) {
                echo "0 results matching on $message<BR>";
            }
            else {
                if (!$search_was_full_email && $module_type == 'LeadContacts' && ($index == 'email' || $index == 'accountname')) {
                    echo "\n<!-- show_related_leads_true -->\n";
                } else {
                    echo count($record_ids[$module_type][$index]) . " result(s) matching on $message<BR>";
                }
            }
        }
    }
}

$GLOBALS['sugar_config']['save_query'] = 'populate_only';
?>
