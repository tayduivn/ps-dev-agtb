<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Table definition file for the project table
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: vardefs.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $

$dictionary['Project'] = array(
	'table' => 'project',
	'unified_search' => true,
	'comment' => 'Project',
	'fields' => array(
		'id' => array(
			'name' => 'id',
			'vname' => 'LBL_ID',
			'required' => true,
			'type' => 'id',
			'reportable'=>false,
			'comment' => 'Unique identifier'
		),
		'date_entered' => array(
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record created'
		),
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record last modified'
		),
		'assigned_user_id' => array(
			'name' => 'assigned_user_id',
			'rname' => 'user_name',
			'id_name' => 'assigned_user_id',
			'type' => 'assigned_user_name',
			'vname' => 'LBL_ASSIGNED_USER_ID',
			'required' => false,
			'len' => 36,
			'dbType' => 'id',
			'table' => 'users',
			'isnull' => false,
			'reportable'=>true,
			'comment' => 'User assigned to this record'
		),
		'modified_user_id' => array(
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED_USER_ID',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'dbType' => 'id',
			'reportable'=>true,
			'comment' => 'User who last modified record'
		),
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'dbType' => 'id',
			'comment' => 'User who created record'
		),
		//BEGIN SUGARCRM flav=pro ONLY 
  		'team_id' => 
  		array (
    		'name' => 'team_id',
    		'vname' => 'LBL_TEAM_ID',
    		'reportable'=>false,
    		'dbtype' => 'id',
    		'type' => 'team_list',
    		'comment' => 'Team assigned to record'
		  ),
        'team_name' => 
          array (
            'name' => 'team_name',
            'rname' => 'name',
            'id_name' => 'team_id',
            'vname' => 'LBL_TEAM',
            'type' => 'relate',
            'table' => 'teams',
            'isnull' => 'true',
            'module' => 'Teams',
            'link'=>'team_link',
            'massupdate' => false,
            'source'=>'non-db',
            'dbType' => 'varchar',
            'len' => 36,
          ),        
		 //END SUGARCRM flav=pro ONLY 
		'name' => array(
			'name' => 'name',
			'vname' => 'LBL_NAME',
			'required' => true,
			'dbType' => 'varchar',
			'type' => 'name',
			'len' => 50,
			'unified_search' => true,
			'comment' => 'Project name'
		),
		'description' => array(
			'name' => 'description',
			'vname' => 'LBL_DESCRIPTION',
			'required' => false,
			'type' => 'text',
			'comment' => 'Project description'
		),
		'deleted' => array(
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
            'reportable'=>false,
			'default' => '0',
			'comment' => 'Record deletion indicator'
		),	
        'estimated_start_date' =>
        array(
            'name' => 'estimated_start_date',
            'vname' => 'LBL_DATE_START',
            'required' => true,
            'validation' => array('type' => 'isbefore', 'compareto' => 'estimated_end_date', 'blank' => true),
            'type' => 'date',
        ),
        'estimated_end_date' =>
        array(
            'name' => 'estimated_end_date',
            'vname' => 'LBL_DATE_END',
            'required' => true,
            'type' => 'date',
        ),
        'status' =>
        array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'project_status_dom',
        ),

        'priority' =>
        array(
            'name' => 'priority',
            'vname' => 'LBL_PRIORITY',
            'type' => 'enum',
            'options' => 'projects_priority_options',
        ),               
        'is_template' => array(
            'name' => 'is_template',
            'vname' => 'LBL_IS_TEMPLATE',
            'type' => 'bool',
            'required' => false,
            'default' => '0',
            'comment' => 'Should be checked if the project is a template',
            'massupdate' => false,
        ),         
		'total_estimated_effort' => 
  		array (
  			'name' => 'total_estimated_effort',
			'type' => 'int',
    		'source'=>'non-db',
			'vname'=>'LBL_LIST_TOTAL_ESTIMATED_EFFORT',
  		),		
		'total_actual_effort' => 
  		array (
  			'name' => 'total_actual_effort',
			'type' => 'int',
    		'source'=>'non-db',
			'vname'=>'LBL_LIST_TOTAL_ACTUAL_EFFORT',
  		),		
		
		'accounts' => 
  		array (
  			'name' => 'accounts',
    		'type' => 'link',
    		'relationship' => 'projects_accounts',
    		'source'=>'non-db',
    		'ignore_role'=>true,
				'vname'=>'LBL_ACCOUNTS',
  		),		
		'quotes' => 
  		array (
  			'name' => 'quotes',
    		'type' => 'link',
    		'relationship' => 'projects_quotes',
    		'source'=>'non-db',
    		'ignore_role'=>true,
				'vname'=>'LBL_QUOTES',
  		),		
		'contacts' => 
  		array (
  			'name' => 'contacts',
    		'type' => 'link',
    		'relationship' => 'projects_contacts',
    		'source'=>'non-db',
    		'ignore_role'=>true,
				'vname'=>'LBL_CONTACTS',
  		),		
		'opportunities' => 
  		array (
  			'name' => 'opportunities',
    		'type' => 'link',
    		'relationship' => 'projects_opportunities',
    		'source'=>'non-db',
    		'ignore_role'=>true,
				'vname'=>'LBL_OPPORTUNITIES',
  		),		
  		'notes' => 
  		array (
  			'name' => 'notes',
    		'type' => 'link',
    		'relationship' => 'projects_notes',
    		'source'=>'non-db',
				'vname'=>'LBL_NOTES',
  		),
		'tasks' => 
  			array (
  			'name' => 'tasks',
    		'type' => 'link',
    		'relationship' => 'projects_tasks',
    		'source'=>'non-db',
				'vname'=>'LBL_TASKS',
  		),
  		'meetings' => 
  			array (
  			'name' => 'meetings',
    		'type' => 'link',
    		'relationship' => 'projects_meetings',
    		'source'=>'non-db',
				'vname'=>'LBL_MEETINGS',
  		),
		'calls' => 
  			array (
  			'name' => 'calls',
    		'type' => 'link',
    		'relationship' => 'projects_calls',
    		'source'=>'non-db',
				'vname'=>'LBL_CALLS',
  		),
  		'emails' => 
  			array (
  			'name' => 'emails',
    		'type' => 'link',
    		'relationship' => 'emails_projects_rel',
    		'source'=>'non-db',
				'vname'=>'LBL_EMAILS',
  		),
  		'projecttask' => 
  			array (
  			'name' => 'projecttask',
    		'type' => 'link',
    		'relationship' => 'projects_project_tasks',
    		'source'=>'non-db',
				'vname'=>'LBL_PROJECT_TASKS',
  		),
        //BEGIN SUGARCRM flav=pro ONLY 
          'team_link' =>
          array (
                'name' => 'team_link',
            'type' => 'link',
            'relationship' => 'projects_team',
            'vname' => 'LBL_TEAMS_LINK',
            'link_type' => 'one',
            'module'=>'Teams',
            'bean_name'=>'Team',
            'source'=>'non-db',
          ),
          
        //END SUGARCRM flav=pro ONLY 
          'created_by_link' =>
          array (
                'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'projects_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module'=>'Users',
            'bean_name'=>'User',
            'source'=>'non-db',
          ),
          'modified_user_link' =>
          array (
                'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'projects_modified_user',
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
            'relationship' => 'projects_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module'=>'Users',
            'bean_name'=>'User',
            'source'=>'non-db',
          ),
        'assigned_user_name' => 
        array (
        	'name' => 'assigned_user_name',
        	'rname' => 'user_name',
        	'id_name' => 'assigned_user_id',
        	'vname' => 'LBL_ASSIGNED_USER_NAME',
        	'type' => 'relate',
        	'table' => 'users',
        	'module' => 'Users',
        	'dbType' => 'varchar',
        	'link'=>'users',
        	'len' => '255',
        	'source'=>'non-db',
        	), 
        'cases' => 
            array (
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'projects_cases',
            'side' => 'right',
            'source'=>'non-db',
            'vname'=>'LBL_CASES',
        ),      
        'bugs' => 
            array (
            'name' => 'bugs',
            'type' => 'link',
            'relationship' => 'projects_bugs',
            'side' => 'right',
            'source'=>'non-db',
            'vname'=>'LBL_BUGS',
        ),
        'products' => 
            array (
            'name' => 'products',
            'type' => 'link',
            'relationship' => 'projects_products',
            'side' => 'right',
            'source'=>'non-db',
            'vname'=>'LBL_PRODUCTS',
        ),          
		'user_resources' => 
  			array (
  			'name' => 'user_resources',
    		'type' => 'link',
    		'relationship' => 'projects_users_resources',
            'side' => 'right',
    		'source'=>'non-db',
			'vname'=>'LBL_USER_RESOURCE',
  		),

		'contact_resources' => 
  			array (
  			'name' => 'contact_resources',
    		'type' => 'link',
    		'relationship' => 'projects_contacts_resources',
            'side' => 'right',
    		'source'=>'non-db',
			'vname'=>'LBL_CONTACTS_RESOURCE',
  		),
		'project_holidays' => 
  			array (
  			'name' => 'project_holidays',
    		'type' => 'link',
    		'relationship' => 'projects_holidays',
    		'source'=>'non-db',
			'vname'=>'LBL_PROJECT_HOLIDAYS',
			'module'=>'Holidays',
			'bean_name'=>'Holiday',
  		),    		     		
	),
	'indices' => array(
		array('name' =>'projects_primary_key_index',
			'type' =>'primary',
			'fields'=>array('id')
		),
	),
	'relationships' => array(
		'projects_notes' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'Notes', 'rhs_table'=> 'notes', 'rhs_key' => 'parent_id',	
			'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
			'relationship_role_column_value'=>'Project'),
		'projects_tasks' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'Tasks', 'rhs_table'=> 'tasks', 'rhs_key' => 'parent_id',	
			'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
			'relationship_role_column_value'=>'Project'),
		'projects_meetings' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'parent_id',	
			'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
			'relationship_role_column_value'=>'Project'),
		'projects_calls' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'Calls', 'rhs_table'=> 'calls', 'rhs_key' => 'parent_id',	
			'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
			'relationship_role_column_value'=>'Project'),
		'projects_emails' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'Emails', 'rhs_table'=> 'emails', 'rhs_key' => 'parent_id',	
			'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
			'relationship_role_column_value'=>'Project'),
		'projects_project_tasks' => array(
			'lhs_module'=> 'Project', 'lhs_table'=> 'project', 'lhs_key' => 'id',
			'rhs_module'=> 'ProjectTask', 'rhs_table'=> 'project_task', 'rhs_key' => 'project_id',	
			'relationship_type'=>'one-to-many'),	
        'projects_assigned_user' =>
           array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
           'rhs_module'=> 'Project', 'rhs_table'=> 'project', 'rhs_key' => 'assigned_user_id',
           'relationship_type'=>'one-to-many')
        
           ,'projects_modified_user' =>
           array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
           'rhs_module'=> 'Project', 'rhs_table'=> 'project', 'rhs_key' => 'modified_user_id',
           'relationship_type'=>'one-to-many')
        
           ,'projects_created_by' =>
           array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
           'rhs_module'=> 'Project', 'rhs_table'=> 'project', 'rhs_key' => 'created_by',
           'relationship_type'=>'one-to-many')
        //BEGIN SUGARCRM flav=pro ONLY 
           ,'projects_team' =>
           array('lhs_module'=> 'Teams', 'lhs_table'=> 'teams', 'lhs_key' => 'id',
           'rhs_module'=> 'Project', 'rhs_table'=> 'project', 'rhs_key' => 'team_id',
           'relationship_type'=>'one-to-many'),
           
        //END SUGARCRM flav=pro ONLY 
        'projects_users_resources' => array(
            'lhs_module'        => 'Project', 
            'lhs_table'         => 'project', 
            'lhs_key'           => 'id',
            'rhs_module'        => 'Users', 
            'rhs_table'         => 'users', 
            'rhs_key'           => 'id',
            'relationship_type' => 'many-to-many',
            'join_table'        => 'project_resources', 
            'join_key_lhs'      => 'project_id', 
            'join_key_rhs'      => 'resource_id',
            'relationship_role_column'=>'resource_type',
            'relationship_role_column_value'=>'Users',            
        ),
        'projects_contacts_resources' => array(
            'lhs_module'        => 'Project', 
            'lhs_table'         => 'project', 
            'lhs_key'           => 'id',
            'rhs_module'        => 'Contacts', 
            'rhs_table'         => 'contacts', 
            'rhs_key'           => 'id',
            'relationship_type' => 'many-to-many',
            'join_table'        => 'project_resources', 
            'join_key_lhs'      => 'project_id', 
            'join_key_rhs'      => 'resource_id',
            'relationship_role_column'=>'resource_type',
            'relationship_role_column_value'=>'Contacts',            
        ),        

        'projects_holidays' => array(
            'lhs_module'        => 'Project', 
            'lhs_table'         => 'project', 
            'lhs_key'           => 'id',
            'rhs_module'        => 'Holidays', 
            'rhs_table'         => 'holidays', 
            'rhs_key'           => 'related_module_id',
            'relationship_type' => 'one-to-many',          
        ),       

	),
);
?>
