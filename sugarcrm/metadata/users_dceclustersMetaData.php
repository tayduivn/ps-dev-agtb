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
//FILE SUGARCRM flav=dce ONLY
$dictionary['users_dceclusters'] = array ( 
    'table' => 'users_dceclusters', 
    'fields' => array (
        array('name' =>'id', 'type' =>'varchar', 'len'=>'36'),
        array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36', ),
        array('name' =>'cluster_id', 'type' =>'varchar', 'len'=>'36', ),
        array ('name' => 'date_modified','type' => 'datetime'),
        array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0','required'=>false),
    ),
    'indices' => array (
        array('name' =>'users_dceclusterspk', 'type' =>'primary', 'fields'=>array('id')),
        array('name' =>'idx_use_clu_use', 'type' =>'index', 'fields'=>array('user_id')),
        array('name' =>'idx_use_clu_clu', 'type' =>'index', 'fields'=>array('cluster_id')),
        array('name' => 'idx_user_dcecluster', 'type'=>'alternate_key', 'fields'=>array('user_id','cluster_id')),           
    ),
    'relationships' => array (
        'users_dceclusters' => array(
            'lhs_module'=> 'Users', 
            'lhs_table'=> 'users', 
            'lhs_key' => 'id',
            'rhs_module'=> 'DCEClusters', 
            'rhs_table'=> 'dceclusters', 
            'rhs_key' => 'id',
            'relationship_type'=>'many-to-many',
            'join_table'=> 'users_dceclusters', 
            'join_key_lhs'=>'user_id', 
            'join_key_rhs'=>'cluster_id',
        ),
    ),
);
?>
