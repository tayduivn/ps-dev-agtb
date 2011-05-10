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
$dictionary['iFrame'] = array('table' => 'iframes'
                               ,'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LBL_ID',
    'type' => 'id',
    'required'=>true,
  ),
  'name' =>
  array (
    'name' => 'name',
    'vname' => 'LBL_LIST_NAME',
    'type' => 'varchar',
    'len' => '255',
    'required'=>true,
    'importable' => 'required',
  ),
  'url' =>
  array (
    'name' => 'url',
    'vname' => 'LBL_LIST_URL',
    'type' => 'varchar',
    'len' => '255',
    'required'=>true,
    'importable' => 'required',
  ),
  'type' =>
  array (
    'name' => 'type',
    'vname' => 'LBL_LIST_TYPE',
    'type' => 'varchar',
    'len' => '255',
    'required'=>true,
  ),
  'placement' =>
  array (
    'name' => 'placement',
    'vname' => 'LBL_LIST_PLACEMENT',
    'type' => 'varchar',
    'len' => '255',
    'required'=>true,
    'importable' => 'required',
  ),
  'status' =>
  array (
    'name' => 'status',
    'vname' => 'LBL_LIST_STATUS',
    'type' => 'bool',
    'required'=>true,
  ),
  'deleted' =>
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'required'=>true,
  ),
  'date_entered' =>
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_DATE_ENTERED',
    'type' => 'datetime',
    'required'=>true,
  ),
  'date_modified' =>
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
    'required'=>true,
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
    'dbType' => 'id',
    'required'=>true,
  ),
)
                                                      , 'indices' => array (
       array('name' =>'iframespk', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_cont_name', 'type'=>'index', 'fields'=>array('name','deleted'))
                                                      )
                            );
?>