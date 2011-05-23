<?php
$hook_version = 1;
$hook_array = Array();

$hook_array['process_record'] = Array();
$hook_array['process_record'][] = Array(1, 'setListViewIcon', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'setListViewIcon');

$hook_array['after_retrieve'] = Array();
$hook_array['after_retrieve'][] = Array(1, 'getTags', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'getTags');

$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'setCMROrClientNumber', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'setCMROrClientNumber');
$hook_array['before_save'][] = Array(1, 'clientIdFromParent', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'clientIdFromParent');
$hook_array['before_save'][] = Array(1, 'setAssignedUserRelationship', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'setAssignedUserRelationship');
$hook_array['before_save'][] = Array(1, 'addOppsToSimCalcQueue', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'addOppsToSimCalcQueue');

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'saveTags', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'saveTags');
$hook_array['after_save'][] = Array(1, 'setAssignedUserRelationship', 'custom/modules/Accounts/AccountLogicHooks.php', 'AccountLogicHooks', 'setAssignedUserRelationship');
