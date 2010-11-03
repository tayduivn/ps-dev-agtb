<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['after_retrieve'] = Array(); 
$hook_array['after_retrieve'][] = Array(1, 'setDisplayName', 'custom/modules/Subscriptions/setDisplayName.php','setDisplayName', 'set'); 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler');; 
$hook_array['before_save'][] = Array(1, 'pushToNetSuite', 'custom/modules/Subscriptions/pushToNetSuite.php', 'pushToNetSuite', 'push'); 
$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'subscriptionAccountUpdate', 'custom/modules/Subscriptions/accountUpdate.php', 'subscriptionAccountUpdate', 'update');


