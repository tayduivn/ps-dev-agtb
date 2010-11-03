<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 

$hook_array['before_save'][] = Array(1, 'applyOSSC', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'applySugarExpress'); 
$hook_array['before_save'][] = Array(1, 'applyOSSC', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'applyOSSC'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'caseportalAssignment', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'caseportalAssignment'); 
$hook_array['before_save'][] = Array(1, 'mapCaseToRep', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'mapCaseToRep'); 
$hook_array['before_save'][] = Array(1, 'partnerAssignmentMap', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'partnerAssignmentMap'); 
$hook_array['before_save'][] = Array(1, 'caseSurveyInvite', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'caseSurveyInvite'); 
$hook_array['before_save'][] = Array(1, 'fillInSupportServiceLevel', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'fillInSupportServiceLevel'); 
$hook_array['before_save'][] = Array(1, 'caseRoutingHandler', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'caseRoutingHandler');
$hook_array['before_save'][] = Array(1, 'setCaseOnBoardFlag', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'setCaseOnBoardFlag'); 
$hook_array['before_save'][] = Array(1, 'scoreCaseHook', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'scoreCaseHook'); 
$hook_array['before_save'][] = Array(1, 'updateResTime', 'custom/modules/Cases/customPortalLogicCases.php','CaseCustomPortal', 'updateResTime');
$hook_array['before_save'][] = Array(1, 'slaMetLogicHook', 'custom/si_logic_hooks/CaseSlaLogicHook.php','CaseSlaLogicHook', 'slaMetCaseHook'); 
$hook_array['before_save'][] = Array(1, 'Cases push feed', 'modules/Cases/SugarFeeds/CaseFeed.php','CaseFeed', 'pushFeed'); 
$hook_array['before_save'][] = Array(1, 'sendUpdateEmails', 'custom/modules/Cases/customPortalLogicCases.php','CaseCustomPortal', 'sendUpdates');
$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['after_save'][] = Array(1, 'saveMyEscalation', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'saveMyEscalation'); 
$hook_array['after_retrieve'] = Array(); 
$hook_array['after_retrieve'][] = Array(1, 'showMyEscalation', 'custom/si_logic_hooks/CaseHooks.php','CaseHooks', 'showMyEscalation'); 



?>