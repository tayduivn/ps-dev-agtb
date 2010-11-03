<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'CleanUpLead', 'custom/si_logic_hooks/Leads/CleanUpLead.php','CleanUpLead', 'cleanUpData'); 
$hook_array['before_save'][] = Array(1, 'lead_qual', 'modules/Leads/LeadQualHandler.php','LeadQualHandler', 'LeadQualHandler'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'leadQualRoundRobin', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadQualRoundRobin'); 
$hook_array['before_save'][] = Array(1, 'leadAutoRoute', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadAutoRoute'); 
$hook_array['before_save'][] = Array(1, 'leadScore', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadScore'); 
$hook_array['before_save'][] = Array(1, 'leadCountryRegionMap', 'custom/si_logic_hooks/Leads/LeadHooks.php','LeadHooks', 'leadCountryRegionMap'); 
$hook_array['before_save'][] = Array(1, 'setLeadPassDate', 'custom/si_logic_hooks/Leads/LeadHooks.php','LeadHooks', 'setLeadPassDate'); 
$hook_array['before_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry'); 
$hook_array['before_save'][] = Array(1, 'leadPassConvertRedirect', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'leadPassConvertRedirect'); 
$hook_array['before_save'][] = Array(99, 'normalize phone numbers', 'fonality/include/normalizePhone/custom_logic_hooks.php','Normalize', 'normalize_phones'); 
$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1, 'postAutoWelcomeEmail', 'custom/si_logic_hooks/Leads/LeadQualAutomation.php','LeadQualAutomation', 'postAutoWelcomeEmail'); 
$hook_array['after_save'][] = Array(1, 'lead_qual', 'modules/Leads/LeadQualHandler.php','LeadQualHandler', 'LeadQualHandler'); 
$hook_array['after_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry'); 



?>