<?php
// created: 2009-06-08 17:15:59
$dictionary["bugs_bugs"] = array (
  'true_relationship_type' => 'many-to-many',
  'relationships' => 
  array (
    'bugs_bugs' => 
    array (
      'lhs_module' => 'Bugs',
      'lhs_table' => 'bugs',
      'lhs_key' => 'id',
      'rhs_module' => 'Bugs',
      'rhs_table' => 'bugs',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'bugs_bugs_c',
      'join_key_lhs' => 'bugs_bugsb73ffugsbugs_ida',
      'join_key_rhs' => 'bugs_bugsb65f4ugsbugs_idb',
    ),
  ),
  'table' => 'bugs_bugs_c',
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
      'name' => 'bugs_bugsb73ffugsbugs_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'bugs_bugsb65f4ugsbugs_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'bugs_bugsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'bugs_bugs_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'bugs_bugsb73ffugsbugs_ida',
        1 => 'bugs_bugsb65f4ugsbugs_idb',
      ),
    ),
  ),
);
?>
