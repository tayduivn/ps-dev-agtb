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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=ent ONLY
$dictionary['QueryFilter'] = array('table' => 'query_filters'
                               ,'fields' => array (
  'id' => 
  array (
    'name' => 'id',
    'vname' => 'LBL_NAME',
    'type' => 'id',
    'required' => true,
    'reportable'=>false,
  ),
   'deleted' => 
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'required' => true,
    'default' => '0',
    'reportable'=>false,
  ),
   'date_entered' => 
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_DATE_ENTERED',
    'type' => 'datetime',
    'required' => true,
  ),
  'date_modified' => 
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
    'required' => true,
  ),
    'modified_user_id' => 
  array (
    'name' => 'modified_user_id',
    'rname' => 'user_name',
    'id_name' => 'modified_user_id',
    'vname' => 'LBL_ASSIGNED_TO',
    'type' => 'assigned_user_name',
    'table' => 'users',
    'isnull' => 'false',
    'dbType' => 'id',
    'required' => true,
  ),
  'created_by' => 
  array (
    'name' => 'created_by',
    'rname' => 'user_name',
    'id_name' => 'modified_user_id',
    'vname' => 'LBL_ASSIGNED_TO',
    'type' => 'assigned_user_name',
    'table' => 'users',
    'isnull' => 'false',
    'dbType' => 'id'
  ),
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_FILTER_NAME',
    'type' => 'varchar',
    'len' => '50',
  ),
  'left_field' => 
  array (
    'name' => 'left_field',
    'vname' => 'LBL_LEFT_FIELD',
    'type' => 'varchar',
    'len' => '50',
  ),
    'left_module' => 
  array (
    'name' => 'left_module',
    'vname' => 'LBL_LEFT_MODULE',
    'type' => 'varchar',
    'len' => '50',
  ),
    'right_field' => 
  array (
    'name' => 'right_field',
    'vname' => 'LBL_RIGHT_FIELD',
    'type' => 'varchar',
    'len' => '50',
  ),
    'right_module' => 
  array (
    'name' => 'right_module',
    'vname' => 'LBL_RIGHT_MODULE',
    'type' => 'varchar',
    'len' => '50',
  ),
  'filter_type' => 
  array (
    'name' => 'filter_type',
    'vname' => 'LBL_FILTER_TYPE',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_filter_type_dom',
    'len'=>25,
  ),
    'left_type' => 
  array (
    'name' => 'left_type',
    'vname' => 'LBL_FILTER_LEFT_TYPE',
    'type' => 'enum',
    'options' => 'query_calc_leftright_type_dom',
    'len'=>10,
  ),
  	  'right_type' => 
  array (
    'name' => 'right_type',
    'vname' => 'LBL_FILTER_RIGHT_TYPE',
    'type' => 'enum',
    'options' => 'query_calc_leftright_type_dom',
    'len'=>10,
  ),
    'left_value' => 
  array (
    'name' => 'left_value',
    'vname' => 'LBL_LEFT_VALUE',
    'type' => 'varchar',
    'len' => '100',
  ),
      'right_value' => 
  array (
    'name' => 'right_value',
    'vname' => 'LBL_RIGHT_VALUE',
    'type' => 'varchar',
    'len' => '100',
  ),
    'parent_id' => 
  array (
    'name' => 'parent_id',
    'type' => 'id',
    'required' => true,
    'reportable'=>false,
  ),
      'parent_filter_id' => 
  array (
    'name' => 'parent_filter_id',
    'type' => 'id',
    'required' => true,
    'reportable'=>false,
  ),
    'list_order' => 
  array (
    'name' => 'list_order',
    'vname' => 'LBL_LIST_ORDER',
    'type' => 'int',
    'len' => '4',
  ),
  'parent_filter_group' => 
  array (
    'name' => 'parent_filter_group',
    'vname' => 'LBL_PARENT_FILTER_GROUP',
    'type' => 'int',
    'len' => '8',
      ),
  'operator' => 
  array (
    'name' => 'operator',
    'vname' => 'LBL_OPERATOR',
    'type' => 'varchar',
    'len' => '15',
     ),
  'calc_enclosed' => 
  array (
    'name' => 'calc_enclosed',
    'vname' => 'LBL_CALC_ENCLOSED',
    'type' => 'varchar',
    'len' => '3',
  ),
  
)
                                                      , 'indices' => array (
       array('name' =>'filter_k', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_filter', 'type'=>'index', 'fields'=>array('name','deleted')),
                                                      )
 );
?>
