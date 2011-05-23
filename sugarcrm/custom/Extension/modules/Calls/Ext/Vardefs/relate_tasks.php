<?php

// BEGIN sadek - SIMILAR OPPORTUNITIES SUBPANEL
$dictionary["Call"]["fields"]["calls_tasks"] = array (
  'name' => 'calls_tasks',
  'type' => 'link',
  'relationship' => 'calls_tasks',
  'source' => 'non-db',
  'vname' => 'LBL_RELATE_TASKS_TITLE',
);

$dictionary["Call"]['relationships']['calls_tasks'] = array(
  'lhs_module' => 'Calls',
  'lhs_table' => 'calls',
  'lhs_key' => 'id',
  'rhs_module' => 'Tasks',
  'rhs_table' => 'tasks',
  'rhs_key' => 'parent_id',
  'relationship_type' => 'one-to-many',
  'relationship_role_column' => 'parent_type',
  'relationship_role_column_value' => 'Calls',
);

