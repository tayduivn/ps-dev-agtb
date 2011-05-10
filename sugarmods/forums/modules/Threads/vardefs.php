<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
/*********************************************************************************
 * $Id
 * Description:
 ********************************************************************************/
$dictionary['Threads'] = array(
	'table' => 'threads',
	'fields' => array(
		'id' => array(
			'name' => 'id',
			'vname' => 'LBL_ID',
			'required' => true,
			'type' => 'id',
			'reportable'=>false,
		),
		'date_entered' => array(
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
		),
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'created_by',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'assigned_user_name',
			'table' => 'created_by_users',
			'isnull' => 'false',
			'dbType' => 'id',
		),
		'created_by_user'=>array(
			'name' =>'created_by_user',
			'source'=>'non-db',
		    'type' => 'assigned_user_name',
		),		
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
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
			'required' => true,
			'default' => '',
			'reportable'=>true,
		),
		'modified_by_user'=>array(
			'name' =>'modified_by_user',
			'source'=>'non-db',
		    'type' => 'assigned_user_name',
		),
        'postcount'=>array(
            'name' =>'postcount',
            'vname' => 'LBL_POST_COUNT',
            'type' => 'int',
            'default' => '0',
            'len' => 255,
        ),
		'deleted' => array(
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
		),	
		'title' => array(
			'name' => 'title',
			'vname' => 'LBL_TITLE',
			'required' => true,
			'type' => 'varchar',
			'len' => 255,
		),
        'description_html' => array(
          'name' => 'description_html',
          'vname' => 'LBL_BODY',
          'type' => 'text',
        ),
		'forum_id' => array(
			'name' => 'forum_id',
			'vname' => 'LBL_FORUM_ID',
			'type' => 'id',
		),
		'forum_name'=>array(
			'name' =>'forum_name',
			'source'=>'non-db',
		    'type' => 'varchar',
		),
		'is_sticky' => array(
			'name' => 'is_sticky',
			'vname' => 'LBL_IS_STICKY',
			'type' => 'bool',
			'default' => '0',
		),
		'stickyDisplay'=>array(
			'name' =>'stickyDisplay',
			'source'=>'non-db',
		    'type' => 'bool',
		),


	    'recent_post_title' => array(
	      'name' => 'recent_post_title',
          'source' => 'non-db',
		  'type' => 'varchar',
		
	    ),
	    'recent_post_id' => array(
	      'name' => 'recent_post_id',
          'source' => 'non-db',
	      'type' => 'id',
	    
	    ),
	    'recent_post_modified_id' => array(
	      'name' => 'recent_post_modified_id',
          'source' => 'non-db',
	      'type' => 'id',
	    
	    ),	
	    'recent_post_modified_name' => array(
	      'name' => 'recent_post_modified_name',
          'source' => 'non-db',
	      'type' => 'varchar',
	    
	    ),
    	
		'view_count' => array(
			'name' => 'view_count',
			'vname' => 'LBL_VIEW_COUNT',
			'type' => 'int',
			'required' => true,
			'default' => 0,
		),
		'accounts' => array (
			'name' => 'accounts',
			'type' => 'link',
			'relationship' => 'accounts_threads',
			'source' => 'non-db',
			'vname' => 'LBL_ACCOUNTS',
		),  
		'bugs' => array (
			'name' => 'bugs',
			'type' => 'link',
			'relationship' => 'bugs_threads',
			'source' => 'non-db',
			'vname' => 'LBL_BUGS',
		),  
		'cases' => array (
			'name' => 'cases',
			'type' => 'link',
			'relationship' => 'cases_threads',
			'source' => 'non-db',
			'vname' => 'LBL_CASES',
		),  
		'opportunities' => array (
			'name' => 'opportunities',
			'type' => 'link',
			'relationship' => 'opportunities_threads',
            'module'=>'opportunities',
            'bean_name'=>'Opportunities',
    		'source' => 'non-db',
			'vname' => 'LBL_OPPORTUNITIES',
		), 
        'project' => array (
            'name' => 'project',
            'type' => 'link',
            'relationship' => 'project_threads',
            'module'=>'project',
            'bean_name'=>'Project',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECT',
        ), 
	),
	
	'indices' => array(
		array('name' =>'thread_primary_key_index',
			'type' =>'primary',
			'fields'=>array('id')
			),
	),
);
?>
