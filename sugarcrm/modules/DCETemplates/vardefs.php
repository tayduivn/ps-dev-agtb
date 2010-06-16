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
$dictionary['DCETemplate'] = array(
	'table'=>'dcetemplates',
	'audited'=>true,
	'fields'=>array (
    'status' => 
    array (
        'required' => '1',
        'name' => 'status',
        'vname' => 'LBL_STATUS',
        'type' => 'enum',
        'massupdate' => 0,
        'default' => 'active',
        'comments' => 'Status',
        'help' => 'Status',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
        'len' => 100,
        'options' => 'status_list',
        'studio' => 'visible',
    ),
    'sugar_version' => 
    array (
        'required' => false,
        'name' => 'sugar_version',
        'vname' => 'LBL_SUGAR_VERSION',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'Sugar Version',
        'help' => 'Sugar Version',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 255,
        'studio' => 'visible',
    ),
    'sugar_edition' => 
    array (
        'required' => false,
        'name' => 'sugar_edition',
        'vname' => 'LBL_SUGAR_EDITION',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 255,
        'studio' => 'visible',
    ),
    'upgrade_acceptable_edition' => 
    array (
        'required' => false,
        'name' => 'upgrade_acceptable_edition',
        'vname' => 'LBL_UPGRADE_ACCEPTABLE_EDITION',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'show the editions that this template can accept for an upgarde (separator is |)',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
        'len' => 255,
        'studio' => 'visible',
    ),
    'upgrade_acceptable_version' => 
    array (
        'required' => false,
        'name' => 'upgrade_acceptable_version',
        'vname' => 'LBL_UPGRADE_ACCEPTABLE_VERSION',
        'type' => 'varchar',
        'massupdate' => 0,
        'comments' => 'show the versions that this template can accept for an upgarde (separator is |)',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 1,
        'reportable' => 1,
        'len' => 255,
        'studio' => 'visible',
    ),
    'template_name' => 
    array (
        'required' => true,
        'name' => 'template_name',
        'vname' => 'LBL_TEMPLATE_NAME',
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
    'zip_name' => 
    array (
        'required' => false,
        'name' => 'zip_name',
        'vname' => 'LBL_ZIP_NAME',
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
    'DCEActions' =>
    array (
        'name' => 'DCEActions',
        'type' => 'link',
        'relationship' => 'DCETemplates_DCEActions',
        'module'=>'DCEActions',
        'bean_name'=>'DCEAction',
        'source'=>'non-db',
    ),
    'DCEInstances' =>
    array (
        'name' => 'DCEInstances',
        'type' => 'link',
        'relationship' => 'DCETemplates_DCEInstances',
        'module'=>'DCEInstances',
        'bean_name'=>'DCEInstance',
        'source'=>'non-db',
    ),
    'convert_status' => 
    array (
        'required' => '1',
        'name' => 'convert_status',
        'vname' => 'LBL_CONVERTED_STATUS',
        'type' => 'enum',
        'massupdate' => 0,
        'default' => 'no',
        'comments' => 'Displays status of whether template has been converted or not.',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'len' => 100,
        'options' => 'convert_status_list',
        'studio' => 'visible',
    ),    

    'copy_template' => 
    array (
        'required' => false,
        'name' => 'copy_template',
        'vname' => 'LBL_COPY_TEMPLATE',
        'type' => 'bool',
        'comments'=> 'Used to designate Template as a copy template',
        'massupdate' => 0,
        'comments' => '',
        'help' => '',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => 0,
        'audited' => 0,
        'reportable' => 1,
        'disable_num_format' => '',
    ),    
    
    
),
'relationships'=>array (
    'DCETemplates_DCEInstances' => array(
        'lhs_module'        =>  'DCETemplates',
        'lhs_table'         =>  'dcetemplates',
        'lhs_key'           =>  'id',
        'rhs_module'        =>  'DCEInstances',
        'rhs_table'         =>  'dceinstances',
        'rhs_key'           =>  'dcetemplate_id',
        'relationship_type' =>'one-to-many'
    ),
    'DCETemplates_DCEActions' => array(
        'lhs_module'        =>  'DCETemplates',
        'lhs_table'         =>  'dcetemplates',
        'lhs_key'           =>  'id',
        'rhs_module'        =>  'DCEActions',
        'rhs_table'         =>  'dceactions',
        'rhs_key'           =>  'template_id',
        'relationship_type' =>'one-to-many'
    )
),

	'optimistic_lock'=>true,
);

VardefManager::createVardef('DCETemplates','DCETemplate', array('basic','team_security','assignable'));