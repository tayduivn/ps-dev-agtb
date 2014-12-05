<?php
$dictionary['pmse_BpmGroupUser'] = array(
	'table'=>'pmse_bpm_group_user',
	'audited'=>false,
	'activity_enabled'=>false,
		'duplicate_merge'=>true,
		'reassignable'=>false,
		'fields'=>array ('user_id' =>
  array (
    'required' => true,
    'name' => 'user_id',
    'vname' => 'User Identifier',
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
    'len' => '36',
    'size' => '36',
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
VardefManager::createVardef('pmse_BpmGroupUser','pmse_BpmGroupUser', array('basic','assignable'));