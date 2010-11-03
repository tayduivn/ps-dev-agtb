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
$dictionary['Subscription'] = array(
	'table' => 'subscriptions',
	'optimistic_locking' => true,
	'audited'=>true,
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_NAME',
			'type' => 'id',
			'required'=>true,
			'reportable'=>false,
		),
        'name' => array(
            'name' => 'name',
            'len' => '255',
            'type' => 'string',
            'source'=>'non-db',
        ),
		'subscription_id' =>  array (
			'name' => 'subscription_id',
			'vname' => 'LBL_SUBSCRIPTION_ID',
			'type' => 'id',
			'required'=>true,
			'reportable'=>true,
		),
		'expiration_date' =>  array (
			'name' => 'expiration_date',
			'vname' => 'LBL_EXPIRATION_DATE',
			'type' => 'date',
			'required'=>true,
			'audited'=>true,
		),
		'audited' =>  array (
			'name' => 'audited',
			'vname' => 'LBL_AUDITED',
			'type' => 'bool',
			'audited'=>true,
		),
		'enforce_user_limit' =>  array (
			'name' => 'enforce_user_limit',
			'vname' => 'LBL_ENFORCE_USER_LIMIT',
			'type' => 'bool',
			'audited'=>true,
		),
		// BEGIN jostrow MoofCart customization
		// See ITRequest #9329

		'ignore_expiration_date' => array(
			'name' => 'ignore_expiration_date',
			'vname' => 'LBL_IGNORE_EXPIRATION_DATE',
			'type' => 'bool',
			'audited' => true,
		),

		// END jostrow MoofCart customization
		'portal_users' => array(
			'name' => 'portal_users',
			'vname' => 'LBL_PORTAL_USERS',
			'type' => 'int',
			'audited' => true,
            'default' => 0,
		),
		'enforce_portal_users' => array(
			'name' => 'enforce_portal_users',
			'vname' => 'LBL_ENFORCE_PORTAL_USERS',
			'type' => 'bool',
			'audited' => true,
		),
		'debug' =>  array (
			'name' => 'debug',
			'vname' => 'LBL_DEBUG',
			'type' => 'bool',
			'default' => '0',
		),
		'account_id' => array (
			'name' => 'account_id',
			'type' => 'id',
			'dbType' => 'id',
			'vname' => 'LBL_ACCOUNT_ID',
			'reportable'=> false,
			'required'=>true,
			'audited'=>true,
			'duplicate_merge' => 'disabled',
		),
        'account_name' =>
          array (
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'table' => 'accounts',
            'join_name'=>'accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'dbType' => 'varchar',
            'link'=>'accounts',
            'len' => '255',
            'source'=>'non-db',
            'unified_search' => true,
            'required' => true,
            'importable' => 'required',
          ),
		'status' =>  array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'options' => 'subscription_status_dom',
			'required'=>true,
			'len' => '64',
			'audited' => true,
		),
		'perpetual' =>  array (
			'name' => 'perpetual',
			'vname' => 'LBL_PERPETUAL',
			'type' => 'bool',
			'audited'=>true,
		),
		'date_entered' =>  array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required'=>true,
		),
		'date_modified' =>  array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required'=>true,
		),
		'modified_user_id' => array (
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED',
			'type' => 'assigned_user_name',
			'dbType' => 'id',
			'table' => 'users',
			'isnull' => 'false',
			'reportable'=>true,
			'comment' => 'User who last modified record'
		),
		'modified_user_name' => array (
			'name' => 'modified_user_name',
			'rname' => 'name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED_BY',
			'type' => 'relate',
			'table' => 'users',
			'isnull' => 'false',
			'module' => 'Users',
			'dbType' => 'varchar',
			'len' => '255',
			'source' => 'non-db',
			'link' => 'modified_user_link',
		),
		'assigned_user_id' => array (
			'name' => 'assigned_user_id',
			'rname' => 'user_name',
			'id_name' => 'assigned_user_id',
			'vname' => 'LBL_ASSIGNED_TO',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'reportable'=>true,
			'isnull' => 'false',
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
		'created_by' => array (
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_CREATED',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'dbType' => 'id',
			'comment' => 'User ID who created record'
		),
		'created_user_name' => array (
			'name' => 'created_user_name',
			'rname' => 'name',
			'id_name' => 'created_user_id',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'relate',
			'table' => 'users',
			'isnull' => 'false',
			'module' => 'Users',
			'dbType' => 'varchar',
			'len' => '255',
			'source' => 'non-db',
			'link' => 'created_user_link',
		),
		'distgroups' =>
		array (
			'name' => 'distgroups',
			'type' => 'link',
			'relationship' => 'subscriptions_distgroups',
			'vname' => 'LBL_SUBSCRIPTIONS_DISTGROUPS_LINK',
			'source' => 'non-db',
		),
        'name' => array(
            'name' => 'name',
            'type' => 'string',
            'source' => 'non-db',
            'len' => '255'
        ),
		//DEE
		'notes' =>
  		array (
    			'name' => 'notes',
    			'type' => 'link',
    			'relationship' => 'subscriptions_notes',
    			'source'=>'non-db',
    			'vname'=>'LBL_NOTES',
  		),
		'tasks' =>
  		array (
        		'name' => 'tasks',
    			'type' => 'link',
    			'relationship' => 'subscriptions_tasks',
    			'source'=>'non-db',
        		'vname'=>'LBL_TASKS',
  		),
		'meetings' =>
  		array (
        		'name' => 'meetings',
    			'type' => 'link',
    			'relationship' => 'subscriptions_meetings',
    			'bean_name'=>'Meeting',
    			'source'=>'non-db',
        		'vname'=>'LBL_MEETINGS',
  		),
		'users' =>
 		 array (
        		'name' => 'users',
    			'type' => 'link',
    			'relationship' => 'subscriptions_users',
    			'source'=>'non-db',
        		'vname'=>'LBL_USERS',
  		),
		//END DEE

        // jwhitcraft
        'accounts'  =>
        array (
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'subscriptions_accounts',
            'source' => 'non-db',
            'vname' => 'LBL_ACCOUNT'
        )
        // end jwhitcraft
	),
	'relationships' => array(
		// ITR: 14106 - jwhitcraft - removed as these modules have been removed
        /*'subscriptions_orders' => array(
			'lhs_module' => 'Subscriptions', 'lhs_table' => 'subscriptions', 'lhs_key' => 'id',
			'rhs_module' => 'Orders', 'rhs_table' => 'subscriptions_orders', 'rhs_key' => 'subscription_id',
			'relationship_type' => 'one-to-many',
    		'join_table'=> 'subscriptions_orders',
    		'join_key_lhs'=>'id',
    		'join_key_rhs'=>'subscription_id',
		),
		'subscriptions_portalusers' => array(
			'lhs_module' => 'Subscriptions', 'lhs_table' => 'subscriptions', 'lhs_key' => 'id',
			'rhs_module' => 'PortalUsers', 'rhs_table' => 'subscriptions_portalusers', 'rhs_key' => 'subscription_id',
			'relationship_type' => 'one-to-many',
    		'join_table'=> 'subscriptions_portalusers',
    		'join_key_lhs'=>'id',
    		'join_key_rhs'=>'subscription_id',
		),*/
        // end ITR: 14106
		'subscriptions_accounts' => array(
			'lhs_module' => 'Accounts', 'lhs_table' => 'accounts', 'lhs_key' => 'id',
			'rhs_module' => 'Subscriptions', 'rhs_table' => 'subscriptions', 'rhs_key' => 'account_id',
			'relationship_type' => 'one-to-many'
		),
		'subscriptions_assigned_user' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'Subscriptions', 'rhs_table'=> 'subscriptions', 'rhs_key' => 'assigned_user_id',
			'relationship_type'=>'one-to-many'
		),
		'subscriptions_modified_user' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'Subscriptions', 'rhs_table'=> 'subscriptions', 'rhs_key' => 'modified_user_id',
			'relationship_type'=>'one-to-many'
		),
		'subscriptions_created_by' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'Subscriptions', 'rhs_table'=> 'subscriptions', 'rhs_key' => 'created_by',
			'relationship_type'=>'one-to-many'
		),
		//DEE
		'subscriptions_tasks' => array('lhs_module'=> 'Subscriptions', 'lhs_table'=> 'subscriptions', 'lhs_key' => 'id',
                                                          'rhs_module'=> 'Tasks', 'rhs_table'=> 'tasks', 'rhs_key' => 'parent_id',
                                                          'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
                                                          'relationship_role_column_value'=>'Subscriptions'
		),
        	'subscriptions_notes' => array('lhs_module'=> 'Subscriptions', 'lhs_table'=> 'subscriptions', 'lhs_key' => 'id',
                                                          'rhs_module'=> 'Notes', 'rhs_table'=> 'notes', 'rhs_key' => 'parent_id',
                                                          'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
                                                          'relationship_role_column_value'=>'Subscriptions'
		),
        	'subscriptions_meetings' => array('lhs_module'=> 'Subscriptions', 'lhs_table'=> 'subscriptions', 'lhs_key' => 'id',
                                                          'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'parent_id',
                                                          'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
                                                          'relationship_role_column_value'=>'Subscriptions'
		),
		//END DEE
	),
);
VardefManager::createVardef('Subscriptions','Subscription', array(
'team_security',
));
?>
