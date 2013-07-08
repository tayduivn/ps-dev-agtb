<?php
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

$dictionary['DBFTS'] = array(
    'table' => 'dbfts_search',
    'engine' => 'MyISAM', // To allow text search indexing on MySQL
    'audited' => false,
    'duplicate_merge' => false,

    'fields' => array(
        'name' =>
        array (
          'name' => 'name',
          'type' => 'varchar',
          'len' => '255',
          'comment' => 'Unused Name',
          'required'=>false,
          'source' => 'non-db',
        ),
        'description' =>
        array (
          'name' => 'description',
          'type' => 'varchar',
          'len' => '255',
          'comment' => 'Unused Name',
          'required'=>false,
          'source' => 'non-db',
        ),        
        'parent_type'=>
        array(
        	'name'=>'parent_type',
        	'vname'=>'LBL_PARENT_TYPE',
        	'type' =>'parent_type',
            'dbType' => 'varchar',
            'group'=>'parent_name',
            'options'=> 'parent_type_display',
        	'len'=> '255',
        	'comment' => 'Sugar module this record is associated with'
        ),
        'parent_id'=>
        array(
        	'name'=>'parent_id',
        	'vname'=>'LBL_PARENT_ID',
        	'type'=>'id',
        	'required'=>true,
        	'comment' => 'The ID of the Sugar item specified in parent_type'
        ),
        'field_name' =>
        array (
          'name' => 'field_name',
          'type' => 'varchar',
          'len' => '255',
          'comment' => 'Name of the field in the parent module',
      	  'required'=>true,
        ),
        'field_value' =>
     	  array (
     	    'name' => 'field_value',
     	    'vname' => 'LBL_DESCRIPTION',
     	    'type' => 'text',
            'required'=>true,
     	    'comment' => 'Actual value that we are text indexing',
     	    'rows' => 6,
     	    'cols' => 80,
     	  ),
        'boost' =>
        array (
          'name' => 'boost',
          'type' => 'int',
          'len' => '10',
          'comment' => 'Boost Value',
      	  'required'=>true,
        ),
    ),

    'indices' => array (
          array('name' =>'dbftsftk', 'type' =>'fulltext','fields'=>array('field_value'), 'db'=>'mysql'),
          array('name' =>'dbftsftk', 'type' =>'fulltext','fields'=>array('field_value'), 'db'=>'ibm_db2','options'=>'UPDATE FREQUENCY D(*) H(*) M(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55) UPDATE MINIMUM 1','message_locale' =>'en_US'), // Update the TS index every 5 minutes if only 1 record was updated
          array('name' =>'dbftsftk', 'type' =>'fulltext','fields'=>array('field_value'), 'db'=>'oci8','indextype'=>'CTXSYS.CONTEXT','parameters' =>'sync (on commit)'),
          array('name' =>'dbftsftk', 'type' =>'fulltext','fields'=>array('field_value'), 'db'=>'mssql','key_index'=>'fts_unique_idx','change_tracking' =>'AUTO', 'language' => 1033,'catalog'=>'default'),
       ),

    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => false,
    'acls' => array('SugarACLStatic' => false),
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('DBFTS', 'DBFTS', array('basic', 
//BEGIN SUGARCRM flav=pro ONLY
  'team_security'
//END SUGARCRM flav=pro ONLY
  ));
