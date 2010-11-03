<?php
// created: 2010-07-27 14:39:47
$dictionary["orders_opportunities"] = array (
  'true_relationship_type' => 'one-to-one',
  'from_studio' => true,
  'relationships' => 
  array (
    'orders_opportunities' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Opportunities',
      'rhs_table' => 'opportunities',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'orders_opportunities_c',
      'join_key_lhs' => 'orders_opp69easorders_ida',
      'join_key_rhs' => 'orders_opp02e0unities_idb',
    ),
  ),
  'table' => 'orders_opportunities_c',
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
      'name' => 'orders_opp69easorders_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'orders_opp02e0unities_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'orders_opportunitiesspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'orders_opportunities_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_opp69easorders_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'orders_opportunities_idb2',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_opp02e0unities_idb',
      ),
    ),
  ),
);
?>
