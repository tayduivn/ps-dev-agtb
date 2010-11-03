<?php
// created: 2010-07-27 14:46:32
$dictionary["discountcodes_accounts"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'discountcodes_accounts' => 
    array (
      'lhs_module' => 'DiscountCodes',
      'lhs_table' => 'discountcodes',
      'lhs_key' => 'id',
      'rhs_module' => 'Accounts',
      'rhs_table' => 'accounts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'discountcodes_accounts_c',
      'join_key_lhs' => 'discountco4170ntcodes_ida',
      'join_key_rhs' => 'discountcoa817ccounts_idb',
    ),
  ),
  'table' => 'discountcodes_accounts_c',
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
      'name' => 'discountco4170ntcodes_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'discountcoa817ccounts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'discountcodes_accountsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'discountcodes_accounts_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'discountco4170ntcodes_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'discountcodes_accounts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'discountcoa817ccounts_idb',
      ),
    ),
  ),
);
?>
