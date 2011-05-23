<?php
$hook_version = 1;
$hook_array = Array();

$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'setType', 'custom/modules/ibm_WinPlans/ibm_WinPlanLogicHooks.php', 'ibm_WinPlanLogicHooks', 'setType');
$hook_array['before_save'][] = Array(1, 'setApprovalDate', 'custom/modules/ibm_WinPlanGeneric/ibm_WinPlanGenericLogicHooks.php', 'ibm_WinPlanGenericLogicHooks', 'setApprovalDate');
