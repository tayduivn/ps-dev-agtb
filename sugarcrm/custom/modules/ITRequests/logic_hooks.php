<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'setAssignedUserIdFromDepartment', 'custom/si_logic_hooks/ITRequests/setAssignedUserIdFromDepartment.php','setAssignedUserIdFromDepartment', 'setAssignedToId');
$hook_array['before_save'][] = Array(1, 'updateWorkLogOnSave', 'custom/si_logic_hooks/ITRequests/updateResolution.php','updateWorkLogOnSave', 'update');
$hook_array['before_save'][] = Array(1, 'sendUpdateEmail', 'modules/ITRequests/sendUpdateEmail.php','sendUpdateEmail', 'send');
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler');


$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'assignCaseItrToAccount', 'custom/si_logic_hooks/ITRequests/AssignCaseItrToAccount.php','assignCaseItrToAccount', 'assignITR');
