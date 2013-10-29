<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['Activity'] = array(
    'table' => 'activities',
    'fields' => array(
        // Set unnecessary fields from Basic to non-required/non-db.
        'name' => array(
            'name' => 'name',
            'type' => 'varchar',
            'required' => false,
            'source' => 'non-db',
        ),

        'description' => array(
            'name' => 'description',
            'type' => 'varchar',
            'required' => false,
            'source' => 'non-db',
        ),

        // Add relationship fields.
        'comments' => array(
            'name' => 'comments',
            'type' => 'link',
            'relationship' => 'comments',
            'link_type' => 'many',
            'module' => 'Comments',
            'bean_name' => 'Comment',
            'source' => 'non-db',
        ),

        'activities_users' => array(
            'name' => 'activities_users',
            'type' => 'link',
            'relationship' => 'activities_users',
            'link_type' => 'many',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),

        'activities_teams' => array(
            'name' => 'activities_teams',
            'type' => 'link',
            'relationship' => 'activities_teams',
            'link_type' => 'many',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),

        // Relationships for M2M related beans.
        'contacts' => array(
            'name' => 'contacts',
            'type' => 'link',
            'relationship' => 'contact_activities',
            'vname' => 'LBL_LIST_CONTACT_NAME',
            'source' => 'non-db',
        ),
        'cases' => array(
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'case_activities',
            'vname' => 'LBL_CASES',
            'source' => 'non-db',
        ),
        'accounts' => array(
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'account_activities',
            'source' => 'non-db',
            'vname' => 'LBL_ACCOUNTS',
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'type' => 'link',
            'relationship' => 'opportunity_activities',
            'source' => 'non-db',
            'vname' => 'LBL_OPPORTUNITIES',
        ),
        'leads' => array(
            'name' => 'leads',
            'type' => 'link',
            'relationship' => 'lead_activities',
            'source' => 'non-db',
            'vname' => 'LBL_LEADS',
        ),
        //BEGIN SUGARCRM flav=pro ONLY
        'products' => array(
            'name' => 'products',
            'type' => 'link',
            'relationship' => 'product_activities',
            'source' => 'non-db',
            'vname' => 'LBL_PRODUCTS',
        ),
        'revenuelineitems' => array(
            'name' => 'revenuelineitems',
            'type' => 'link',
            'relationship' => 'revenuelineitem_activities',
            'source' => 'non-db',
            'vname' => 'LBL_REVENUELINEITEMS',
        ),
        'quotes' => array(
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'quote_activities',
            'vname' => 'LBL_QUOTES',
            'source' => 'non-db',
        ),
        'contracts' => array(
            'name' => 'contracts',
            'type' => 'link',
            'relationship' => 'contract_activities',
            'source' => 'non-db',
            'vname' => 'LBL_CONTRACTS',
        ),
        //END SUGARCRM flav=pro ONLY
        'bugs' => array(
            'name' => 'bugs',
            'type' => 'link',
            'relationship' => 'bug_activities',
            'source' => 'non-db',
            'vname' => 'LBL_BUGS',
        ),
        'emails' => array(
            'name'=> 'emails',
            'vname'=> 'LBL_EMAILS',
            'type'=> 'link',
            'relationship'=> 'emails_activities',
            'source'=> 'non-db',
        ),
        'projects' => array(
            'name' => 'projects',
            'type' => 'link',
            'relationship' => 'projects_activities',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS',
        ),
        'project_tasks' => array(
            'name' => 'project_tasks',
            'type' => 'link',
            'relationship' => 'project_tasks_activities',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECT_TASKS',
        ),
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'meeting_activities',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS',
        ),
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'calls_activities',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS',
        ),
        'tasks' => array(
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'task_activities',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
        ),
        'notes' => array(
            'name'         => 'notes',
            'type'         => 'link',
            'relationship' => 'note_activities',
            'source'       => 'non-db',
            'vname'        => 'LBL_NOTES',
        ),

        // Add table columns.
        'parent_id' => array(
            'name'     => 'parent_id',
            'type'     => 'id',
            'len'      => 36,
        ),

        'parent_type' => array(
            'name' => 'parent_type',
            'type' => 'varchar',
            'len'  => 100,
        ),

        'activity_type' => array(
            'name' => 'activity_type',
            'type' => 'varchar',
            'len'  => 100,
            'required' => true,
        ),

        'data' => array(
            'name' => 'data',
            'type' => 'json',
            'dbType' => 'longtext',
            'required' => true,
        ),

        'comment_count' => array(
            'name' => 'comment_count',
            'type' => 'int',
            'required' => true,
            'default' => 0,
        ),

        'last_comment' => array(
            'name' => 'last_comment',
            'type' => 'json',
            'dbType' => 'longtext',
            'required' => true,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'activity_records',
            'type' => 'index',
            'fields' => array('parent_type', 'parent_id'),
        ),
    ),
    'relationships' => array(
        'comments' => array(
            'lhs_module' => 'Activities',
            'lhs_table' => 'activities',
            'lhs_key' => 'id',
            'rhs_module' => 'Comments',
            'rhs_table' => 'comments',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
);

VardefManager::createVardef('ActivityStream/Activities', 'Activity', array('basic'));
