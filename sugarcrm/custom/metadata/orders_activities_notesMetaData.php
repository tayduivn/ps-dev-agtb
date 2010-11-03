<?php
// created: 2010-07-27 14:43:43
$dictionary["orders_activities_notes"] = array (
  'relationships' => 
  array (
    'orders_activities_notes' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Notes',
      'rhs_table' => 'notes',
      'rhs_key' => 'parent_id',
      'relationship_type' => 'one-to-many',
      'relationship_role_column' => 'parent_type',
      'relationship_role_column_value' => 'Orders',
    ),
  ),
  'fields' => '',
  'indices' => '',
  'table' => '',
);
?>
