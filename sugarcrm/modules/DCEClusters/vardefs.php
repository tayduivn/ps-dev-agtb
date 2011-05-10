<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
$dictionary['DCECluster'] = array(
    'table'=>'dceclusters',
    'audited'=>true,
    'fields'=>array (
  'url' => 
  array (
    'required' => true,
    'name' => 'url',
    'vname' => 'LBL_URL',
    'type' => 'varchar',
    'massupdate' => 0,
    'comments' => 'Site URL',
    'help' => 'Site URL',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => 255,
  ),
  'url_format' => 
  array (
    'name' => 'url_format',
    'vname' => 'LBL_URL_FORMAT',
    'type' => 'enum',
    'options' => 'url_format_list',
    'default' => 'URL/Instance_Name',
    'massupdate' => 0,
    'comments' => 'URL Format',
    'help' => 'URL Format',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => 100,
  ),
  'server_status' => 
  array (
    'required' => false,
    'name' => 'server_status',
    'vname' => 'LBL_SERVER_STATUS',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'active',
    'comments' => 'Server Status',
    'help' => 'Server Status',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => 100,
    'options' => 'server_status_list',
    'studio' => 'visible',
  ),
    'DCEActions' =>
    array (
        'name' => 'DCEActions',
        'type' => 'link',
        'relationship' => 'DCEClusters_DCEActions',
        'module'=>'DCEActions',
        'bean_name'=>'DCEAction',
        'source'=>'non-db',
    ),
    'DCEDataBases' =>
    array (
        'name' => 'DCEDataBases',
        'type' => 'link',
        'relationship' => 'DCEClusters_DCEDataBases',
        'module'=>'DCEDataBases',
        'bean_name'=>'DCEDataBase',
        'source'=>'non-db',
    ),
    'DCEInstances' =>
    array (
        'name' => 'DCEInstances',
        'type' => 'link',
        'relationship' => 'DCEClusters_DCEInstances',
        'module'=>'DCEInstances',
        'bean_name'=>'DCEInstance',
        'source'=>'non-db',
    ),
    'contacts' =>
    array (
        'name' => 'contacts',
        'type' => 'link',
        'relationship' => 'contacts_dceclusters',
        'source'=>'non-db',
        'vname'=>'LBL_CONTACTS',
    ),
    'users' =>
    array (
        'name' => 'users',
        'type' => 'link',
        'relationship' => 'users_dceclusters',
        'source'=>'non-db',
        'vname'=>'LBL_USERS',
    ),
),
'relationships'=>array (
    'DCEClusters_DCEActions' => array(
        'lhs_module'        =>  'DCEClusters',
        'lhs_table'         =>  'dceclusters',
        'lhs_key'           =>  'id',
        'rhs_module'        =>  'DCEActions',
        'rhs_table'         =>  'dceactions',
        'rhs_key'           =>  'cluster_id',
        'relationship_type' =>'one-to-many'
    ),
    'DCEClusters_DCEDataBases' => array(
        'lhs_module'        =>  'DCEClusters',
        'lhs_table'         =>  'dceclusters',
        'lhs_key'           =>  'id',
        'rhs_module'        =>  'DCEDataBases',
        'rhs_table'         =>  'dcedatabases',
        'rhs_key'           =>  'cluster_id',
        'relationship_type' =>'one-to-many'
    ),
),
    'optimistic_lock'=>true,
);

VardefManager::createVardef('DCEClusters','DCECluster', array('basic','team_security','assignable'));