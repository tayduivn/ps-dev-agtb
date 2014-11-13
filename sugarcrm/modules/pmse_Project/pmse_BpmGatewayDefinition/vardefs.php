<?php
$dictionary['pmse_BpmGatewayDefinition'] = array(
	'table'=>'pmse_bpm_gateway_definition',
	'audited'=>false,
	'activity_enabled'=>false,
		'duplicate_merge'=>true,
		'fields'=>array ('prj_id' =>
  array (
    'required' => true,
    'name' => 'prj_id',
    'vname' => 'Project id',
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
'pro_id' =>
  array (
    'required' => true,
    'name' => 'pro_id',
    'vname' => 'Process id',
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
'execution_mode' =>
  array (
    'required' => true,
    'name' => 'execution_mode',
    'vname' => 'script to be executed',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'DEFAULT',
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
    'len' => '10',
    'size' => '10',
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
VardefManager::createVardef('pmse_BpmGatewayDefinition','pmse_BpmGatewayDefinition', array('basic','assignable'));