<?php 
 //WARNING: The contents of this file are auto-generated



//BEGIN PRODUCTS VARDEFS 
// adding project field
$dictionary['Product']['fields']['projects'] = array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_products',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END PRODUCTS VARDEFS


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 83
 * had to change the relationship for orders to use order_id instead of name
*/



// created: 2010-07-27 14:39:12
$dictionary["Product"]["fields"]["orders_products"] = array (
  'name' => 'orders_products',
  'type' => 'link',
  'relationship' => 'orders_products',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_PRODUCTS_FROM_ORDERS_TITLE',
);
$dictionary["Product"]["fields"]["orders_products_name"] = array (
  'name' => 'orders_products_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_PRODUCTS_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_prob569sorders_ida',
  'link' => 'orders_products',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'order_id',
);
$dictionary["Product"]["fields"]["orders_prob569sorders_ida"] = array (
  'name' => 'orders_prob569sorders_ida',
  'type' => 'link',
  'relationship' => 'orders_products',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ORDERS_PRODUCTS_FROM_PRODUCTS_TITLE',
);


// created: 2010-07-21 07:19:23
$dictionary["Product"]["fields"]["products_contracts"] = array (
  'name' => 'products_contracts',
  'type' => 'link',
  'relationship' => 'products_contracts',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_PRODUCTS_CONTRACTS_FROM_CONTRACTS_TITLE',
);

?>