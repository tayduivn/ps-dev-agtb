<?php

// BEGIN sadek - SIMILAR OPPORTUNITIES SUBPANEL
$dictionary["Meeting"]["fields"]["meetings_tasks"] = array (
  'name' => 'meetings_tasks',
  'type' => 'link',
  'relationship' => 'meetings_tasks',
  'source' => 'non-db',
  'vname' => 'LBL_RELATE_TASKS_TITLE',
);

$dictionary["Meeting"]['relationships']['meetings_tasks'] = array(
  'lhs_module' => 'Meetings',
  'lhs_table' => 'meetings',
  'lhs_key' => 'id',
  'rhs_module' => 'Tasks',
  'rhs_table' => 'tasks',
  'rhs_key' => 'parent_id',
  'relationship_type' => 'one-to-many',
  'relationship_role_column' => 'parent_type',
  'relationship_role_column_value' => 'Meetings',
);

