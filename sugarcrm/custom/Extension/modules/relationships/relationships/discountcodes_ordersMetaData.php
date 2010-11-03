<?php
// created: 2010-07-27 14:42:23
$dictionary["discountcodes_orders"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'discountcodes_orders' => 
    array (
      'lhs_module' => 'DiscountCodes',
      'lhs_table' => 'discountcodes',
      'lhs_key' => 'id',
      'rhs_module' => 'Orders',
      'rhs_table' => 'orders',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'discountcodes_orders_c',
      'join_key_lhs' => 'discountco8a18ntcodes_ida',
      'join_key_rhs' => 'discountcoe4b7sorders_idb',
    ),
  ),
  'table' => 'discountcodes_orders_c',
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
      'name' => 'discountco8a18ntcodes_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'discountcoe4b7sorders_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'discountcodes_ordersspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'discountcodes_orders_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'discountco8a18ntcodes_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'discountcodes_orders_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'discountcoe4b7sorders_idb',
      ),
    ),
  ),
);
?>
