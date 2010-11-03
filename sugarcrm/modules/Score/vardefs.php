<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */
$dictionary['Score'] = array(
	'table' => 'score',
	'audited' => false,
	'fields' => array (
		'rule_id' => array (
			'name' => 'rule_id',
			'type' => 'varchar',
			'vname' => 'LBL_RULE_ID',
			'len' => 64,
			'comment' => 'The id of the rule that generated this score row',
			'unified_search' => false,
			'audited' => false,
		),
		'rule_data' => array (
			'name' => 'rule_data',
			'type' => 'varchar',
			'vname' => 'LBL_RULE_DATA',
			'len' => 255,
			'comment' => 'The data that was used by the rule in generating this score row',
			'unified_search' => false,
			'audited' => false,
		),
		'target_id' => array (
			'name' => 'target_id',
			'type' => 'id',
			'vname' => 'LBL_TARGET_ID',
			'comment' => 'The id of the record that this score row should affect',
			'unified_search' => false,
			'audited' => false,
		),
		'target_module' => array (
			'name' => 'target_module',
			'type' => 'varchar',
			'vname' => 'LBL_TARGET_MODULE',
			'len' => 255,
			'comment' => 'The module that this score row should affect',
			'unified_search' => false,
			'audited' => false,
		),
		'source_id' => array (
			'name' => 'source_id',
			'type' => 'id',
			'vname' => 'LBL_SOURCE_ID',
			'comment' => 'The id of the record that was the source of this score row',
			'unified_search' => false,
			'audited' => false,
		),
		'source_module' => array (
			'name' => 'source_module',
			'type' => 'varchar',
			'vname' => 'LBL_SOURCE_MODULE',
			'len' => 255,
			'comment' => 'The module that was the source of this score row',
			'unified_search' => false,
			'audited' => false,
		),
		'score_add' => array (
			'name' => 'score_add',
			'type' => 'int',
			'vname' => 'LBL_SCORE_ADD',
			'len' => 11,
			'comment' => 'The amount this row should add or subtract from the total score',
			'unified_search' => false,
			'audited' => false,
		),
		'score_mul' => array (
			'name' => 'score_mul',
			'type' => 'float',
			'dbType' => 'double',
			'vname' => 'LBL_SCORE_MUL',
			'comment' => 'The amount this row should add or subtract from the total score multiplier',
			'unified_search' => false,
			'audited' => false,
		),
		'is_dirty' => array (
			'name' => 'is_dirty',
			'type' => 'bool',
			'vname' => 'LBL_IS_DIRTY',
			'len' => 1,
			'comment' => 'If the data that generated this score row was updated',
			'unified_search' => false,
			'audited' => false,
		),
	),
	'indices' => 
	array( 
		array(
			'name' => 'idx_score_dirty',
			'type' => 'index',
			'fields' => array('is_dirty'),
		),
		array(
			'name' => 'idx_score_source',
			'type' => 'index',
			'fields' => array('source_id'),
		),
		array(
			'name' => 'idx_score_target',
			'type' => 'index',
			'fields' => array('target_id'),
		),
	),
);
			

require_once('include/SugarObjects/VardefManager.php');
VardefManager::createVardef('Score','Score', array('basic'));
