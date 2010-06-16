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
$dictionary['Forums'] = array(
    'table' => 'forums', 'comment' => 'Forums are named collections of threads',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'vname' => 'LBL_ID',
            'required' => true,
            'type' => 'id',
            'reportable'=>false,
            'comment' => 'Unique identifier',
        ),
// BEGIN SUGARCRM flav=pro ONLY 
    'team_id' =>
      array (
        'name' => 'team_id',
        'vname' => 'LBL_TEAM_ID',
        'reportable'=>false,
        'dbtype' => 'id',
        'type' => 'team_list',
        'audited'=>true,
        'comment' => 'Team identifier',
      ),
// END SUGARCRM flav=pro ONLY 
        'date_entered' => array(
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record created',
        ),
        'category' => array(
            'name' => 'category',
            'vname' => 'LBL_CATEGORY',
            'type' => 'varchar',
            'required' => true,
            'len' => 255,
            'comment' => 'Category forum is associated',
        ),
        'category_ranking' => array(
            'name' => 'category_ranking',
            'source' => 'non-db',
            'type' => 'int',
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
            'comment' => 'User who created record',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record last modified',
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
            'default' => '0',
            'reportable'=>true,
            'comment' => 'User ID who last modified record',
        ),
        
        // basically the post count
        // threads are posts, so they are included in this
        //   (which is why it's called threadAndPostCount)
        'threadandpostcount'=>array(
            'name' =>'threadandpostcount',
            'vname' => 'LBL_THREAD_POST_COUNT',
            'type' => 'int',
            'default' => '0',
            'len' => 255,
            'comment' => 'The number of posts in this Forum. Threads are included in this count.'
        ),
        'threadcount'=>array(
            'name' =>'threadcount',
            'vname' => 'LBL_THREAD_COUNT',
            'type' => 'int',
            'default' => '0',
            'len' => 255,
            'comment' => 'The number of threads in this Forum.'
        ),
        'deleted' => array(
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => true,
            'default' => '0',
            'comment' => 'Record deletion indicator',
        ),  
        'title' => array(
            'name' => 'title',
            'vname' => 'LBL_TITLE',
            'required' => true,
            'type' => 'varchar',
            'len' => 255,
            'comment' => 'Forum title',
        ),
        'description' => array(
            'name' => 'description',
            'vname' => 'LBL_DESCRIPTION',
            'required' => false,
            'type' => 'text',
            'comment' => 'Forum description',
        ),
        'recent_thread_title' => array(
            'name' => 'recent_thread_title',
            'source'=>'non-db',
            'type' => 'varchar',
        ),
        'recent_thread_id' => array(
            'name' => 'recent_thread_id',
            'source'=>'non-db',
            'type' => 'id',
        ),
        'recent_thread_modified_name' => array(
            'name' => 'recent_thread_modified_name',
            'source'=>'non-db',
            'type' => 'assigned_user_name',
        ),
        'recent_thread_modified_id' => array(
            'name' => 'recent_thread_modified_id',
            'source'=>'non-db',
            'type' => 'id',
        ),
    ),

    'indices' => array(
        array('name' =>'forum_primary_key_index',
            'type' =>'primary',
            'fields'=>array('id')
            ),
    ),
);
?>
