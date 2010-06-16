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

$dictionary['KBDocumentKBTag'] = array('table' => 'kbdocuments_kbtags'
                               ,'fields' => array (
  'id' => 
  array (
    'name' => 'id',
    'vname' => 'LBL_KBDOCUMENTS_KBTAGS_ID',
    'type' => 'varchar',
    'len' => '36',
    'required'=>true,
    'reportable'=>false,
  ),  
  'kbdocument_id' => 
  array (
    'name' => 'kbdocument_id',
    'vname' => 'LBL_KBDOCUMENT_ID',
    'type' => 'varchar',
    'len' => '36',
    'required'=>false,
    'reportable'=>false,
  ),
  'kbtag_id' => 
  array (
    'name' => 'kbtag_id',
    'vname' => 'LBL_TAG_ID',
    'type' => 'varchar',
    'len' => '36',
    'required'=>false,
    'reportable'=>false,
  ),  
  'date_entered' => array (
	'name' => 'date_entered',
	'vname' => 'LBL_DATE_ENTERED',
	'type' => 'datetime',
	'comment' => 'Date record created'
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
    'dbType' => 'id',
    'source'=>'db',
  ),    
  'revision'=>
  array (
    'name' => 'revision',
    'vname' => 'LBL_REVISION',
    'type' => 'varchar',
    'len' => 100,
  ),    
  'deleted' => 
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'default' => 0,
    'reportable'=>false,
  ),
  'date_modified' => 
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
  ),
'created_by_link' =>
  array (
    'name' => 'created_by_link',
    'type' => 'link',
    'relationship' => 'revisions_created_by',
    'vname' => 'LBL_CREATED_BY_USER',
    'link_type' => 'one',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
  ),  
'created_by_name' => 
  array (
    'name' => 'created_by_name',
    'rname' => 'user_name',
    'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),    
    'id_name' => 'created_by',
    'vname' => 'LBL_CREATED_BY_NAME',
    'type' => 'relate',
    'table' => 'users',
    'isnull' => 'true',
    'module' => 'Users',
    'dbType' => 'varchar',
    'link'=>'created_by_link',
    'len' => '255',
   	'source'=>'non-db',
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

  'team_link' =>
  array (
    'name' => 'team_link',
    'type' => 'link',
    'relationship' => 'kbdocuments_kbtags_team',
    'vname' => 'LBL_TEAMS_LINK',
    'link_type' => 'one',
    'module'=>'Teams',
    'bean_name'=>'Team',
    'source'=>'non-db',
  ),

        //END SUGARCRM flav=pro ONLY 
        
  
  
), 
'relationships'=>array(
   'revisions_created_by' => array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
 	  		'rhs_module'=> 'DocumentRevisions', 'rhs_table'=> 'document_revisions', 'rhs_key' => 'created_by',
   			'relationship_type'=>'one-to-many'),
            
            
            //BEGIN SUGARCRM flav=pro ONLY 
             'kbdocuments_kbtags_team' =>
               array('lhs_module'=> 'Teams', 'lhs_table'=> 'teams', 'lhs_key' => 'id',
               'rhs_module'=> 'KBDocuments', 'rhs_table'=> 'kbdocuments_kbtags', 'rhs_key' => 'team_id',
               'relationship_type'=>'one-to-many'),
            //END SUGARCRM flav=pro ONLY 
            
),
'indices' => array (
       array('name' =>'kbdocumentskbtagspk', 'type' =>'primary', 'fields'=>array('id'))
)
);
?>
