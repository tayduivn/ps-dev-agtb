<?php
// created: 2010-07-21 07:19:23
$dictionary["products_contracts"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'products_contracts' => 
    array (
      'lhs_module' => 'Products',
      'lhs_table' => 'products',
      'lhs_key' => 'id',
      'rhs_module' => 'Contracts',
      'rhs_table' => 'contracts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'products_contracts_c',
      'join_key_lhs' => 'products_cf11broducts_ida',
      'join_key_rhs' => 'products_c25e9ntracts_idb',
    ),
  ),
  'table' => 'products_contracts_c',
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
      'name' => 'products_cf11broducts_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'products_c25e9ntracts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'products_contractsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'products_contracts_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'products_cf11broducts_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'products_contracts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'products_c25e9ntracts_idb',
      ),
    ),
  ),
);
?>
