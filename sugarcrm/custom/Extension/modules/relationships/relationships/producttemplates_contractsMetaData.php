<?php
// created: 2010-07-20 07:36:58
$dictionary["producttemplates_contracts"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'producttemplates_contracts' => 
    array (
      'lhs_module' => 'ProductTemplates',
      'lhs_table' => 'product_templates',
      'lhs_key' => 'id',
      'rhs_module' => 'Contracts',
      'rhs_table' => 'contracts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'producttempes_contracts_c',
      'join_key_lhs' => 'producttemf7aamplates_ida',
      'join_key_rhs' => 'productteme217ntracts_idb',
    ),
  ),
  'table' => 'producttempes_contracts_c',
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
      'name' => 'producttemf7aamplates_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'productteme217ntracts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'producttempates_contractsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'producttempates_contracts_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'producttemf7aamplates_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'producttempates_contracts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'productteme217ntracts_idb',
      ),
    ),
  ),
);
?>
