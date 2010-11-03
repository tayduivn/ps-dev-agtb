<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['DiscountCodes'] = array(
	'table'=>'discountcodes',
	'audited'=>true,
	'fields'=>array (
  'code_type' => 
  array (
    'required' => false,
    'name' => 'code_type',
    'vname' => 'LBL_CODE_TYPE',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'Discount Code',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => 100,
    'size' => '20',
    'options' => 'code_type_list',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'discount_code' => 
  array (
    'required' => false,
    'name' => 'discount_code',
    'vname' => 'LBL_DISCOUNT_CODE',
    'type' => 'varchar',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '255',
    'size' => '20',
  ),
  'minimum_price' => 
  array (
    'required' => false,
    'name' => 'minimum_price',
    'vname' => 'LBL_MINIMUM_PRICE',
    'type' => 'decimal',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '18',
    'size' => '20',
    'precision' => '2',
  ),
  'discount' => 
  array (
    'required' => false,
    'name' => 'discount',
    'vname' => 'LBL_DISCOUNT',
    'type' => 'decimal',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '18',
    'size' => '20',
    'precision' => '2',
  ),
  'discount_type' => 
  array (
    'required' => false,
    'name' => 'discount_type',
    'vname' => 'LBL_DISCOUNT_TYPE',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'Percent',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => 100,
    'size' => '20',
    'options' => 'discount_type_list',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'number_of_allowed_uses' => 
  array (
    'required' => false,
    'name' => 'number_of_allowed_uses',
    'vname' => 'LBL_NUMBER_OF_ALLOWED_USES',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '255',
    'size' => '20',
    'disable_num_format' => '',
  ),
  'number_of_uses' => 
  array (
    'required' => false,
    'name' => 'number_of_uses',
    'vname' => 'LBL_NUMBER_OF_USES',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '255',
    'size' => '20',
    'disable_num_format' => '1',
  ),
  'expires_on' => 
  array (
    'required' => false,
    'name' => 'expires_on',
    'vname' => 'LBL_EXPIRES_ON',
    'type' => 'datetimecombo',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'size' => '20',
    'dbType' => 'datetime',
    'display_default' => 'now&12:00pm',
  ),
  'status' => 
  array (
    'required' => false,
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'Enabled',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => 100,
    'size' => '20',
    'options' => 'status_list',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'product_id_c' => 
  array (
    'required' => false,
    'name' => 'product_id_c',
    'vname' => '',
    'type' => 'id',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 0,
    'reportable' => 1,
    'len' => 36,
    'size' => '20',
  ),
  
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Sets the DiscountType.product to relate to ProductTemplates.  Have to do it this way because ProductTemplates doesn't show up in the relate dropdown
 */

 'product' => 
  array (
    'required' => false,
    'source' => 'non-db',
    'name' => 'product',
    'vname' => 'LBL_PRODUCT',
    'type' => 'relate',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '255',
    'size' => '20',
    'id_name' => 'product_id_c',
    'ext2' => 'ProductTemplates',
    'module' => 'ProductTemplates',
    'rname' => 'name',
    'quicksearch' => 'enabled',
    'studio' => 'visible',
  ),
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('DiscountCodes','DiscountCodes', array('basic','team_security','assignable'));
