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

$dictionary['pmse_BpmFlow'] = array(
	'table'=>'pmse_bpm_flow',
	'audited'=>false,
	'activity_enabled'=>false,
		'duplicate_merge'=>true,
		'reassignable'=>false,
		'fields'=>array ('cas_id' =>
  array (
      'required' => true,
      'name' => 'cas_id',
      'vname' => 'Unique Identifier for this Case',
      'type' => 'int',
      'massupdate' => false,
      'default' => null,
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_index' =>
  array (
      'required' => true,
      'name' => 'cas_index',
      'vname' => 'Case flow index a sequential number relative to each case',
      'type' => 'int',
      'massupdate' => false,
      'default' => null,
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'pro_id' =>
  array (
    'required' => true,
    'name' => 'pro_id',
    'vname' => 'Process identifier',
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
    'calculated' => false,
    'len' => '36',
    'size' => '36',
  ),
'cas_previous' =>
  array (
      'required' => true,
      'name' => 'cas_previous',
      'vname' => 'Previous flow',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_reassign_level' =>
  array (
      'required' => true,
      'name' => 'cas_reassign_level',
      'vname' => 'Reassign nesting Level in case an adhoc or reassign derivation has been used',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'bpmn_id' =>
  array (
    'required' => true,
    'name' => 'bpmn_id',
    'vname' => 'Bpmn Object identifier',
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
    'calculated' => false,
    'len' => '36',
    'size' => '36',
  ),
'bpmn_type' =>
  array (
    'required' => true,
    'name' => 'bpmn_type',
    'vname' => 'Bpmn Object type',
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
    'calculated' => false,
    'len' => '32',
    'size' => '32',
  ),
'cas_assignment_method' =>
  array (
    'required' => true,
    'name' => 'cas_assignment_method',
    'vname' => '',
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
    'calculated' => false,
    'len' => '32',
    'size' => '32',
  ),
'cas_user_id' =>
  array (
    'required' => true,
    'name' => 'cas_user_id',
    'vname' => 'User Identifier for who started the Case',
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
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
'cas_thread' =>
  array (
      'required' => true,
      'name' => 'cas_thread',
      'vname' => 'Case thread',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_flow_status' =>
  array (
    'required' => true,
    'name' => 'cas_flow_status',
    'vname' => '',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'OPEN',
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
    'calculated' => false,
    'len' => '32',
    'size' => '32',
  ),
'cas_sugar_module' =>
  array (
    'required' => true,
    'name' => 'cas_sugar_module',
    'vname' => 'Sugarcrm module name',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'ProcessMaker',
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
    'calculated' => false,
    'len' => '128',
    'size' => '128',
  ),
'cas_sugar_object_id' =>
  array (
    'required' => true,
    'name' => 'cas_sugar_object_id',
    'vname' => 'id for the current object in original module',
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
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
'cas_sugar_action' =>
  array (
    'required' => true,
    'name' => 'cas_sugar_action',
    'vname' => 'Sugarcrm module name',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'DetailView',
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
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
'cas_adhoc_type' =>
  array (
    'required' => true,
    'name' => 'cas_adhoc_type',
    'vname' => 'PMSE Adhoc behavior',
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
    'calculated' => false,
    'len' => '40',
    'size' => '40',
  ),
'cas_adhoc_parent_id' =>
  array (
    'required' => true,
    'name' => 'cas_adhoc_parent_id',
    'vname' => 'PMSE adhoc parent id',
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
'cas_adhoc_actions' =>
  array (
    'required' => true,
    'name' => 'cas_adhoc_actions',
    'vname' => 'PMSE adhoc actions',
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
    'len' => '255',
    'size' => '255',
  ),
'cas_task_start_date' =>
  array (
    'required' => false,
    'name' => 'cas_task_start_date',
    'vname' => 'when the case was was delegated to a activity/task.',
    'type' => 'datetimecombo',
    'massupdate' => true,
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
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
'cas_delegate_date' =>
  array (
    'required' => false,
    'name' => 'cas_delegate_date',
    'vname' => 'when the case was was created.',
    'type' => 'datetimecombo',
    'massupdate' => true,
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
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
'cas_start_date' =>
  array (
    'required' => false,
    'name' => 'cas_start_date',
    'vname' => 'when the case was was updated.',
    'type' => 'datetimecombo',
    'massupdate' => true,
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
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
'cas_finish_date' =>
  array (
    'required' => false,
    'name' => 'cas_finish_date',
    'vname' => 'when the case was was updated.',
    'type' => 'datetimecombo',
    'massupdate' => true,
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
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
'cas_due_date' =>
  array (
    'required' => false,
    'name' => 'cas_due_date',
    'vname' => 'when the case was was updated.',
    'type' => 'datetimecombo',
    'massupdate' => true,
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
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
'cas_queue_duration' =>
  array (
      'required' => true,
      'name' => 'cas_queue_duration',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_duration' =>
  array (
      'required' => true,
      'name' => 'cas_duration',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_delay_duration' =>
  array (
      'required' => true,
      'name' => 'cas_delay_duration',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_started' =>
  array (
      'required' => true,
      'name' => 'cas_started',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_finished' =>
  array (
      'required' => true,
      'name' => 'cas_finished',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_delayed' =>
  array (
      'required' => true,
      'name' => 'cas_delayed',
      'vname' => '',
      'type' => 'int',
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
     'calculated' => false,
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
),
'indices' => array(
        array(
            'name' => 'idx_pmse_bpm_flow_cas_flow_status',
            'type' => 'index',
            'fields' => array(
                'bpmn_id',
                'cas_flow_status',
            ),
        ),
        array(
            'name' => 'idx_pmse_bpm_flow_status',
            'type' => 'index',
            'fields' => array(
                'cas_flow_status',
            ),
        ),
        array(
            'name' => 'idx_pmse_bpm_flow_parent',
            'type' => 'index',
            'fields' => array(
                'cas_sugar_object_id',
                'cas_sugar_module',
            ),
        ),
        array(
            'name' => 'idx_pmse_bpm_flow_cas_id',
            'type' => 'index',
            'fields' => array(
                'cas_id',
            ),
        ),
        array(
            'name' => 'idx_pmse_bpm_flow_parent_and_cas_id',
            'type' => 'index',
            'fields' => array(
                'cas_sugar_object_id',
                'cas_sugar_module',
                'cas_index',
            ),
        ),
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
		'unified_search'=>true,
	);
if (!class_exists('VardefManager')){
        require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('pmse_BpmFlow','pmse_BpmFlow', array('basic','assignable'));
