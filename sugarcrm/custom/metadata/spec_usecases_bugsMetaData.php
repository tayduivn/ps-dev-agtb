<?php
// created: 2009-11-18 15:33:24
$dictionary["spec_usecases_bugs"] = array (
  'true_relationship_type' => 'many-to-many',
  'relationships' => 
  array (
    'spec_usecases_bugs' => 
    array (
      'lhs_module' => 'Spec_UseCases',
      'lhs_table' => 'spec_usecases',
      'lhs_key' => 'id',
      'rhs_module' => 'Bugs',
      'rhs_table' => 'bugs',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'spec_usecases_bugs_c',
      'join_key_lhs' => 'spec_useca8f6csecases_ida',
      'join_key_rhs' => 'spec_useca18faugsbugs_idb',
    ),
  ),
  'table' => 'spec_usecases_bugs_c',
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
      'name' => 'spec_useca8f6csecases_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'spec_useca18faugsbugs_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'spec_usecases_bugsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'spec_usecases_bugs_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'spec_useca8f6csecases_ida',
        1 => 'spec_useca18faugsbugs_idb',
      ),
    ),
  ),
);
?>
