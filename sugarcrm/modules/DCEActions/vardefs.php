<?php
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
$dictionary['DCEAction'] = array(
    'table'=>'dceactions',
    'audited'=>true,
    'fields'=>array (
    'instance_id' => 
    array (
      'required' => false,
      'name' => 'instance_id',
      'vname' => 'LBL_INSTANCE_ID',
      'type' => 'id',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
    ),
    'cluster_id' => 
    array (
      'required' => false,
      'name' => 'cluster_id',
      'vname' => 'LBL_CLUSTER_ID',
      'type' => 'id',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
    ),
    'template_id' => 
    array (
      'required' => false,
      'name' => 'template_id',
      'vname' => 'LBL_TEMPLATE_ID',
      'type' => 'id',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
    ),
    'type' => 
    array (
      'required' => false,
      'name' => 'type',
      'vname' => 'LBL_TYPE',
        'type' => 'enum',
        'options' => 'action_type_list',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => 100,
    ),
    'status' => 
    array (
      'required' => false,
      'name' => 'status',
      'vname' => 'LBL_STATUS',
      'type' => 'enum',
      'options' => 'action_status_list',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => 100,
    ),

    'priority' => 
    array (
      'required' => false,
      'name' => 'priority',
      'vname' => 'LBL_PRIORITY',
        'type' => 'enum',
        'options' => 'action_priority_list',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => 100,
    ),    
    'logs' => 
    array (
      'required' => false,
      'name' => 'logs',
      'vname' => 'LBL_LOGS',
      'type' => 'text',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
    ),
    'client_name' => 
    array (
      'required' => false,
      'name' => 'client_name',
      'vname' => 'LBL_CLIENT_NAME',
      'type' => 'varchar',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'len' => '255',
    ),
    'cleanup_parms' => 
    array (
        'required' => false,
        'name' => 'cleanup_parms',
        'vname' => 'LBL_CLEANUP_PARMS',
        'type' => 'text',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
     ),    
    'action_parms' => 
    array (
        'required' => false,
        'name' => 'action_parms',
        'vname' => 'LBL_ACTION_PARMS',
        'type' => 'text',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
     ),    

      'start_date' =>
      array (
        'name' => 'start_date',
        'vname' => 'LBL_START_DATE',
        'type' => 'datetime',
        'comment' => 'date action should start after'
      ),
      
      'date_started' =>
      array (
        'name' => 'date_started',
        'vname' => 'LBL_START_DATE',
        'type' => 'datetime',
        'comment' => 'time action was started'
      ),
      
      'date_completed' =>
      array (
        'name' => 'date_completed',
        'vname' => 'LBL_DATE_COMPLETED',
        'type' => 'datetime',
        'comment' => 'time action cleanup completed'
      ),
      

      'cluster_name' => 
      array (
        'required' => '0',
        'source' => 'non-db',
        'name' => 'cluster_name',
        'rname' => 'name',
        'vname' => 'LBL_CLUSTER_NAME',
        'link'=>'cluster_link',
        'type' => 'relate',
        'massupdate' => 0,
        'module' => 'DCEClusters',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'cluster_id',
      ),
    
    
      'template_name' => 
      array (
        'required' => '0',
        'source' => 'non-db',
        'name' => 'template_name',
        'rname' => 'name',
        'vname' => 'LBL_TEMPLATE_NAME',
        'link'=>'template_link',
        'type' => 'relate',
        'massupdate' => 0,
        'module' => 'DCETemplates',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'template_id',
      ),
    
          
      'instance_name' => 
      array (
        'required' => '0',
        'source' => 'non-db',
        'name' => 'instance_name',
        'rname' => 'name',
        'vname' => 'LBL_INSTANCE_NAME',
        'link'=>'instance_link',
        'type' => 'relate',
        'massupdate' => 0,
        'module' => 'DCEInstances',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => '255',
        'id_name' => 'instance_id',
  ),      

        /////////////////RELATIONSHIP LINKS////////////////////////////
      'created_by_link' =>
      array (
        'name' => 'created_by_link',
        'type' => 'link',
        'relationship' => 'dceactions_created_by',
        'vname' => 'LBL_CREATED_USER',
        'link_type' => 'one',
        'module'=>'Users',
        'bean_name'=>'User',
        'source'=>'non-db',
      ),
      
      'instance_link' =>
      array (
        'name' => 'instance_link',
        'type' => 'link',
        'relationship' => 'DCEInstances_DCEActions',
        'vname' => 'LBL_INSTANCE_NAME',
        'link_type' => 'one-to-many',
        'module'=>'DCEInstances',
        'bean_name'=>'Instance',
        'source'=>'non-db',
      ),
      
      'template_link' =>
      array (
        'name' => 'template_link',
        'type' => 'link',
        'relationship' => 'DCETemplates_DCEActions',
        'vname' => 'LBL_TEMPLATE_NAME',
        'link_type' => 'one-to-many',
        'module'=>'DCETemplates',
        'bean_name'=>'Template',
        'source'=>'non-db',
      ),
      
      'cluster_link' =>
      array (
        'name' => 'cluster_link',
        'type' => 'link',
        'relationship' => 'DCEClusters_DCEActions',
        'vname' => 'LBL_CLUSTER_NAME',
        'link_type' => 'one-to-many',
        'module'=>'DCEClusters',
        'bean_name'=>'DCECluster',
        'source'=>'non-db',
      ),            
  ),
      
  'indices' => array (
       'id'=>array('name' =>'dceactionspk', 'type' =>'primary', 'fields'=>array('id'))
       ),

  'relationships' => 
  array (
    'dceactions_created_by' =>
        array(
            'lhs_module'=> 'Users', 
            'lhs_table'=> 'users', 
            'lhs_key' => 'id',
            'rhs_module'=> 'DCEActions', 
            'rhs_table'=> 'dceactions', 
            'rhs_key' => 'created_by',
            'relationship_type'=>'one-to-many'
        ),
  ),
);

VardefManager::createVardef('DCEActions','DCEAction', array('basic','team_security'));
?>
