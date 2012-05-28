<?php
//FILE SUGARCRM flav=ent ONLY
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
$dictionary['OpportunityLine'] = array('table' => 'opportunity_lines','audited'=>false,
		'comment' => 'The opportunity line item assoicated with the product',
'fields' => array (

'id' =>
array (
  'name' => 'id',
  'vname' => 'LBL_ID',
  'type' => 'id',
  'required' => true,
  'reportable'=>false,
  'comment' => 'Unique identifier'
),
'product_name' =>
array (
    'name' => 'product_name',
    'rname' => 'name',
    'id_name' => 'product_id',
    'vname' => 'LBL_PRODUCT_NAME',
    'join_name'=>'products',
    'type' => 'relate',
    'link' => 'products',
    'table' => 'products',
    'isnull' => 'true',
    'module' => 'Products',
    'dbType' => 'varchar',
    'len' => '255',
    'source' => 'non-db',
    'unified_search' => true,
),
'product_id' =>
array (
    'name' => 'product_id',
    'rname' => 'id',
    'id_name' => 'product_id',
    'vname' => 'LBL_PRODUCT_ID',
    'type' => 'relate',
    'table' => 'products',
    'isnull' => 'true',
    'module' => 'Products',
    'dbType' => 'id',
    'reportable'=>false,
    'source' => 'non-db',
    'massupdate' => false,
    'duplicate_merge'=> 'disabled',
    'hideacl'=>true,

),
'expert_id' =>
    array (
    'name' => 'expert_id',
    'vname' => 'LBL_EXPERT_ID',
    'type' => 'enum',
    'function' => 'get_expert_array',
    'dbType' => 'varchar',
),
'opportunity_id' =>
array (
  'name' => 'opportunity_id',
  'type' => 'id',
  'vname' => 'LBL_OPPORTUNITY_ID',
  'required'=>false,
  'reportable' => false,
  'comment' => 'The opportunity id for the line item entry'
),
'opportunities' =>
  array(
    'name' => 'opportunities',
    'type' => 'link',
    'relationship' => 'opportunity_lines_opportunities',
    'source'=>'non-db',
    'link_type'=>'one',
    'module'=>'Opportunities',
    'bean_name'=>'Opportunity',
    'vname'=>'LBL_OPPORTUNITIES',
  ),
'best_case' =>
array (
    'name' => 'best_case',
    'vname' => 'LBL_BEST_CASE',
    'dbType' => 'decimal',
    'type' => 'currency',
    'len' => '26,6',
),
'likely_case' =>
array (
    'name' => 'likely_case',
    'vname' => 'LBL_LIKELY_CASE',
    'dbType' => 'decimal',
    'type' => 'currency',
    'len' => '26,6',
),
'worst_case' =>
array (
    'name' => 'worst_case',
    'vname' => 'LBL_WORST_CASE',
    'dbType' => 'decimal',
    'type' => 'currency',
    'len' => '26,6',
),
'products' =>
array (
    'name' => 'products',
    'type' => 'link',
    'relationship' => 'opportunity_lines_products',
    'link_type' => 'one',
    'source' => 'non-db',
    'vname' => 'LBL_PRODUCT',
    'duplicate_merge'=> 'disabled',
),

),
    'relationships' => array (
        'opportunity_lines_products' =>
            array('lhs_module'=> 'Products', 'lhs_table'=> 'products', 'lhs_key' => 'id',
                'rhs_module'=> 'OpportunityLines', 'rhs_table'=> 'opportunity_lines', 'rhs_key' => 'product_id',
                'relationship_type'=>'one-to-many'),
        'opportunity_lines_opportunities' =>
            array('lhs_module'=> 'Opportunities', 'lhs_table'=> 'opportunities', 'lhs_key' => 'id',
                'rhs_module'=> 'OpportunityLines', 'rhs_table'=> 'opportunity_lines', 'rhs_key' => 'opportunity_id',
                'relationship_type'=>'one-to-many'),
)

);

VardefManager::createVardef('OpportunityLines','OpportunityLine',
       array('default',
       //BEGIN SUGARCRM flav=pro ONLY
       'team_security'
       //END SUGARCRM flav=pro ONLY
       )
);