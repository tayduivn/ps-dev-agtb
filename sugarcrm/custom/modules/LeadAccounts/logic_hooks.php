<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 
// position, file, function
$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'leadAutoRoute', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadAutoRoute'); 
$hook_array['before_save'][] = Array(1, 'leadCountryRegionMap', 'custom/si_logic_hooks/LeadAccounts/LeadAccountsHooks.php','LeadAccountsHooks', 'leadCountryRegionMap');
$hook_array['before_save'][] = Array(1, 'setLeadPassDate', 'custom/si_logic_hooks/LeadAccounts/LeadAccountsHooks.php','LeadAccountsHooks', 'setLeadPassDate');
$hook_array['before_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry');
$hook_array['before_save'][] = Array(1, 'stateMap', 'custom/si_logic_hooks/StateVerify.php', 'StateVerify', 'AdjustInvalidState');
$hook_array['before_save'][] = Array(1, 'updateLeadContactAssignedFields', 'custom/si_logic_hooks/LeadAccounts/LeadAccountsHooks.php','LeadAccountsHooks', 'updateLeadContactAssignedFields');
$hook_array['before_save'][] = Array(1, 'leadPassConvertRedirect', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadPassConvertRedirect');

$hook_array['after_save'] = array();
$hook_array['after_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry');
