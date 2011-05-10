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
$dictionary['DCEDataBase'] = array(
    'table'=>'dcedatabases',
    'audited'=>true,
    'fields'=>array (
    'primary_role' => 
    array (
        'name' => 'primary_role',
        'vname' => 'LBL_PRIMARY_ROLE',
        'type' => 'bool',
        'default' => 1,
        'massupdate' => 0,
        'comments' => 'DB Role',
        'help' => 'DB Role',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
    'reports_role' => 
    array (
        'name' => 'reports_role',
        'vname' => 'LBL_REPORTS_ROLE',
        'type' => 'bool',
        'default' => 0,
        'massupdate' => 0,
        'comments' => 'DB Role',
        'help' => 'DB Role',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
    'list_role' => 
    array (
        'name' => 'list_role',
        'vname' => 'LBL_LIST_ROLE',
        'type' => 'bool',
        'default' => 0,
        'massupdate' => 0,
        'comments' => 'DB Role',
        'help' => 'DB Role',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
    'search_role' => 
    array (
        'name' => 'search_role',
        'vname' => 'LBL_SEARCH_ROLE',
        'type' => 'bool',
        'default' => 0,
        'massupdate' => 0,
        'comments' => 'DB Role',
        'help' => 'DB Role',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
    ),
    'cluster_id' => 
    array (
        'required' => false,
        'name' => 'cluster_id',
        'vname' => '',
        'type' => 'id',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 0,
    ),
    'cluster_name' => 
    array (
        'required' => '1',
        'source' => 'non-db',
        'name' => 'cluster_name',
        'vname' => 'LBL_CLUSTER',
        'rname' => 'name',//for search
        'link' => 'clusters',//for search
        'type' => 'relate',
        'massupdate' => 0,
        'comments' => 'Cluster',
        'help' => 'Cluster',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'cluster_id',
        'ext2' => 'Clusters',
        'module' => 'DCEClusters',
        'studio' => 'visible',
    ),
    'clusters' =>
    array (
    'name' => 'clusters',
    'type' => 'link',
    'relationship' => 'DCEClusters_DCEDataBases',
    'link_type' => 'one',
    'source' => 'non-db',
    'vname' => 'LBL_CLUSTER',
    'duplicate_merge'=> 'disabled',
  ),
    'host' => 
    array (
        'required' => false,
        'name' => 'host',
        'vname' => 'LBL_HOST',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'DB Host',
        'help' => 'DB Host',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
    ),
    'user_name' => 
    array (
        'required' => false,
        'name' => 'user_name',
        'vname' => 'LBL_USER_NAME',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'DB server User Name',
        'help' => 'DB server User Name',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 100,
    ),
    'user_pass' => 
    array (
        'required' => false,
        'name' => 'user_pass',
        'vname' => 'LBL_USER_PASS',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'DB server User Password',
        'help' => 'DB server User Password',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
    ),
),
'relationships'=>array (
),
    'optimistic_lock'=>true,
);

VardefManager::createVardef('DCEDataBases','DCEDataBase', array('basic','team_security','assignable'));