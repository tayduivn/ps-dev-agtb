<?php
$dictionary['pmse_BpmActivityUser'] = array(
	'table'=>'pmse_bpm_activity_user',
	'audited'=>false,
	'activity_enabled'=>false,
		'duplicate_merge'=>true,
		'fields'=>array ('pro_id' =>
  array (
    'required' => true,
    'name' => 'pro_id',
    'vname' => 'Process identifier',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => '0',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' =>
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '36',
    'size' => '36',
  ),
'act_user_type' =>
  array (
    'required' => true,
    'name' => 'act_user_type',
    'vname' => 'user or group',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => '',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' =>
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '32',
    'size' => '32',
  ),
'act_user_id' =>
  array (
    'required' => true,
    'name' => 'act_user_id',
    'vname' => 'User Identifier for who can be assigned to this Case',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => '',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' =>
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
'act_group_id' =>
  array (
    'required' => true,
    'name' => 'act_group_id',
    'vname' => 'Group or any other Identifier for groups to be assigned to this case',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => '',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' =>
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
		'unified_search'=>true,
	);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('pmse_BpmActivityUser','pmse_BpmActivityUser', array('basic','assignable'));