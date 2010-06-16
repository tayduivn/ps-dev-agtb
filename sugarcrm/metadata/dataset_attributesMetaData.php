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
$dictionary['DataSet_Attribute'] = array('table' => 'dataset_attributes'
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
  'display_type' => 
  array (
    'name' => 'display_type',
    'vname' => 'LBL_DISPLAY_TYPE',
    'type' => 'enum',
    'required' => true,
    'options' => 'dataset_att_display_type_dom',
    'len'=>25,
  ),
    'display_name' => 
  array (
    'name' => 'display_name',
    'vname' => 'LBL_DISPLAY_NAME',
    'type' => 'varchar',
    'len' => '50',
  ),
  'attribute_type' => 
  array (
    'name' => 'attribute_type',
    'vname' => 'LBL_ATT_TYPE',
    'type' => 'varchar',
    'required' => true,
    'len'=>8,
  ),
    'parent_id' => 
  array (
    'name' => 'parent_id',
    'type' => 'id',
    'required'=>false,
    'reportable'=>false,
  ),
  'font_size' => 
  array (
    'name' => 'font_size',
    'vname' => 'LBL_FONT_SIZE',
    'type' => 'enum',
    'options' => 'font_size_dom',
    'len'=>8,
    'default' => '0',
  ),
   'cell_size' => 
  array (
    'name' => 'cell_size',
    'vname' => 'LBL_CELL_SIZE',
    'type' => 'varchar',
    'len' => '3',
  ),
  'size_type' => 
  array (
    'name' => 'size_type',
    'vname' => 'LBL_SIZE_TYPE',
    'type' => 'enum',
    'options' => 'width_type_dom',
    'len'=>3,
  ),
  'bg_color' => 
  array (
    'name' => 'bg_color',
    'vname' => 'LBL_BG_COLOR',
    'type' => 'enum',
    'options' => 'report_color_dom',
    'len'=>25,
  ),
    'font_color' => 
  array (
    'name' => 'font_color',
    'vname' => 'LBL_FONT_COLOR',
    'type' => 'enum',
    'options' => 'report_color_dom',
    'len'=>25,
  ),
    'wrap' => 
  array (
    'name' => 'wrap',
    'vname' => 'LBL_WRAP',
    'type' => 'bool',
    'dbType' => 'varchar',
    'len' => '3',
  ),
  'style' => 
  array (
    'name' => 'style',
    'vname' => 'LBL_STYLE',
    'type' => 'enum',
    'options' => 'dataset_style_dom',
    'len'=>25,
  ),
    'format_type' => 
  array (
    'name' => 'format_type',
    'vname' => 'LBL_FORMAT_TYPE',
    'type' => 'enum',
    'required' => true,
    'options' => 'dataset_att_format_type_dom',
    'len'=>25,
  ),
)
                                                      , 'indices' => array (
       array('name' =>'datasetatt_k', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_datasetatt', 'type'=>'index', 'fields'=>array('parent_id','deleted')),
                                                      )
                            );
?>
