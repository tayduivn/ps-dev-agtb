<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['ITRequest'] = array('table' => 'itrequests','audited'=>true, 'unified_search' => false,'duplicate_merge'=>false,
    'comment' => 'ITRequests are issues or problems that an internal user asks IT personel to resolve'
,'fields' => array (
        'id' =>
        array (
            'name' => 'id',
            'vname' => 'LBL_NAME',
            'type' => 'id',
            'required'=>true,
            'reportable'=>false,
            'comment' => 'Unique identifier'
        ),
        'itrequest_number' =>
        array (
            'name' => 'itrequest_number',
            'vname' => 'LBL_NUMBER',
            'type' => 'int',
            'required'=>true,
            'len' => '11',
            'isnull' => 'false',
            'auto_increment'=>true,
            'comment' => 'Visible itrequest identifier',
 	    'disable_num_format' => true,
        ),
        'date_resolved' =>
        array (
            'name' => 'date_resolved',
            'vname' => 'LBL_DATE_RESOLVED',
            'type' => 'datetime',
            'massupdate' => false,
            'required'=> false,
            'comment' => 'Date record was resolved',
        ),
        'date_entered' =>
        array (
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required'=>true,
            'comment' => 'Date record created'
        ),
        'date_modified' =>
        array (
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required'=>true,
            'comment' => 'Date record last modified'
        ),
        'modified_user_id' =>
        array (
            'name' => 'modified_user_id',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_MODIFIED',
            'type' => 'assigned_user_name',
            'table' => 'modified_user_id_users',
            'isnull' => 'false',
            'reportable'=>true,
            'dbType' => 'id',
            'required'=>true,
            'comment' => 'User who last modified record'
        ),
        'assigned_user_id' =>
        array (
            'name' => 'assigned_user_id',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'reportable'=>true,
            'dbType' => 'id',
            'audited'=>true,
            'comment' => 'User ID assigned to record',
            'duplicate_merge'=>'disabled'
        ),
        'assigned_user_name' => array (
			'name' => 'assigned_user_name',
			'rname' => 'name',
			'id_name' => 'assigned_user_id',
			'vname' => 'LBL_ASSIGNED_TO',
			'type' => 'relate',
			'table' => 'users',
			'isnull' => 'false',
			'module' => 'Users',
			'dbType' => 'varchar',
			'len' => '255',
			'source' => 'non-db',
			'link' => 'assigned_user_link',
		),

        'created_by' =>
        array (
            'name' => 'created_by',
            'rname' => 'user_name',
            'id_name' => 'created_by',
            'vname' => 'LBL_CREATED',
            'type' => 'assigned_user_name',
            'table' => 'created_by_users',
            'isnull' => 'false',
            'dbType' => 'id',
            'comment' => 'User ID who created the record'
        ),
        'created_user_name' =>
        array (
            'name' => 'created_user_name',
            'vname' => 'LBL_CREATED_USER_NAME',
            'type' => 'relate',
            'reportable'=>false,
            'source'=>'nondb',
            'table' => 'users',
			'isnull' => 'false',
			'module' => 'Users',
			'dbType' => 'varchar',
			'len' => '255',
            'link' => 'created_by_link',
            'id_name' => 'created_by',
            'rname' => 'user_name'
        ),
        'modified_by_name' =>
        array (
            'name' => 'modified_by_name',
            'vname' => 'LBL_MODIFIED_USER_NAME',
            'type' => 'string',
            'reportable'=>false,
            'source'=>'nondb',
			'isnull' => 'true',
			'module' => 'Users',
			'dbType' => 'varchar',
        ),
        'deleted' =>
        array (
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => true,
            'reportable'=>false,
            'comment' => 'Record deletion indicator'
        ),
        'name' =>
        array (
            'name' => 'name',
            'vname' => 'LBL_LIST_SUBJECT',
            'type' => 'name',
            'dbType' => 'varchar',
            'len' => '255',
            'comment' => 'The subject of the itrequest',
        ),

        'category' =>
        array (
            'name' => 'category',
            'vname' => 'LBL_CATEGORY',
            'type' => 'enum',
            'options' => 'itrequest_category_dom',
            'len'=>75,
            'audited'=> false,
            'comment' => 'The category of the itrequest',
        ),

        'subcategory' =>
        array (
            'name' => 'subcategory',
            'vname' => 'LBL_SUBCATEGORY',
            'type' => 'enum',
            'options' => 'itrequest_subcategory_dom',
            'len'=>75,
            'audited'=> false,
            'comment' => 'The subcategory of the itrequest',
        ),

        'status' =>
        array (
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'itrequest_status_dom',
            'len'=>25,
            'audited'=>true,
            'comment' => 'The status of the itrequest',
        ),

        'priority' =>
        array (
            'name' => 'priority',
            'vname' => 'LBL_PRIORITY',
            'type' => 'enum',
            'options' => 'itrequest_priority_dom',
            'len'=>25,
            'audited'=>true,
            'comment' => 'The priority of the itrequest',

        ),
        'target_date' =>
        array (
            'name' => 'target_date',
            'vname' => 'LBL_TARGET_DATE',
            'type' => 'date',
            'audited' => false,
            'comment' => 'This is the targeted completion date for the request',
        ),
        'start_date' =>
        array (
            'name' => 'start_date',
            'vname' => 'LBL_START_DATE',
            'type' => 'date',
            'audited' => false,
            'source' => 'non-db',
            'comment' => 'This is the targeted start date for the request',
        ),
        'development_time' =>
        array (
            'name' => 'development_time',
            'vname' => 'LBL_DEVELOPMENT_TIME',
            'type' => 'decimal',
            'len' => '18,2',
            'default' => '0.00',
            'audited' => false,
            'comment' => 'This is the number of hours required to complete the request.',
        ),

        'description' =>
        array (
            'name' => 'description',
            'vname' => 'LBL_DESCRIPTION',
            'type' => 'text',
            'comment' => 'The itrequest description'
        ),
        'resolution' =>
        array (
            'name' => 'resolution',
            'vname' => 'LBL_RESOLUTION',
            'type' => 'text',
            'comment' => 'The resolution of the IT Request'
        ),

        'system_id' =>
        array (
            'name' => 'system_id',
            'vname' => 'LBL_SYSTEM_ID',
            'type' => 'int',
            'comment' => 'The offline client device that created the itrequest'
        ),

        'tasks' =>
        array (
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'itrequests_tasks',
            'source'=>'non-db',
            'vname'=>'LBL_TASKS',
        ),
        'notes' =>
        array (
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'itrequests_notes',
            'source'=>'non-db',
            'vname'=>'LBL_NOTES',
        ),
        'bugs' =>
        array (
            'name' => 'bugs',
            'type' => 'link',
            'relationship' => 'itrequests_bugs',
            'source'=>'non-db',
            'vname'=>'LBL_BUGS',
        ),
        'cases' =>
        array (
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'itrequests_cases',
            'source'=>'non-db',
            'vname'=>'LBL_CASES',
        ),
        'accounts' =>
        array (
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'itrequests_accounts',
            'source'=>'non-db',
            'vname'=>'LBL_ACCOUNTS',
        ),
        'meetings' =>
        array (
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'itrequests_meetings',
            'bean_name'=>'Meeting',
            'source'=>'non-db',
            'vname'=>'LBL_MEETINGS',
        ),
        'users' =>
        array (
            'name' => 'users',
            'type' => 'link',
            'relationship' => 'itrequests_users',
            'source'=>'non-db',
            'vname'=>'LBL_USERS',
        ),

        'created_by_link' =>
        array (
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'itrequests_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module'=>'Users',
            'bean_name'=>'User',
            'source'=>'non-db',
            'rname' => 'user_name',
            'id_name' => 'created_by',
            'table' => 'users'
        ),
        'modified_user_link' =>
        array (
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'itrequests_modified_user',
            'vname' => 'LBL_MODIFIED_BY_USER',
            'link_type' => 'one',
            'module'=>'Users',
            'bean_name'=>'User',
            'source'=>'non-db',
        ),
        'assigned_user_link' =>
        array (
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'itrequests_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module'=>'Users',
            'bean_name'=>'User',
            'source'=>'non-db',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'table' => 'users',
        ),

        'related_itrequests' => array(
            'name' => 'related_itrequests',
            'type' => 'link',
            'relationship' => 'itrequests_itrequests',
            'source'=>'non-db',
            'vname'=>'LBL_RELATED_ITREQUESTS',
        ),

    ), 'indices' => array (
        array('name' =>'itrequestspk', 'type' =>'primary', 'fields'=>array('id')),

        /*

       array('name' =>'itrequest_number' , 'type'=>'index' , 'fields'=>array('itrequest_number')),

        */
        array('name' =>'itrequest_number' , 'type'=>'unique' , 'fields'=>array('itrequest_number', 'system_id')),


        array('name' =>'idx_itrequest_name', 'type' =>'index', 'fields'=>array('name')),
    )

, 'relationships' => array (
        'itrequests_tasks' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
            'rhs_module'=> 'Tasks', 'rhs_table'=> 'tasks', 'rhs_key' => 'parent_id',
            'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'ITRequests')

    ,'itrequests_notes' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
            'rhs_module'=> 'Notes', 'rhs_table'=> 'notes', 'rhs_key' => 'parent_id',
            'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'ITRequests')

    ,'itrequests_meetings' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
            'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'parent_id',
            'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'ITRequests')
        /*
            ,'itrequest_users' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
                                      'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
                                      'relationship_type'=>'one-to-many',)
        */
    ,'itrequests_assigned_user' =>
        array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
            'rhs_module'=> 'ITRequests', 'rhs_table'=> 'itrequests', 'rhs_key' => 'assigned_user_id',
            'relationship_type'=>'one-to-many')

    ,'itrequests_modified_user' =>
        array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
            'rhs_module'=> 'ITRequests', 'rhs_table'=> 'itrequests', 'rhs_key' => 'modified_user_id',
            'relationship_type'=>'one-to-many')

    ,'itrequests_created_by' =>
        array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
            'rhs_module'=> 'ITRequests', 'rhs_table'=> 'itrequests', 'rhs_key' => 'created_by',
            'relationship_type'=>'one-to-many')


,'itrequests_team' =>
array('lhs_module' => 'Teams', 'lhs_table' => 'teams', 'lhs_key' => 'id',
'rhs_module' => 'ITRequests', 'rhs_table' => 'itrequests', 'rhs_key' => 'team_id',
'relationship_type' => 'one-to-many'),



    )
    //This enables optimistic locking for Saves From EditView
,'optimistic_locking'=>true,
);
VardefManager::createVardef('ITRequests','ITRequest', array(
    'team_security',
));
?>
