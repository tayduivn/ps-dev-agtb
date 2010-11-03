<?php
// created: 2010-08-23 08:04:50
$dictionary["orders_subscriptions"] = array (
  'true_relationship_type' => 'one-to-one',
  'from_studio' => true,
  'relationships' => 
  array (
    'orders_subscriptions' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Subscriptions',
      'rhs_table' => 'subscriptions',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'orders_subscriptions_c',
      'join_key_lhs' => 'orders_subef4esorders_ida',
      'join_key_rhs' => 'orders_subb9eaiptions_idb',
    ),
  ),
  'table' => 'orders_subscriptions_c',
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
      'name' => 'orders_subef4esorders_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'orders_subb9eaiptions_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'orders_subscriptionsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'orders_subscriptions_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_subef4esorders_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'orders_subscriptions_idb2',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_subb9eaiptions_idb',
      ),
    ),
  ),
);
?>
