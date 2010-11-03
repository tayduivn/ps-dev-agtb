<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-26 08:58:38
$dictionary["Contract"]["fields"]["orders_contracts"] = array (
  'name' => 'orders_contracts',
  'type' => 'link',
  'relationship' => 'orders_contracts',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_ORDERS_TITLE',
);
$dictionary["Contract"]["fields"]["orders_contracts_name"] = array (
  'name' => 'orders_contracts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_con055dsorders_ida',
  'link' => 'orders_contracts',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Contract"]["fields"]["orders_con055dsorders_ida"] = array (
  'name' => 'orders_con055dsorders_ida',
  'type' => 'link',
  'relationship' => 'orders_contracts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_CONTRACTS_TITLE',
);


// created: 2010-07-20 07:36:58
$dictionary["Contract"]["fields"]["producttemplates_contracts"] = array (
  'name' => 'producttemplates_contracts',
  'type' => 'link',
  'relationship' => 'producttemplates_contracts',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_PRODUCTTEMPLATES_TITLE',
);
$dictionary["Contract"]["fields"]["producttemplates_contracts_name"] = array (
  'name' => 'producttemplates_contracts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_PRODUCTTEMPLATES_TITLE',
  'save' => true,
  'id_name' => 'producttemf7aamplates_ida',
  'link' => 'producttemplates_contracts',
  'table' => 'product_templates',
  'module' => 'ProductTemplates',
  'rname' => 'name',
);
$dictionary["Contract"]["fields"]["producttemf7aamplates_ida"] = array (
  'name' => 'producttemf7aamplates_ida',
  'type' => 'link',
  'relationship' => 'producttemplates_contracts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_CONTRACTS_TITLE',
);


// created: 2010-07-21 07:19:23
$dictionary["Contract"]["fields"]["products_contracts"] = array (
  'name' => 'products_contracts',
  'type' => 'link',
  'relationship' => 'products_contracts',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTS_CONTRACTS_FROM_PRODUCTS_TITLE',
);
$dictionary["Contract"]["fields"]["products_contracts_name"] = array (
  'name' => 'products_contracts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTS_CONTRACTS_FROM_PRODUCTS_TITLE',
  'save' => true,
  'id_name' => 'products_cf11broducts_ida',
  'link' => 'products_contracts',
  'table' => 'products',
  'module' => 'Products',
  'rname' => 'name',
);
$dictionary["Contract"]["fields"]["products_cf11broducts_ida"] = array (
  'name' => 'products_cf11broducts_ida',
  'type' => 'link',
  'relationship' => 'products_contracts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_PRODUCTS_CONTRACTS_FROM_CONTRACTS_TITLE',
);

?>