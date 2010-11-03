<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-27 14:42:23
$dictionary["DiscountCodes"]["fields"]["discountcodes_orders"] = array (
  'name' => 'discountcodes_orders',
  'type' => 'link',
  'relationship' => 'discountcodes_orders',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_ORDERS_TITLE',
);


// created: 2010-07-27 14:46:32
$dictionary["DiscountCodes"]["fields"]["discountcodes_accounts"] = array (
  'name' => 'discountcodes_accounts',
  'type' => 'link',
  'relationship' => 'discountcodes_accounts',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_ACCOUNTS_TITLE',
);


 // created: 2010-07-20 17:01:09
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 88
 * Had to add this so that the product templates would relate to the new discount when field
*/

$dictionary['DiscountCodes']['fields']['discount_when_product_templ_c']['id_name']='discount_when_product_templ_id_c';
$dictionary['DiscountCodes']['fields']['discount_when_product_templ_c']['ext2']='ProductTemplates';
$dictionary['DiscountCodes']['fields']['discount_when_product_templ_c']['module']='ProductTemplates';

 


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Shows the approval_type_c dropdown if the code_type dropdown is set to Approval Code
 */


//$dictionary['DiscountCodes']['fields']['minimum_price']['dependency'] = 'equal($applies_when_c, "minimum_price")';





 // created: 2010-07-26 17:19:02
$dictionary['DiscountCodes']['fields']['discount']['required']=true;

 

// created: 2010-07-27 14:46:59
$dictionary["DiscountCodes"]["fields"]["discountcodes_opportunities"] = array (
  'name' => 'discountcodes_opportunities',
  'type' => 'link',
  'relationship' => 'discountcodes_opportunities',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);


 // created: 2010-07-20 17:01:09
$dictionary['DiscountCodes']['fields']['code_type']['default']='discount_code';
$dictionary['DiscountCodes']['fields']['code_type']['duplicate_merge']='0';
$dictionary['DiscountCodes']['fields']['code_type']['default_value']='discount_code';

 

 // created: 2010-07-21 10:01:39
$dictionary['DiscountCodes']['fields']['expires_on']['default_value']='+1 year&12:00pm';
$dictionary['DiscountCodes']['fields']['expires_on']['default']='+1 year&12:00pm';
$dictionary['DiscountCodes']['fields']['expires_on']['duplicate_merge']='0';

 

 // created: 2010-07-21 09:45:25
$dictionary['DiscountCodes']['fields']['number_of_uses']['duplicate_merge']='0';

 

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Shows the approval_type_c dropdown if the code_type dropdown is set to Approval Code
 */


$dictionary['DiscountCodes']['fields']['approval_type_c']['dependency'] = 'equal($code_type, "approval_code")';





/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Shows the approval_type_c dropdown if the code_type dropdown is set to Approval Code
 */


//$dictionary['DiscountCodes']['fields']['product']['dependency'] = 'equal($applies_when_c, "specific_product")';







 // created: 2010-07-26 17:18:49
$dictionary['DiscountCodes']['fields']['discount_code']['required']=true;

 

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 88
 * Had to add this so that the product templates would relate to the new discount when field
*/

$dictionary['DiscountCodes']['fields']['discount_when_product_templ_id_c'] = array (
    'required' => false,
    'name' => 'discount_when_product_templ_id_c',
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
  );


?>