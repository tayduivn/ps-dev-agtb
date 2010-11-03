<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'updateProbability', 'custom/modules/Opportunities/updateProbability.php','updateProbability', 'update'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'verifyCloseDate'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'applySugarExpress'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'applyOSSC'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'applyTC'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'setCloseDate'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'insertSalesStageAudits'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'createTaskForRejectedOpp');
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'createTaskForTrainingOpps');
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'sixtyMinOppTouched');
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'sendCustomerEmail');
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'pushToPardot');
/* @author: DEE; ITREQUEST 15782; Turn off Work-flow - Opp Validity Check */
//$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'validateOpportunity'); 
/* END SUGARINTERNAL CUSTOMIZATION */
$hook_array['before_save'][] = Array(1, 'Opportunities push feed', 'modules/Opportunities/SugarFeeds/OppFeed.php','OppFeed', 'pushFeed'); 
$hook_array['before_save'][] = Array(1, 'OpportunitiesHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'generateRenewalOpportunity'); 
$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'clearDependantValues');

$hook_array['before_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'updateMaxScore');
$hook_array['before_save'][] = Array(1, 'startApproval', 'custom/si_logic_hooks/DiscountCodes/AccountOppApproval.php','AccountOppApproval', 'startApproval');

$hook_array['before_save'][] = Array(1, 'updateAmount', 'custom/si_logic_hooks/Opportunities/updateAmount.php','updateAmount', 'update');
$hook_array['before_save'][] = Array(1, 'updateOppSalesStage', 'custom/si_logic_hooks/Opportunities/updateOppSalesStage.php','updateOppSalesStage', 'checkSalesStageUpdate');
//$hook_array['before_save'][] = Array(1, 'updateAccountPartner', 'custom/si_logic_hooks/Opportunities/updateAccountPartner.php','updateAccountPartner', 'update');


$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'setTeamFromLeadGroup'); 
$hook_array['after_save'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'updateSubscriptionOrderChange');

$hook_array['after_save'][] = Array(1, 'updateCARep', 'custom/si_logic_hooks/Opportunities/updateCARep.php','updateCARep', 'update'); 

$hook_array['before_delete'] = Array(); 
$hook_array['before_delete'][] = Array(1, 'OpportunityHooks', 'custom/si_logic_hooks/OpportunityHooks.php','OpportunityHooks', 'checkDeletePermissions'); 



?>
