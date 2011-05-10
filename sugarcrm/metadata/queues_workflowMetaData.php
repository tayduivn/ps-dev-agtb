<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
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
