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
$dictionary['DistGroup'] = array(
	'table' => 'distgroups',
	'optimistic_locking' => true,
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_NAME',
			'type' => 'id',
			'required'=>true,
			'reportable'=>false,
		),
		'name' =>  array (
			'name' => 'name',
			'vname' => 'LBL_LIST_NAME',
			'type' => 'name',
			'dbType' => 'varchar',
			'len' => '255',
			'required' => true,
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
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'reportable'=>false,
		),
		'created_by_link' => array (
			'name' => 'created_by_link',
			'type' => 'link',
			'relationship' => 'distgroups_created_by',
			'vname' => 'LBL_CREATED_BY_USER',
			'link_type' => 'one',
			'module'=>'Users',
			'bean_name'=>'User',
			'source'=>'non-db',
			'id_name' => 'created_by',
			'duplicate_merge'=> 'disabled',
		),
		'modified_user_link' => array (
			'name' => 'modified_user_link',
			'type' => 'link',
			'relationship' => 'distgroups_modified_user',
			'vname' => 'LBL_MODIFIED_BY_USER',
			'link_type' => 'one',
			'source'=>'non-db',
			'module'=>'Users',
			'bean_name'=>'User',
			'id_name' => 'modified_user_id',
			'table' => 'users',
		),
		'assigned_user_link' => array (
			'name' => 'assigned_user_link',
			'type' => 'link',
			'relationship' => 'distgroups_assigned_user',
			'vname' => 'LBL_ASSIGNED_TO_USER',
			'link_type' => 'one',
			'module'=>'Users',
			'bean_name'=>'User',
			'source'=>'non-db',
			'rname' => 'user_name',
			'id_name' => 'assigned_user_id',
			'table' => 'users',
		),
        'subscriptions' =>
        array (
            'name' => 'subscriptions',
            'type' => 'link',
            'relationship' => 'subscriptions_distgroups',
            'vname' => 'LBL_SUBSCRIPTIONS_DISTGROUPS_LINK',
            'source' => 'non-db',
        ),
		'quantity_fields' => array (
			'name' => 'quantity_fields',
			'rname' => 'id',
			//                           db_col         rel_name          db_col        rel_name
			'relationship_fields' => array('id' => 'quantity_fields_id', 'quantity' => 'quantity'),
			'vname' => 'LBL_LIST_QUANTITY',
			'type' => 'relate',
			'link' => 'subscriptions',
			'link_type' => 'relationship_info',
			'join_name' => 'distgroups',
			'join_link_name' => 'subscriptions_distgroups',
			'source' => 'non-db',
			'Importable' => false,
			'duplicate_merge'=> 'disabled',
		),
		'quantity_fields_id' => array(
			'name' => 'quantity_fields_id',
			'type' => 'varchar',
			'source' => 'non-db',
			'vname' => 'LBL_QUANTITY',
		),
		'quantity' => array(
			'name' => 'quantity',
			'type' => 'int',
			'len'  => '20',
			'source' => 'non-db',
			'vname' => 'LBL_QUANTITY',
		),
		/*
        'sugarproducts' =>
        array (
            'name' => 'sugarproducts',
            'type' => 'link',
            'relationship' => 'distgroups_sugarproducts',
            'vname' => 'LBL_DISTGROUPS_SUGARPRODUCTS_LINK',
            'source' => 'non-db',
        ),
		*/
	),
	'relationships' => array(
		/*
		'subscriptions_portalusers' => array(
			'lhs_module' => 'DistGroups', 'lhs_table' => 'subscriptions', 'lhs_key' => 'id',
			'rhs_module' => 'PortalUsers', 'rhs_table' => 'subscriptions_portalusers', 'rhs_key' => 'subscription_id',
			'relationship_type' => 'one-to-many',
    		'join_table'=> 'subscriptions_portalusers',
    		'join_key_lhs'=>'id',
    		'join_key_rhs'=>'subscription_id',
		),
		*/
		'distgroups_assigned_user' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'DistGroups', 'rhs_table'=> 'distgroups', 'rhs_key' => 'assigned_user_id',
			'relationship_type'=>'one-to-many'
		),
		'distgroups_modified_user' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'DistGroups', 'rhs_table'=> 'distgroups', 'rhs_key' => 'modified_user_id',
			'relationship_type'=>'one-to-many'
		),
		'distgroups_created_by' => array(
			'lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
			'rhs_module'=> 'DistGroups', 'rhs_table'=> 'distgroups', 'rhs_key' => 'created_by',
			'relationship_type'=>'one-to-many'
		),
	),
);
VardefManager::createVardef('DistGroups','DistGroup', array(
'team_security',
));
?>
