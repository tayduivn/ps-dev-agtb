<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 

// BEGIN sadek - SIMILAR OPPORTUNITY CALCULATOR
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'addOppsToSimCalcQueue', 'custom/modules/ibm_revenueLineItems/ibm_revenueLineItemLogicHooks.php', 'ibm_revenueLineItemLogicHooks', 'addOppsToSimCalcQueue');
// END sadek - SIMILAR OPPORTUNITY CALCULATOR
// BEGIN sadek - SugarAlerts logic hooks
$hook_array['before_save'][] = Array(1, 'SugarAlerts before save', 'custom/modules/ibm_revenueLineItems/ibm_revenueLineItemLogicHooks.php','ibm_revenueLineItemLogicHooks', 'sugarAlerts');
// END sadek - SugarAlerts logic hooks

// position, file, function 
$hook_array['after_save'] = Array();
// START jvink - IBMSyncHelper (should be the first after_save hook !)
$hook_array['after_save'][] = Array(1, 'IBMSyncHelper', 'custom/modules/IBMSyncHelperLogicHooks.php','IBMSyncHelperLogicHooks', 'IBMSyncHelper');
// END jvink 
$hook_array['after_save'][] = Array(1, 'setLineItemSpecialist', 'custom/modules/ibm_revenueLineItems/ibm_revenueLineItemLogicHooks.php','ibm_revenueLineItemLogicHooks', 'setLineItemSpecialist');
// BEGIN sadek - SugarAlerts logic hooks
$hook_array['after_save'][] = Array(1, 'SugarAlerts after save', 'custom/modules/ibm_revenueLineItems/ibm_revenueLineItemLogicHooks.php','ibm_revenueLineItemLogicHooks', 'sugarAlerts');
// END sadek - SugarAlerts logic hooks

// moved to SyncHelper
//$hook_array['after_save'][] = Array(1, 'setAverageProbability', 'custom/modules/ibm_revenueLineItems/ibm_revenueLineItemLogicHooks.php','ibm_revenueLineItemLogicHooks', 'setAverageProbability');

