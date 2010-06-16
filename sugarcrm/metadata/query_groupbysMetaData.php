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
$dictionary['QueryGroupBy'] = array('table' => 'query_groupbys'
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
    'reportable'=>true,
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
  'groupby_field' => 
  array (
    'name' => 'groupby_field',
    'vname' => 'LBL_GROUPBY_FIELD',
    'type' => 'varchar',
    'len' => '50',
  ),
    'groupby_module' => 
  array (
    'name' => 'groupby_module',
    'vname' => 'LBL_GROUPBY_MODULE',
    'type' => 'varchar',
    'len' => '50',
  ),
   'groupby_calc_field' => 
  array (
    'name' => 'groupby_calc_field',
    'vname' => 'LBL_GROUPBY_CALC_FIELD',
    'type' => 'varchar',
    'len' => '50',
  ),
    'groupby_calc_module' => 
  array (
    'name' => 'groupby_calc_module',
    'vname' => 'LBL_GROUPBY_CALC_MODULE',
    'type' => 'varchar',
    'len' => '50',
  ),
  'groupby_type' => 
  array (
    'name' => 'groupby_type',
    'vname' => 'LBL_GROUPBY_TYPE',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_type_dom',
    'len'=>25,
  ),
    'groupby_calc_type' => 
  array (
    'name' => 'groupby_calc_type',
    'vname' => 'LBL_GROUPBY_CALC_TYPE',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_calc_type_dom',
    'len'=>25,
  ),
    'parent_id' => 
  array (
    'name' => 'parent_id',
    'type' => 'id',
    'required'=>false,
    'reportable'=>false,
  ),
    'groupby_axis' => 
  array (
    'name' => 'groupby_axis',
    'vname' => 'LBL_GROUPBY_AXIS',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_axis_dom',
    'len'=>25,
  ),
    'list_order_x' => 
  array (
    'name' => 'list_order_x',
    'vname' => 'LBL_LIST_ORDER_X',
    'type' => 'int',
    'len' => '4',
  ),
     'list_order_y' => 
  array (
    'name' => 'list_order_y',
    'vname' => 'LBL_LIST_ORDER_Y',
    'type' => 'int',
    'len' => '4',
  ),
      'groupby_qualifier' => 
  array (
    'name' => 'groupby_qualifier',
    'vname' => 'LBL_GROUPBY_QUALIFIER',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_qualifier_dom',
    'len'=>25,
  ),
        'groupby_qualifier_qty' => 
  array (
    'name' => 'groupby_qualifier_qty',
    'vname' => 'LBL_GROUPBY_QUALIFIER_QTY',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_qualifier_qty_dom',
    'len'=>25,
  ),
        'groupby_qualifier_start' => 
  array (
    'name' => 'groupby_qualifier_start',
    'vname' => 'LBL_GROUPBY_QUALIFIER_START',
    'type' => 'enum',
    'required' => true,
    'options' => 'query_groupby_qualifier_start_dom',
    'len'=>25,
  ),
 
  
)
                                                      , 'indices' => array (
       array('name' =>'querygroupby_k', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_querygroupby', 'type'=>'index', 'fields'=>array('groupby_field','deleted')),
                                                      )
                            );
?>
