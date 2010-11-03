<?php
// created: 2010-07-26 08:58:38
$dictionary["orders_contracts"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'orders_contracts' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Contracts',
      'rhs_table' => 'contracts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'orders_contracts_c',
      'join_key_lhs' => 'orders_con055dsorders_ida',
      'join_key_rhs' => 'orders_cone780ntracts_idb',
    ),
  ),
  'table' => 'orders_contracts_c',
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
      'name' => 'orders_con055dsorders_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'orders_cone780ntracts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'orders_contractsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'orders_contracts_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_con055dsorders_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'orders_contracts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'orders_cone780ntracts_idb',
      ),
    ),
  ),
);
?>
