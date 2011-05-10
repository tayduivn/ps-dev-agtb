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
 *Portions created by SugarCRM are Copyright (C) 2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['KBDocument'] = array('table' => 'kbdocuments',
                'comment' => 'Knowledge Document management and FTS'
                               ,'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LBL_KBDOCUMENT_ID',
    'type' => 'varchar',
    'len' => '36',
    'required'=>true,
    'reportable'=>false,
  ),

  'kbdocument_name' =>
  array (
    'name' => 'kbdocument_name',
    'vname' => 'LBL_NAME',
    'type' => 'varchar',
    'required'=>true
  ),

'active_date' =>
  array (
    'name' => 'active_date',
    'vname' => 'LBL_DOC_ACTIVE_DATE',
    'type' => 'date',
  ),

'exp_date' =>
  array (
    'name' => 'exp_date',
    'vname' => 'LBL_DOC_EXP_DATE',
    'type' => 'date',
  ),

  'status_id' =>
  array (
    'name' => 'status_id',
    'vname' => 'LBL_DOC_STATUS',
    'type' => 'enum',
    'len' => 100,
    'options' => 'kbdocument_status_dom',
    'reportable'=>false,
  ),

  'date_entered' =>
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_DATE_ENTERED',
    'type' => 'datetime',
  ),

  'date_modified' =>
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
  ),

  'deleted' =>
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'default'=> 0,
    'reportable'=>false,
  ),
  'is_external_article' =>
  array (
    'name' => 'is_external_article',
    'vname' => 'LBL_IS_EXTERNAL_ARTICLE',
    'type' => 'bool',
    'default'=> 0,
    'reportable'=>true,
  ),
  'description' =>
  array (
    'name' => 'description',
    'vname' => 'LBL_DESCRIPTION',
    'type' => 'text',
  ),
  'modified_user_id' =>
  array (
    'name' => 'modified_user_id',
    'rname' => 'user_name',
    'id_name' => 'modified_user_id',
    'vname' => 'LBL_MODIFIED',
    'type' => 'assigned_user_name',
    'table' => 'users',
    'isnull' => 'false',
    'reportable'=>true,
    'dbType' => 'id'
  ),
  'modified_user_name' =>
  array (
     'name' => 'modified_user_name',
     'rname' => 'id',
     'id_name' => 'user_id',
     'vname' => 'LBL_MODIFIED_USER',
     'join_name'=>'users',
     'type' => 'relate',
     'link' => 'users',
     'table' => 'users',
     'isnull' => 'true',
     'module' => 'Users',
     'dbType' => 'varchar',
     'len' => '255',
     'source' => 'non-db'
  ),
  'created_by' =>
  array (
    'name' => 'created_by',
    'rname' => 'user_name',
    'id_name' => 'modified_user_id',
    'vname' => 'LBL_CREATED',
    'type' => 'assigned_user_name',
    'table' => 'users',
    'isnull' => 'false',
    'dbType' => 'id'
  ),

  //BEGIN SUGARCRM flav=pro ONLY 
  'team_id' =>
  array (
    'name' => 'team_id',
    'vname' => 'LBL_TEAM_ID',
    'reportable'=>false,
    'dbtype' => 'id',
    'type' => 'team_list',
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
            'link' => 'team_link',
            'massupdate' => false,
            'dbType' => 'varchar',
            'source' => 'non-db',
            'len' => 36,
        ),

        //END SUGARCRM flav=pro ONLY 

  'kbdocument_revision_id'=>
  array (
    'name' => 'kbdocument_revision_id',
    'vname' => 'LBL_LATEST_REVISION',
    'type' => 'varchar',
    'len' => '36',
    'reportable'=>false,
  ),
 'kbdocument_revision_number'=>
  array (
    'name' => 'kbdocument_revision_number',
    'vname' => 'LBL_KBDOCUMENT_REVISION_NUMBER',
    'type' => 'varchar',
    'len' => 100,
    'comment' => 'Kbdocument revision number',
  ),


  'revisions' =>
  array (
    'name' => 'revisions',
    'type' => 'link',
    'relationship' => 'kbdocument_revisions',
    'source'=>'non-db',
     'vname'=>'LBL_REVISIONS',
  ),
  'latest_revision' =>
  array (
    'name' => 'latest_revision',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'last_rev_create_date' =>
  array (
    'name' => 'last_rev_create_date',
    'type' => 'date',
    'table' => 'kbdocument_revisions',
    'link'  => 'revisions',
    'join_name'  => 'kbdocument_revisions',
    'vname'=>'LBL_LAST_REV_CREATE_DATE',
    'rname'=> 'date_entered',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  /* mail_merge_document has been deprecated*/
 'mail_merge_document' =>
  array (
    'name' => 'mail_merge_document',
    'vname' => 'LBL_MAIL_MERGE_DOCUMENT',
    'type' => 'bool',
    'dbType' => 'varchar',
    'len' => '3',
    'default' => 'off',
    'audited'=>true,
  ),

  //BEGIN SUGARCRM flav=pro ONLY 
  'team_link' =>
  array (
        'name' => 'team_link',
    'type' => 'link',
    'relationship' => 'documents_team',
    'vname' => 'LBL_TEAMS_LINK',
    'link_type' => 'one',
    'module'=>'Teams',
    'bean_name'=>'Team',
    'source'=>'non-db',
  ),
  //END SUGARCRM flav=pro ONLY 
  'cases' => array (
    'name' => 'cases',
    'type' => 'link',
    'relationship' => 'case_kbdocuments',
    'source' => 'non-db',
    'vname' => 'LBL_CASES',
  ),
  'emails' => array (
	'name' => 'emails',
	'type' => 'link',
	'relationship' => 'email_kbdocuments',
	'source' => 'non-db',
	'vname' => 'LBL_EMAILS',
   ),
  'contracts' => array (
    'name' => 'contracts',
    'type' => 'link',
    'relationship' => 'contracts_documents',
    'source' => 'non-db',
    'vname' => 'LBL_CONTRACTS',
  ),
  //todo remove
  'leads' => array (
    'name' => 'leads',
    'type' => 'link',
    'relationship' => 'leads_documents',
    'source' => 'non-db',
    'vname' => 'LBL_CONTRACTS',
  ),
  'created_by_link' =>
  array (
        'name' => 'created_by_link',
    'type' => 'link',
    'relationship' => 'kbdocuments_created_by',
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
    'relationship' => 'kbdocuments_modified_user',
    'vname' => 'LBL_MODIFIED_BY_USER',
    'link_type' => 'one',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
  ),

  'related_doc_id' =>
  array (
    'name' => 'related_doc_id',
    'vname' => 'LBL_RELATED_DOCUMENT_ID',
    'reportable'=>false,
    'dbtype' => 'id',
    'type' => 'varchar',
    'len' => '36',
  ),
  'related_doc_rev_id' =>
  array (
    'name' => 'related_doc_rev_id',
    'vname' => 'LBL_RELATED_DOCUMENT_REVISION_ID',
    'reportable'=>false,
    'dbtype' => 'id',
    'type' => 'varchar',
    'len' => '36',
  ),
  'is_template' =>
  array (
    'name' => 'is_template',
    'vname' => 'LBL_IS_TEMPLATE',
    'type' => 'bool',
    'default'=> 0,
    'reportable'=>false,
  ),
  'template_type' =>
  array (
    'name' => 'template_type',
    'vname' => 'LBL_TEMPLATE_TYPE',
    'type' => 'enum',
    'len' => 100,
    'options' => 'document_template_type_dom',
    'reportable'=>false,
  ),
//BEGIN field used for contract document subpanel.
  'latest_revision_name' =>
  array (
    'name' => 'latest_revision_name',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'selected_revision_name' =>
  array (
    'name' => 'selected_revision_name',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'contract_status' =>
  array (
    'name' => 'contract_status',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'contract_name'=>
  array (
    'name' => 'contract_name',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'linked_id'=>
  array (
    'name' => 'linked_id',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'selected_revision_id'=>
  array (
    'name' => 'selected_revision_id',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'latest_revision_id'=>
  array (
    'name' => 'latest_revision_id',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'selected_revision_filename'=>
  array (
    'name' => 'selected_revision_filename',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
  'keywords'=>
  array (
    'name' => 'keywords',
    'vname' => 'LBL_KEYWORDS',
    'type' => 'relate',
    'reportable'=>false,
    'source'=>'non-db'
  ),
//END fields used for contract documents subpanel.

  'kbdoc_approver_id' =>
  array (
    'name' => 'kbdoc_approver_id',
    'rname' => 'user_name',
    'id_name' => 'kbdoc_approver_id',
    'vname' => 'LBL_KBDOC_APPROVER',
    'type' => 'assigned_user_name',
    'table' => 'kbdoc_approver_id_users',
    'reportable'=>true,
    'dbType' => 'id',
    'len' => 36,
    'audited'=>true,
    'comment' => 'User ID of the document approver',
    'duplicate_merge'=>'disabled'
  ),


 'kbdoc_approver_name'=>
  array (
    'name' => 'kbdoc_approver_name',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),
 'assigned_user_id' => array (
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
    'comment' => 'User ID assigned to record'
    ),
 'assigned_user_name' => array (
    'name' => 'assigned_user_name',
    'vname' => 'LBL_ASSIGNED_TO',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'nondb',
    'table' => 'users'
 ),

 'assigned_user_link' => array (
    'name' => 'assigned_user_link',
    'type' => 'link',
    'relationship' => 'kb_assigned_user',
    'vname' => 'LBL_ASSIGNED_TO_USER',
    'link_type' => 'one',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
    'duplicate_merge'=>'disabled',
    'rname' => 'user_name',
    'id_name' => 'assigned_user_id',
    'table' => 'users',
 ),


 'kbdoc_approver_link' => array (
    'name' => 'kbdoc_approver_link',
    'type' => 'link',
    'relationship' => 'kbdoc_approver_user',
    'vname' => 'LBL_KBDOC_APPROVED_BY',
    'link_type' => 'one',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
    'duplicate_merge'=>'disabled',
 ),

 'views_number'=>
  array (
    'name' => 'views_number',
    'type' => 'varchar',
    'reportable'=>false,
    'source'=>'non-db'
  ),

  'parent_id' =>
  array (
    'name' => 'parent_id',
    'type' => 'id',
    'reportable'=>false,
  ),
  'parent_type' =>
  array (
    'name' => 'parent_type',
    'type' => 'varchar',
    'len' => 100,
    'reportable'=>false,
  ),
),
 'indices' => array (
       array('name' =>'kbdocumentspk', 'type' =>'primary', 'fields'=>array('id')),
       ),
 'relationships' => array (
    'kbdocument_revisions' => array('lhs_module'=> 'KBDocuments', 'lhs_table'=> 'kbdocuments', 'lhs_key' => 'id',
                              'rhs_module'=> 'KBDocumentRevisions', 'rhs_table'=> 'kbdocument_revisions', 'rhs_key' => 'kbdocument_id',
                              'relationship_type'=>'one-to-many'),
    'kbdocuments_modified_user' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'modified_user_id',
   'relationship_type'=>'one-to-many') ,

  'kbdocuments_created_by' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'created_by',
   'relationship_type'=>'one-to-many'),

// BEGIN SUGARCRM flav=pro ONLY 
 'kbdocuments_team' =>
   array('lhs_module'=> 'Teams', 'lhs_table'=> 'teams', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'team_id',
   'relationship_type'=>'one-to-many'),
// END SUGARCRM flav=pro ONLY 
 'kb_assigned_user' => array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'assigned_user_id',
   'relationship_type'=>'one-to-many'),

 'kbdoc_approver_user' => array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'kbdoc_approver_id',
   'relationship_type'=>'one-to-many'),


 'case_kbdocuments' =>
   array('lhs_module'=> 'Cases', 'lhs_table'=> 'cases', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'parent_id',
   'relationship_type'=>'one-to-many','relationship_role_column'=>'parent_type', 'relationship_role_column_value'=>'Cases'),

 'email_kbdocuments' =>
   array('lhs_module'=> 'Emails', 'lhs_table'=> 'emails', 'lhs_key' => 'id',
   'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments', 'rhs_key' => 'parent_id',
   'relationship_type'=>'one-to-many','relationship_role_column'=>'parent_type',
	'relationship_role_column_value'=>'Emails'),
   ),
);
?>
