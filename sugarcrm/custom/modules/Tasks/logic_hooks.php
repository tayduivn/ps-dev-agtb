<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'task_before_save', 'custom/si_logic_hooks/Tasks/TaskHooks.php', 'TaskHooks', 'task_before_save');
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
/*
** @author: Jon Whitcraft
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #8421:
** Description: Add Logic Hook to send the UpdateEmail for the task modules
*/
$hook_array['before_save'][] = Array(1, 'sendUpdateEmail', 'custom/modules/Tasks/sendUpdateEmail.php','sendUpdateEmail', 'send');
// end SugarInternal Customization


?>
