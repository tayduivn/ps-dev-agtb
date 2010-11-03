<?php
// created: 2008-10-06 05:00:56
$dictionary["bugs_e1_escalations"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    'bugs_e1_escalations' => 
    array (
      'lhs_module' => 'Bugs',
      'lhs_table' => 'bugs',
      'lhs_key' => 'id',
      'rhs_module' => 'E1_Escalations',
      'rhs_table' => 'e1_escalations',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'bugs_e1_escalations_c',
      'join_key_lhs' => 'bugs_e1_escationsbugs_ida',
      'join_key_rhs' => 'bugs_e1_escscalations_idb',
    ),
  ),
  'table' => 'bugs_e1_escalations_c',
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
      'name' => 'bugs_e1_escationsbugs_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'bugs_e1_escscalations_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'bugs_e1_escalationsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'bugs_e1_escalations_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'bugs_e1_escationsbugs_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'bugs_e1_escalations_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'bugs_e1_escscalations_idb',
      ),
    ),
  ),
);
?>
