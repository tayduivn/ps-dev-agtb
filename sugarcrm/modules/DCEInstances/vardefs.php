<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['DCEInstance'] = array(
	'table'=>'dceinstances',
	'audited'=>true,
	'fields'=>array (
  'account_id' => 
  array (
    'required' => false,
    'name' => 'account_id',
    'vname' => '',
    'type' => 'id',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 0,
  ),
  'account_name' => 
  array (
    'required' => '1',
    'source' => 'non-db',
    'name' => 'account_name',
    'rname' => 'name',
    'vname' => 'LBL_ACCOUNT',
    'link'=>'accounts',
    'type' => 'relate',
    'massupdate' => 0,
    'comments' => 'Account',
    'help' => 'Account',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 0,
    'reportable' => 1,
    'len' => '255',
    'id_name' => 'account_id',
    'ext2' => 'Accounts',
    'module' => 'Accounts',
    'studio' => 'visible',
  ),
  'accounts' =>
    array (
    'name' => 'accounts',
    'type' => 'link',
    'relationship' => 'Accounts_DCEInstances',
    'link_type' => 'one',
    'source' => 'non-db',
    'vname' => 'LBL_ACCOUNT',
    'duplicate_merge'=> 'disabled',
  ),
  'license_key' => 
  array (
    'required' => '1',
    'name' => 'license_key',
    'vname' => 'LBL_LICENSE_KEY',
    'type' => 'varchar',
    'massupdate' => 0,
    'comments' => 'License Key',
    'help' => 'License Key',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '255',
  ),
    'type' => 
    array (
        'required' => true,
        'name' => 'type',
        'vname' => 'LBL_TYPE',
        'type' => 'enum',
        'options' => 'instance_type_list',
        'default' => 'production',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
        'len' => 100,
    ),
  'licensed_users' => 
  array (
    'required' => true,
    'name' => 'licensed_users',
    'vname' => 'LBL_LICENSED_USERS',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'Licensed Users',
    'help' => 'Licensed Users',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '5',
  ),
  'license_start_date' => 
    array (
      'required' => true,
      'name' => 'license_start_date',
      'vname' => 'LBL_LICENSE_START_DATE',
      'type' => 'date',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 1,
      'reportable' => 1,
    ),
    'license_duration' => 
    array (
        'required' => true,
        'name' => 'license_duration',
        'vname' => 'LBL_LICENSE_DURATION',
        'type' => 'enum',
        'options' => 'production_duration_list',
        'default' => '365',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
        'len' => 100,
    ),
  'license_expire_date' => 
    array (
      'required' => false,
      'name' => 'license_expire_date',
      'vname' => 'LBL_LICENSE_EXPIRE_DATE',
      'type' => 'date',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 1,
      'reportable' => 1,
    ),

    'admin_user' => 
    array (
      'required' => false,
      'name' => 'admin_user',
      'vname' => 'LBL_ADMIN_USER',
      'type' => 'varchar',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => 100,
    ),
    'admin_pass' => 
    array (
      'required' => false,
      'name' => 'admin_pass',
      'vname' => 'LBL_ADMIN_PASS',
      'type' => 'encrypt',
      'dbtype' => 'varchar',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
    ),
    'internal_record' => 
    array (
      'required' => false,
      'name' => 'internal_record',
      'vname' => 'LBL_INTERNAL_RECORD',
      'type' => 'varchar',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => '255',
    ),
    'license_oc' => 
    array (
      'required' => false,
      'name' => 'license_oc',
      'vname' => 'LBL_LICENSE_OC',
      'type' => 'int',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => '11',
    ),
    'sugar_version' => 
    array (
        'name' => 'sugar_version',
        'vname' => 'LBL_SUGAR_VERSION',
        'type' => 'varchar',
        'massupdate' => 0,
        'source' => 'non-db',
    ),
    'sugar_edition' => 
    array (
        'name' => 'sugar_edition',
        'vname' => 'LBL_SUGAR_EDITION',
        'type' => 'varchar',
        'massupdate' => 0,
        'source' => 'non-db',

    ),
    
    'demo_data' => 
    array (
        'required' => false,
        'name' => 'demo_data',
        'vname' => 'LBL_DEMO_DATA',
        'type' => 'bool',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'disable_num_format' => '',
    ),
    'dcetemplate_id' => 
    array (
        'required' => false,
        'name' => 'dcetemplate_id',
        'vname' => 'LBL_TEMPLATE_ID',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'dcetemplate_name' => 
    array (
        'required' => '1',
        'source' => 'non-db',
        'name' => 'dcetemplate_name',
        'vname' => 'LBL_TEMPLATE',
        'rname' => 'name',//for search
        'link' => 'templates',//for search
        'type' => 'relate',
        'massupdate' => 0,
        'join_name'=>'dcetemplates',
        'comments' => 'Template',
        'help' => 'Template',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'dcetemplate_id',
        'ext2' => 'DCETemplates',
        'module' => 'DCETemplates',
        'studio' => 'visible',
        'additionalFields'=>array('sugar_version'=>'sugar_version', 'sugar_edition'=>'sugar_edition'),
        'field_list'=>array('dcetemplate_name','dcetemplate_id','sugar_version','sugar_edition'),
        'populate_list'=>array('name','id','sugar_version','sugar_edition'),
    ),
    'templates' =>
    array (
        'name' => 'templates',
        'type' => 'link',
        'relationship' => 'DCETemplates_DCEInstances',
        'link_type' => 'one',
        'source' => 'non-db',
        'vname' => 'LBL_TEMPLATE',
        'duplicate_merge'=> 'disabled',
    ),
    
    'dcecluster_id' => 
    array (
        'required' => false,
        'name' => 'dcecluster_id',
        'vname' => 'LBL_CLUSTER_ID',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'dcecluster_name' => 
    array (
        'required' => '1',
        'source' => 'non-db',
        'name' => 'dcecluster_name',
        'vname' => 'LBL_CLUSTER',
        'rname' => 'name',//for search
        'link' => 'clusters',//for search
        'type' => 'relate',
        'massupdate' => 0,
        'join_name'=>'dceclusters',
        'comments' => 'Cluster',
        'help' => 'Cluster',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'dcecluster_id',
        'ext2' => 'DCEClusters',
        'module' => 'DCEClusters',
        'studio' => 'visible',
        //'additionalFields'=>array('url'=>'cluster_url'),
        'field_list'=>array('dcecluster_name','dcecluster_id','cluster_url','cluster_url_format'),
        'populate_list'=>array('name','id','url','url_format'),
    ),
    'clusters' =>
    array (
        'name' => 'clusters',
        'type' => 'link',
        'relationship' => 'DCEClusters_DCEInstances',
        'link_type' => 'one',
        'source' => 'non-db',
        'vname' => 'LBL_CLUSTER',
        'duplicate_merge'=> 'disabled',
    ),
    'si_config_file' => 
    array (
        'required' => false,
        'name' => 'si_config_file',
        'vname' => 'LBL_SI_CONFIG_FILE',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
    ),
    'status' => 
    array (
        'required' => false,
        'name' => 'status',
        'vname' => 'LBL_STATUS',
        'type' => 'enum',
        'options' => 'instance_status_list',
        'default' => 'new',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 100,
    ),
    'url' => 
    array (
        'required' => false,
        'name' => 'url',
        'vname' => 'LBL_URL',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
    ),
    'instance_path' => 
    array (
        'required' => false,
        'name' => 'instance_path',
        'vname' => 'LBL_INSTANCE_PATH',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'holds physical server path of this instance',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
    ),
    'support_user' => 
    array (
        'required' => false,
        'name' => 'support_user',
        'vname' => 'LBL_SUPPORT_USER',
        'type' => 'bool',
        'default'=> 0,
        'massupdate' => 0,
        'comments' => 'Determine if the support user is active or not for this instance',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'last_accessed' => 
    array (
        'required' => false,
        'name' => 'last_accessed',
        'vname' => 'LBL_LAST_ACCESSED',
        'type' => 'datetime',
        'massupdate' => 0,
        'comments' => 'Last Accessed',
        'help' => 'Last Accessed',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'db_user' => 
    array (
        'required' => false,
        'name' => 'db_user',
        'vname' => 'LBL_DB_USER',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 100,
    ),
    'db_pass' => 
    array (
        'required' => false,
        'name' => 'db_pass',
        'vname' => 'LBL_DB_PASS',
        'type' => 'encrypt',
        'dbtype' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'parent_dceinstance_id' => 
    array (
        'required' => false,
        'name' => 'parent_dceinstance_id',
        'vname' => 'LBL_PARENT_ID',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'parent_dceinstance_name' => 
    array (
        'required' => false,
        'source' => 'non-db',
        'name' => 'parent_dceinstance_name',
        'vname' => 'LBL_PARENT',
        'rname' => 'name',//for search
        'link' => 'dceinstances',//for search
        'type' => 'relate',
        'massupdate' => 0,
        'comments' => 'parent Instance',
        'help' => 'parent Instance',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'parent_dceinstance_id',
        'ext2' => 'DCEInstances',
        'module' => 'DCEInstances',
        'studio' => 'visible',
    ),
    'get_key_user_id' =>
    array (
        'required' => true,
        'name' => 'get_key_user_id',
        'vname' => 'LBL_GET_KEY_USER_ID',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => 'Store the ID of the User who first requested a license key',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
    'update_key_user_id' =>
    array (
        'required' => false,
        'name' => 'update_key_user_id',
        'vname' => 'LBL_UPDATE_KEY_USER_ID',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => 'Store the ID of the last User who requested a license key update',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
    'license_key_status' =>
    array (
        'required' => true,
        'name' => 'license_key_status',
        'vname' => 'LBL_LICENSE_KEY_STATUS',
        'type' => 'bool',
        'default' => true,
        'massupdate' => 0,
        'comments' => 'determine if the license key is enable or disable',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
   'DCEActions' =>
    array (
        'name' => 'DCEActions',
        'type' => 'link',
        'relationship' => 'DCEInstances_DCEActions',
        'module'=>'DCEActions',
        'bean_name'=>'DCEAction',
        'source'=>'non-db',
        'vname'=>'LBL_DCEACTIONS',
    ),
    'Cases' =>
    array (
        'name' => 'Cases',
        'type' => 'link',
        'relationship' => 'dceinstances_cases',
        'source'=>'non-db',
        'vname'=>'LBL_CASES',
    ),
    'tasks' =>
    array (
        'name' => 'tasks',
        'type' => 'link',
        'relationship' => 'DCEInstances_Tasks',
        'source'=>'non-db',
        'vname'=>'LBL_TASKS',
    ),
    'notes' =>
    array (
        'name' => 'notes',
        'type' => 'link',
        'relationship' => 'DCEInstances_Notes',
        'source'=>'non-db',
        'vname'=>'LBL_NOTES',
    ),
    'emails' =>
    array (
        'name' => 'emails',
        'type' => 'link',
        'relationship' => 'DCEInstances_Emails',
        'source'=>'non-db',
        'vname'=>'LBL_EMAILS',
    ),
    'contacts' =>
    array (
        'name' => 'contacts',
        'type' => 'link',
        'relationship' => 'dceinstances_contacts',
        'source'=>'non-db',
        'vname'=>'LBL_CONTACTS',
    ),
    'users' =>
    array (
        'name' => 'users',
        'type' => 'link',
        'relationship' => 'dceinstances_users',
        'source'=>'non-db',
        'vname'=>'LBL_USERS',
    ),
    'parent_dceinstance' =>
    array (
        'name' => 'parent_dceinstance',
        'type' => 'link',
        'relationship' => 'Parent_dceinstance_DCEInstances',
        'module'=>'DCEInstances',
        'bean_name'=>'DCEInstance',
        'source'=>'non-db',
        'vname'=>'LBL_DCEINSTANCE',
    ),
    'upgrade_searchForm' =>
    array (
        'name' => 'upgrade_searchForm',
        'vname' => '',
        'default' => '1',
        'source'=>'non-db',
        'type' => 'bool',
        'comments'=> 'Use for differenciate the upgrade search form with the listview search form',
    ),
    'from_copy_template' => 
    array (
        'required' => false,
        'name' => 'from_copy_template',
        'vname' => 'LBL_FROM_COPY_TEMPLATE',
        'type' => 'bool',
        'comments'=> 'Used to designate Instance as coming from a copy template',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'disable_num_format' => '',
    ),    
),
	'relationships'=>array (
        'Parent_dceinstance_DCEInstances' => array(
            'lhs_module'=> 'DCEInstances', 
            'lhs_table'=> 'dceinstances', 
            'lhs_key' => 'id',
            'rhs_module'=> 'DCEInstances', 
            'rhs_table'=> 'dceinstances', 
            'rhs_key' => 'parent_dceinstance_id',
            'relationship_type'=>'one-to-many'
        ),
        'DCEInstances_DCEActions' => array(
            'lhs_module'        =>  'DCEInstances',
            'lhs_table'         =>  'dceinstances',
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'DCEActions',
            'rhs_table'         =>  'dceactions',
            'rhs_key'           =>  'instance_id',
            'relationship_type' =>'one-to-many'
        ),
        'Accounts_DCEInstances' => array(
            'lhs_module'        =>  'Accounts',
            'lhs_table'         =>  'accounts',
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'DCEInstances',
            'rhs_table'         =>  'dceinstances',
            'rhs_key'           =>  'account_id',
            'relationship_type' =>'one-to-many'
        ),
        'DCEClusters_DCEInstances' => array(
            'lhs_module'        =>  'DCEClusters',
            'lhs_table'         =>  'dceclusters',
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'DCEInstances',
            'rhs_table'         =>  'dceinstances',
            'rhs_key'           =>  'dcecluster_id',
            'relationship_type' =>'one-to-many'
        ),
        'DCEInstances_Tasks' => array(
            'lhs_module'        =>  'DCEInstances', 
            'lhs_table'         =>  'dceinstances', 
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'Tasks', 
            'rhs_table'         =>  'tasks', 
            'rhs_key'           =>  'parent_id',
            'relationship_type' =>  'one-to-many', 
            'relationship_role_column'      =>'parent_type',
            'relationship_role_column_value'=>'DCEInstances',
        ),
        'DCEInstances_Notes' => array(
            'lhs_module'        =>  'DCEInstances', 
            'lhs_table'         =>  'dceinstances', 
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'Notes', 
            'rhs_table'         =>  'notes', 
            'rhs_key'           =>  'parent_id',
            'relationship_type' =>  'one-to-many', 
            'relationship_role_column'      =>'parent_type',
            'relationship_role_column_value'=>'DCEInstances',
        ),
        'DCEInstances_Emails' => array(
            'lhs_module'        =>  'DCEInstances', 
            'lhs_table'         =>  'dceinstances', 
            'lhs_key'           =>  'id',
            'rhs_module'        =>  'Emails', 
            'rhs_table'         =>  'emails', 
            'rhs_key'           =>  'parent_id',
            'relationship_type' =>  'one-to-many', 
            'relationship_role_column'      =>'parent_type',
            'relationship_role_column_value'=>'DCEInstances',
        ),
    ),
	'optimistic_lock'=>true,
);
$dictionary['DCECronSchedule'] = array(
	'table'=>'dcecronschedules',
	'audited'=>false,
	'fields'=>array (
  'id' =>
	  array (
	    'name' => 'id',
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'required'=>true,
	    'reportable'=>false,
	    'comment' => 'Unique identifier'
	  ),
 'instance_id' => 
  array (
   		'name' => 'instance_id',
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'required'=>true,
	    'reportable'=>false,
  		'comment' => 'The id of the instance this record relates to'
  ),
  'is_locked' => 
  array (
   		'name' => 'is_locked',
	    'vname' => 'LBL_ID',
	    'type' => 'bool',
  		'comment' => 'Whether this row has been locked or not'
  ), 
  'lock_date' => 
  array (
   		'name' => 'lock_date',
	    'vname' => 'LBL_ID',
	    'type' => 'datetime',
  		'comment' => 'Datetime in which the item was locked'
  ),
  'next_execution_time' => 
  array (
   		'name' => 'next_execution_time',
	    'vname' => 'LBL_ID',
	    'type' => 'datetime',
  		'comment' => 'The datetime when this record can be run again'
  ), 
  'running_server' => 
  array (
   		'name' => 'running_server',
	    'vname' => 'LBL_ID',
	    'type' => 'varchar',
		'len' => '50',
  		'comment' => 'The ip address/name of the machine running the cron job currently.'
  ),  
),

);

VardefManager::createVardef('DCEInstances','DCEInstance', array('basic','team_security','assignable'));
VardefManager::createVardef('DCEInstances','DCECronSchedule', array());