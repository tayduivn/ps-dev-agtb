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
$dictionary['Posts'] = array(
	'table' => 'posts',
	'comment' => 'Captures posts made to threads in Forums module',
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
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'created_by',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'assigned_user_name',
			'table' => 'created_by_users',
			'isnull' => 'false',
			'dbType' => 'id',
			'comment' => 'User who created record'
		),
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record last modified'
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
			'comment' => 'User who last modified record'
		),

    'created_by_user' => array(
      'name' => 'created_by_user',
      'source' => 'non-db',
	  'type' => 'assigned_user_name',
    ),
    'modified_by_user' => array(
      'name' => 'modified_by_user',
      'source' => 'non-db',
      'type' => 'assigned_user_name',
    ),
    
		'deleted' => array(
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
			'comment' => 'Record deletion indicator'
		),	
		'title' => array(
			'name' => 'title',
			'vname' => 'LBL_TITLE',
			'required' => true,
			'type' => 'varchar',
			'len' => 255,
			'comment' => 'Title of the post'
		),
		'description_html' => array(
			'name' => 'description_html',
			'vname' => 'LBL_BODY',
			'type' => 'text',
			'comment' => 'Post content'
		),
		'thread_id' => array(
			'name' => 'thread_id',
			'vname' => 'LBL_THREAD_ID',
			'type' => 'id',
			'comment' => 'Associated thread'
		),
        'thread_name' => array(
            'name' => 'thread_name',
            'source' => 'non-db',
		    'type' => 'varchar',
        ),

	),
	
	'indices' => array(
		array('name' =>'post_primary_key_index',
			'type' =>'primary',
			'fields'=>array('id')
			),
	),
);
?>
