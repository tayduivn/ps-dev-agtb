<?php
$module_menu=array();

global $current_language, $mod_strings, $app_strings, $sugar_config;

$lead_mod_strings = return_module_language($current_language, 'Leads');
$lead_accounts_mod_strings = return_module_language($current_language, 'LeadAccounts');
$lead_contacts_mod_strings = return_module_language($current_language, 'LeadContacts');
$touchpoints_mod_strings = return_module_language($current_language, 'Touchpoints');

if(ACLController::checkAccess('LeadAccounts', 'edit', true))$module_menu[]=Array("index.php?module=LeadAccounts&action=EditView&return_module=LeadAccounts&return_action=DetailView", $lead_accounts_mod_strings['LNK_NEW_LEAD'],"CreateLeadAccounts", 'Leads');
if(ACLController::checkAccess('LeadContacts', 'edit', true))$module_menu[]=Array("index.php?module=LeadContacts&action=EditView&return_module=LeadContacts&return_action=DetailView", $lead_contacts_mod_strings['LNK_NEW_LEAD'],"CreateLeadContacts", 'Leads');
if(ACLController::checkAccess('LeadContacts', 'edit', true))$module_menu[]=Array("index.php?module=LeadContacts&action=ImportVCard", $mod_strings['LNK_IMPORT_VCARD'],"CreateLeadContacts", 'Leads');
if(ACLController::checkAccess('Touchpoints', 'edit', true))$module_menu[]=Array("index.php?module=Touchpoints&action=EditView&return_module=Touchpoints&return_action=DetailView", $touchpoints_mod_strings['LNK_NEW_TOUCHPOINT'],"CreateTouchpoints", 'Leads');
if(ACLController::checkAccess('LeadAccounts', 'list', true))$module_menu[]=Array("index.php?module=LeadAccounts&action=index&return_module=LeadAccounts&return_action=DetailView", $lead_accounts_mod_strings['LNK_LEAD_LIST'],"LeadAccounts", 'Leads');
if(ACLController::checkAccess('LeadContacts', 'list', true))$module_menu[]=Array("index.php?module=LeadContacts&action=index&return_module=LeadContacts&return_action=DetailView", $lead_contacts_mod_strings['LNK_LEAD_LIST'],"LeadContacts", 'Leads');
if(ACLController::checkAccess('Touchpoints', 'list', true))$module_menu[]=Array("index.php?module=Touchpoints&action=index&return_module=Touchpoints&return_action=DetailView", "Touchpoints","Touchpoints", "Leads");

if(empty($sugar_config['disc_client'])){
        if(ACLController::checkAccess('LeadAccounts', 'list', true))$module_menu[] =Array("index.php?module=Reports&action=index&view=leads", $mod_strings['LNK_LEAD_REPORTS'],"LeadReports", 'Leads');
}

if(ACLController::checkAccess('LeadAccounts', 'import', true))$module_menu[]=Array("index.php?module=Import&import_module=LeadAccounts&action=Step1", $lead_mod_strings['LBL_IMPORT_LEAD_ACCOUNTS'],"Import", 'Leads');
if(ACLController::checkAccess('LeadContacts', 'import', true))$module_menu[]=Array("index.php?module=Import&import_module=LeadContacts&action=Step1", $lead_mod_strings['LBL_IMPORT_LEAD_CONTACTS'],"Import", 'Leads');
if(ACLController::checkAccess('Touchpoints', 'import', true))$module_menu[]=Array("index.php?module=Import&import_module=Touchpoints&action=Step1", $touchpoints_mod_strings['LBL_IMPORT_TOUCHPOINTS'],"Import", 'Leads');
// BEGIN SUGARINTERNAL CUSTOMIZATION - LEAD QUAL RANDOM LEAD FROM POOL
$altUsers = array(
        'sadek',
        'rmeeker',
);
$userGroups = array(
        'Leads_HotCorpMktg' =>  'ebdd06a4-6794-f03a-c0f8-4460e9bde0d8',
        'Leads_HotEntMktg'  =>  'b73f0af6-c9b7-f485-32f7-4782e5af0c62',
        'Leads_HotMktg'  =>  'c15afb6d-a403-b92a-f388-4342a492003e',
        'Leads_Partner'   =>  '2c780a1f-1f07-23fd-3a49-434d94d78ae5',
        'Leads_Installer'   =>  'cef7c0a7-4ab0-ae95-2200-4342a4f55812',
        'Leads_escalation'   =>  'bf6f1e6b-f6bf-01e5-69e3-4a833bf57cfd',
);

if($GLOBALS['current_user']->check_role_membership('Leads Scrubber Role') || in_array($GLOBALS['current_user']->user_name, $altUsers)){
        $module_menu[] = array("####", "- Lead Qual Scrub Bucket -", "");
        $module_menu[] = array("index.php?module=Touchpoints&action=LeadQualScoredLead&user={$userGroups['Leads_HotMktg']}", "Leads_HotMktg", "Touchpoints", "Leads");
        $module_menu[] = array("index.php?module=Touchpoints&action=LeadQualScoredLead&user={$userGroups['Leads_Partner']}", "Leads_Partner", "Touchpoints", "Leads");
        $module_menu[] = array("index.php?module=Touchpoints&action=LeadQualScoredLead&user={$userGroups['Leads_escalation']}", "Leads_escalation", "Touchpoints", "Leads");
}
// END SUGARINTERNAL CUSTOMIZATION - LEAD QUAL RANDOM LEAD FROM POOL
