<?php
// created: 2010-07-02 19:32:01
$dictionary["ps_timesheets_tasks"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    'ps_timesheets_tasks' => 
    array (
      'lhs_module' => 'Tasks',
      'lhs_table' => 'tasks',
      'lhs_key' => 'id',
      'rhs_module' => 'ps_Timesheets',
      'rhs_table' => 'ps_timesheets',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'ps_timesheets_tasks_c',
      'join_key_lhs' => 'ps_timeshed71akstasks_ida',
      'join_key_rhs' => 'ps_timeshe2f4aesheets_idb',
    ),
  ),
  'table' => 'ps_timesheets_tasks_c',
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
      'name' => 'ps_timeshed71akstasks_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'ps_timeshe2f4aesheets_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'ps_timesheets_tasksspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'ps_timesheets_tasks_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'ps_timeshed71akstasks_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'ps_timesheets_tasks_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'ps_timeshe2f4aesheets_idb',
      ),
    ),
  ),
);
?>
