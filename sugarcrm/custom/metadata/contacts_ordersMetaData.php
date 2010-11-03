<?php
// created: 2010-07-27 14:41:48
$dictionary["contacts_orders"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'contacts_orders' => 
    array (
      'lhs_module' => 'Contacts',
      'lhs_table' => 'contacts',
      'lhs_key' => 'id',
      'rhs_module' => 'Orders',
      'rhs_table' => 'orders',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'contacts_orders_c',
      'join_key_lhs' => 'contacts_o7603ontacts_ida',
      'join_key_rhs' => 'contacts_o95f4sorders_idb',
    ),
  ),
  'table' => 'contacts_orders_c',
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
      'name' => 'contacts_o7603ontacts_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'contacts_o95f4sorders_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'contacts_ordersspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'contacts_orders_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'contacts_o7603ontacts_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'contacts_orders_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'contacts_o95f4sorders_idb',
      ),
    ),
  ),
);
?>
