<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 

$hook_array['after_save'] = Array();
// START jvink - IBMSyncHelper (should be the first after_save hook !)
$hook_array['after_save'][] = Array(1, 'IBMSyncHelper', 'custom/modules/IBMSyncHelperLogicHooks.php','IBMSyncHelperLogicHooks', 'IBMSyncHelper');
// END jvink

// moved to SyncHelper
//$hook_array['after_save'][] = Array(1, 'setOpptyDecisionDate', 'custom/modules/ibm_RevenueLineItemRoadmap/ibm_RevenueLineItemRoadmapLogicHooks.php','ibm_RevenueLineItemRoadmapLogicHooks', 'setOpptyDecisionDate');

