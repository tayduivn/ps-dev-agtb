<?php
// created: 2010-07-27 10:20:48
$dictionary["sales_seticket_activities_notes"] = array (
  'relationships' => 
  array (
    'sales_seticket_activities_notes' => 
    array (
      'lhs_module' => 'sales_SETicket',
      'lhs_table' => 'sales_seticket',
      'lhs_key' => 'id',
      'rhs_module' => 'Notes',
      'rhs_table' => 'notes',
      'rhs_key' => 'parent_id',
      'relationship_type' => 'one-to-many',
      'relationship_role_column' => 'parent_type',
      'relationship_role_column_value' => 'sales_SETicket',
    ),
  ),
  'fields' => '',
  'indices' => '',
  'table' => '',
);
?>
