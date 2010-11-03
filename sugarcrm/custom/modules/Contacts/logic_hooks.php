<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry');
$hook_array['before_save'][] = Array(1, 'stateMap', 'custom/si_logic_hooks/StateVerify.php', 'StateVerify', 'AdjustInvalidState');
$hook_array['before_save'][] = Array(1, 'updateRelatedLeads', 'custom/si_logic_hooks/Contacts/ContactHooks.php','ContactHooks', 'updateRelatedLeads');
//$hook_array['before_save'][] = Array(1, 'setSupportAuthorized', 'custom/si_logic_hooks/Contacts/ContactHooks.php','ContactHooks', 'setSupportAuthorized');
$hook_array['before_save'][] = Array(1, 'checkPortalUserName', 'custom/si_logic_hooks/Contacts/ContactHooks.php','ContactHooks', 'checkPortalUserName');

$hook_array['after_save'] = array();
$hook_array['after_save'][] = Array(1, 'updateMaxScore', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'updateMaxScore');
?>
