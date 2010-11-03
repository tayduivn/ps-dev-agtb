<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1, 'TouchpointHooks', 'custom/si_logic_hooks/Touchpoints/TouchpointHooks.php','TouchpointHooks', 'rollupTouchpointData_fp'); 
$hook_array['after_save'][] = Array(1, 'Touchpoints update interaction', 'modules/Touchpoints/Interactions/TouchpointsInteraction.php','TouchpointsInteraction', 'updateInteraction'); 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'CleanUpLead', 'custom/si_logic_hooks/Leads/CleanUpLead.php','CleanUpLead', 'cleanUpData'); 
$hook_array['before_save'][] = Array(1, 'setLeadGroupFromValues', 'custom/si_logic_hooks/Touchpoints/TouchpointHooks.php','TouchpointHooks', 'setLeadGroupFromValues'); 
$hook_array['before_save'][] = Array(1, 'touchpointCountryRegionMap', 'custom/si_logic_hooks/Touchpoints/TouchpointHooks.php','TouchpointHooks', 'touchpointCountryRegionMap'); 
$hook_array['before_save'][] = Array(1, 'stateMap', 'custom/si_logic_hooks/StateVerify.php','StateVerify', 'AdjustInvalidState'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 



?>