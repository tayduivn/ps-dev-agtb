<?php
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
