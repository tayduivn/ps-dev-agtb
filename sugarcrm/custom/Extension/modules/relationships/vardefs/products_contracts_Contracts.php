<?php
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
