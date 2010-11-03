<?php
// created: 2010-07-27 14:39:12
$dictionary["orders_products"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'orders_products' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Products',
      'rhs_table' => 'products',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'orders_products_c',
      'join_key_lhs' => 'orders_prob569sorders_ida',
      'join_key_rhs' => 'orders_pro2902roducts_idb',
    ),
  ),
  'table' => 'orders_products_c',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'id',
      'type' => 'varchar',
      'len' => 36,
    ),
    1 => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    2 => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'len' => '1',
      'default' => '0',
      'required' => true,
    ),
    3 => 
    array (
      'name' => 'orders_prob569sorders_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'orders_pro2902roducts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'orders_productsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'orders_products_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_prob569sorders_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'orders_products_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'orders_pro2902roducts_idb',
      ),
    ),
  ),
);
?>
