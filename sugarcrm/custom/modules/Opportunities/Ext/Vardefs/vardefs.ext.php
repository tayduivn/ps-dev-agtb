<?php 
 //WARNING: The contents of this file are auto-generated


/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #14114:
** Description: keeping references to leadcontacts and leadaccounts instead of leads.  References
** is also kept in relationships
*/  
if (isset($dictionary['Opportunity']['fields']) && isset($dictionary['Opportunity']['fields']['leads'])) 
unset($dictionary['Opportunity']['fields']['leads']);

$dictionary['Opportunity']['fields']['leadaccounts'] =
  array (
  	'name' => 'leadaccounts',
	'type' => 'link',
    	'relationship' => 'opportunity_leadaccounts',
    	'source'=>'non-db',
	'vname'=>'LBL_LEADACCOUNTS',
  );
$dictionary['Opportunity']['fields']['leadcontacts'] =
  array (
  	'name' => 'leadcontacts',
	'type' => 'link',
    	'relationship' => 'opportunity_leadcontacts',
    	'source'=>'non-db',
	'vname'=>'LBL_LEADCONTACTS',
  );

if(isset($dictionary['Opportunity']['relationships']) && isset($dictionary['Opportunity']['relationships']['opportunity_leads'])) unset($dictionary['Opportunity']['relationships']['opportunity_leads']);
$dictionary['Opportunity']['relationships']['opportunity_leadaccounts'] =
	 array('lhs_module'=> 'Opportunities', 'lhs_table'=> 'opportunities', 'lhs_key' => 'id',
		'rhs_module'=> 'LeadAccounts', 'rhs_table'=> 'leadaccounts', 'rhs_key' => 'opportunity_id',
		'relationship_type'=>'one-to-many');




//BEGIN DEE CUSTOMIZATION ITREQUEST 3659 09.08.2008
$dictionary['Opportunity']['fields']['next_step']['len'] = '255';
$dictionary['Opportunity']['fields']['next_step']['type'] = 'varchar';
//END DEE CUSTOMIZATION




// created: 2009-02-13 17:29:10
$dictionary["Opportunity"]["fields"]["opportunities_accounts"] = array (
  'name' => 'opportunities_accounts',
  'type' => 'link',
  'relationship' => 'opportunities_accounts',
  'source' => 'non-db',
);



// BEGIN jostrow MoofCart customization
// See ITRequest #9622

$dictionary['Opportunity']['fields']['discount_code_c'] = array(
	'name' => 'discount_code_c',
	'vname' => 'LBL_DISCOUNT_CODE',
	'type' => 'varchar',
	'dbType' => 'varchar',
	'len' => 100,
	'unified_search' => FALSE,
	'help' => 'This discount code will automatically be applied to a customer\'s shopping cart when they click a link from a Renewal Notice e-mail',
	'audited' => TRUE,
);

// END jostrow MoofCart customization



$dictionary['Opportunity']['fields']['name']['len'] = '255';


// created: 2010-07-27 10:20:48
$dictionary["Opportunity"]["fields"]["sales_seticket_opportunities"] = array (
  'name' => 'sales_seticket_opportunities',
  'type' => 'link',
  'relationship' => 'sales_seticket_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
);


 // created: 2010-10-17 18:41:48
$dictionary['Opportunity']['fields']['connect_sell_c']['enforced']='false';

 

 // created: 2010-10-25 20:37:53
$dictionary['Opportunity']['fields']['partner_name']['enforced']='false';

 

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 95
 * Change teh partner_assigned_to_c to be the id of a new relate field so we can continue to use the relations setup by the dropdown that it used to be
 */

$dictionary["Opportunity"]["fields"]["partner_assigned_to_c"] =
  array (
    'required' => false,
    'name' => 'partner_assigned_to_c',
    'vname' => 'LBL_PARTNER_ASSIGNED_TO_NEW',
    'type' => 'enum',
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



// created: 2010-07-27 14:46:59
$dictionary["Opportunity"]["fields"]["discountcodes_opportunities"] = array (
  'name' => 'discountcodes_opportunities',
  'type' => 'link',
  'relationship' => 'discountcodes_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Opportunity"]["fields"]["discountcodes_opportunities_name"] = array (
  'name' => 'discountcodes_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco8282ntcodes_ida',
  'link' => 'discountcodes_opportunities',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'name',
);
$dictionary["Opportunity"]["fields"]["discountco8282ntcodes_ida"] = array (
  'name' => 'discountco8282ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);


 // created: 2010-10-22 12:43:30

 

// created: 2010-07-27 14:39:47
$dictionary["Opportunity"]["fields"]["orders_opportunities"] = array (
  'name' => 'orders_opportunities',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
);
$dictionary["Opportunity"]["fields"]["orders_opportunities_name"] = array (
  'name' => 'orders_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_opp69easorders_ida',
  'link' => 'orders_opportunities',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Opportunity"]["fields"]["orders_opp69easorders_ida"] = array (
  'name' => 'orders_opp69easorders_ida',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
);


 // created: 2010-10-22 11:47:11
$dictionary['Opportunity']['fields']['additional_training_credits_c']['enforced']='false';

 
?>