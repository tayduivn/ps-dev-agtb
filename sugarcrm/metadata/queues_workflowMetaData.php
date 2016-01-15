<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
// $Id: queues_workflowMetaData.php 51719 2009-10-22 17:18:00Z mitani $
//FILE SUGARCRM flav=int ONLY

$dictionary['queues_workflow'] = array('table' => 'queues_workflow', 
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_ID',
			'type' => 'id',
			'required' => true,
			'reportable'=>false,
		),
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
			'reportable'=>false,
		),
		'date_entered' => array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
		),
		'date_modified' => array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
		),
		'queue_id'		=> array (
			'name' 		=> 'queue_id',
			'vname' 	=> 'LBL_QUEUE_ID',
			'type' 		=> 'id',
			'required' 	=> true,
			'reportable'=>false,
		),
		'wf_function' 	=> array (
			'name'		=> 'wf_function',
			'vname'		=> 'LBL_WORKFLOW_FUNCTION',
			'type'		=> 'varchar',
			'len'		=> 50,
			'required'	=> true,
			'reportable'=> false,
		),
		'wf_precedence' => array (
			'name'		=> 'wf_precedence',
			'vname'		=> 'LBL_WORKFLOW_PRECEDENCE',
			'type'		=> 'tinyint',
			'len'		=> 4,
			'required'	=> true,
			'reportable'=> false,
		),
	),
	'relationships' => array (
		'queues_workflow_rel' => array(
			'lhs_module'		=> 'Queues',
			'lhs_table'			=> 'queues',
			'lhs_key' 			=> 'id',
			'rhs_module'		=> 'Workflow',
			'rhs_table'			=> 'workflow',
			'rhs_key' 			=> 'id',
			'relationship_type' => 'many-to-many',
			'join_table'		=> 'queues_workflow', 
			'join_key_rhs'		=> 'id', 
			'join_key_lhs'		=> 'id'			
		),
	), /* end relationship definitions */
	'indices' => array (
		array(
			'name' => 'queues_itemspk',
			'type' =>'primary',
			'fields' => array(
				'id'
			)
		),
		array(
		'name' =>'idx_queue_id',
		'type'=>'index',
		'fields' => array(
			'queue_id'
			)
		),
		array(
		'name' => 'compidx_queue_id_wf_precedence',
		'type' => 'alternate_key',
		'fields' => array (
			'queue_id',
			'wf_precedence'
			),
		),
	), /* end indices */
);


?>
