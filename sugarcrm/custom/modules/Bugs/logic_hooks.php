<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'label1', 'custom/modules/Bugs/customPortalLogic.php','BugCustomPortal', 'sendUpdates'); 
$hook_array['before_save'][] = Array(1, 'assignmentRules', 'custom/si_logic_hooks/Bugs/BugHooks.php','BugHooks', 'assignmentRules'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 



?>
