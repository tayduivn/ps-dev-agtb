<?php

// BEGIN sadek - SIMILAR OPPORTUNITIES SUBPANEL
$dictionary["Task"]["fields"]["tasks_tasks"] = array (
  'name' => 'tasks_tasks',
  'type' => 'link',
  'relationship' => 'tasks_tasks',
  'source' => 'non-db',
  'vname' => 'LBL_RELATE_TASKS_TITLE',
);

$dictionary["Task"]['relationships']['tasks_tasks'] = array(
  'lhs_module' => 'Tasks',
  'lhs_table' => 'tasks',
  'lhs_key' => 'id',
  'rhs_module' => 'Tasks',
  'rhs_table' => 'tasks',
  'rhs_key' => 'parent_id',
  'relationship_type' => 'one-to-many',
  'relationship_role_column' => 'parent_type',
  'relationship_role_column_value' => 'Tasks',
);
