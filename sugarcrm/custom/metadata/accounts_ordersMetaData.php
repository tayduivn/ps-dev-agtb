<?php
// created: 2010-07-27 14:40:36
$dictionary["accounts_orders"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'accounts_orders' => 
    array (
      'lhs_module' => 'Accounts',
      'lhs_table' => 'accounts',
      'lhs_key' => 'id',
      'rhs_module' => 'Orders',
      'rhs_table' => 'orders',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'accounts_orders_c',
      'join_key_lhs' => 'accounts_od749ccounts_ida',
      'join_key_rhs' => 'accounts_o0f8dsorders_idb',
    ),
  ),
  'table' => 'accounts_orders_c',
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
      'name' => 'accounts_od749ccounts_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'accounts_o0f8dsorders_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'accounts_ordersspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'accounts_orders_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'accounts_od749ccounts_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'accounts_orders_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'accounts_o0f8dsorders_idb',
      ),
    ),
  ),
);
?>
