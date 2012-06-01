<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['SchedulersJob'] = array('table' => 'job_queue',
    'comment' => 'Job queue keeps the list of the jobs executed by this instance',
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_NAME',
			'type' => 'id',
			'len' => '36',
			'required' => true,
			'reportable'=>false,
		),
	   'name'=>
	    array(
    	    'name'=>'name',
    	    'vname'=> 'LBL_NAME',
    	    'type'=>'name',
    	    'link' => true, // bug 39288
    		'dbType' => 'varchar',
    	    'len'=>255,
            'required'=>true,
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
		'scheduler_id' => array (
			'name' => 'scheduler_id',
			'vname' => 'LBL_SCHEDULER',
			'type' => 'id',
			'required' => false,
			'reportable' => false,
		),
		'execute_time' => array (
			'name' => 'execute_time',
			'vname' => 'LBL_EXECUTE_TIME',
			'type' => 'datetime',
			'required' => true,
		),
		'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'options'	=> 'schedulers_times_dom',
			'len' => 20,
			'required' => true,
			'reportable' => true,
			'readonly' => true,
		),
		'resolution' => array (
			'name' => 'resolution',
			'vname' => 'LBL_RESOLUTION',
			'type' => 'enum',
			'options'	=> 'schedulers_resolution_dom',
			'len' => 20,
			'required' => true,
			'reportable' => true,
		    'readonly' => true,
		),
		'message' => array (
			'name' => 'message',
			'vname' => 'LBL_MESSAGE',
			'type' => 'text',
			'required' => false,
			'reportable' => false,
		),
		'target' => array (
			'name' => 'target',
			'vname' => 'LBL_TARGET',
			'type' => 'varchar',
			'len' => 255,
			'required' => true,
			'reportable' => true,
		),
		'data' => array (
			'name' => 'data',
			'vname' => 'LBL_DATA',
			'type' => 'text',
			'required' => false,
			'reportable' => true,
		),
		'requeue' => array (
			'name' => 'requeue',
			'vname' => 'LBL_REQUEUE',
			'type' => 'bool',
		    'default' => 0,
			'required' => false,
			'reportable' => true,
		),
		'retry_count' => array (
			'name' => 'retry_count',
			'vname' => 'LBL_RETRY_COUNT',
			'type' => 'tinyint',
			'required' => false,
			'reportable' => true,
		),
		'failure_count' => array (
			'name' => 'failure_count',
			'vname' => 'LBL_FAIL_COUNT',
			'type' => 'tinyint',
			'required' => false,
			'reportable' => true,
		    'readonly' => true,
		),
		'job_delay' => array (
			'name' => 'job_delay',
			'vname' => 'LBL_INTERVAL',
			'type' => 'int',
			'required' => false,
			'reportable' => false,
		),
		'client' => array (
			'name' => 'client',
			'vname' => 'LBL_CLIENT',
			'type' => 'varchar',
			'len' => 255,
			'required' => true,
			'reportable' => true,
		),
		'percent_complete' => array (
			'name' => 'percent_complete',
			'vname' => 'LBL_PERCENT',
			'type' => 'int',
			'required' => false,
	    	),
	    'schedulers' => array (
			'name'            => 'schedulers',
			'vname'            => 'LBL_SCHEDULER_ID',
			'type'            => 'link',
			'relationship'    => 'schedulers_jobs_rel',
			'source'        => 'non-db',
	        'link_type' => 'one',
	   ),
		),
	'indices' => array (
		array(
			'name' =>'job_queuepk',
			'type' =>'primary',
			'fields' => array(
				'id'
			)
		),
		array(
    		'name' =>'idx_status_scheduler',
    		'type'=>'index',
    		'fields' => array(
                'status',
	        	'scheduler_id',
   			)
   		),
		array(
    		'name' =>'idx_status_time',
    		'type'=>'index',
    		'fields' => array(
                'status',
				'execute_time',
				'date_entered',
   			)
   		),
		array(
    		'name' =>'idx_status_entered',
    		'type'=>'index',
    		'fields' => array(
                'status',
	        	'date_entered',
   			)
   		),
		array(
    		'name' =>'idx_status_modified',
    		'type'=>'index',
    		'fields' => array(
                'status',
	        	'date_modified',
   			)
   		),
   	),
);

VardefManager::createVardef('SchedulersJobs','SchedulersJob', array('assignable'));
