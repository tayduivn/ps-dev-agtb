<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'AccountHooks', 'custom/si_logic_hooks/Accounts/AccountHooks.php','AccountHooks', 'applySugarExpress');
$hook_array['before_save'][] = Array(1, 'applyOSSC', 'custom/si_logic_hooks/Accounts/AccountHooks.php','AccountHooks', 'applyOSSC');
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'accountCountryRegionMap', 'custom/si_logic_hooks/Accounts/AccountHooks.php','AccountHooks', 'accountCountryRegionMap');
$hook_array['before_save'][] = Array(1, 'CountryVerify', 'custom/si_logic_hooks/CountryVerify.php','CountryVerify', 'LogInvalidCountry');
$hook_array['before_save'][] = Array(1, 'stateMap', 'custom/si_logic_hooks/StateVerify.php', 'StateVerify', 'AdjustInvalidState');
$hook_array['before_save'][] = Array(1, 'AccountHooks', 'custom/si_logic_hooks/Accounts/AccountHooks.php','AccountHooks', 'setSupportServiceLevel');

$hook_array['before_save'][] = Array(1, 'reassignContracts', 'custom/si_logic_hooks/Accounts/AccountHooks.php','AccountHooks', 'reassignContracts');
$hook_array['before_save'][] = Array(1, 'startApproval', 'custom/si_logic_hooks/DiscountCodes/AccountOppApproval.php','AccountOppApproval', 'startApproval');


$hook_array['before_save'][] = Array(1, 'updateAddresses', 'custom/si_logic_hooks/Accounts/updateAddresses.php','updateAddresses', 'update');
