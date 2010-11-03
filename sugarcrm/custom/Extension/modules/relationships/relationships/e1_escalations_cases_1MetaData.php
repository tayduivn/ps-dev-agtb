<?php
// created: 2010-10-11 16:24:26
$dictionary["e1_escalations_cases_1"] = array (
  'true_relationship_type' => 'many-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'e1_escalations_cases_1' => 
    array (
      'lhs_module' => 'E1_Escalations',
      'lhs_table' => 'e1_escalations',
      'lhs_key' => 'id',
      'rhs_module' => 'Cases',
      'rhs_table' => 'cases',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'e1_escalations_cases_1_c',
      'join_key_lhs' => 'e1_escalat96eelations_ida',
      'join_key_rhs' => 'e1_escalatae9a_1cases_idb',
    ),
  ),
  'table' => 'e1_escalations_cases_1_c',
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
      'name' => 'e1_escalat96eelations_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'e1_escalatae9a_1cases_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'e1_escalations_cases_1spk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'e1_escalations_cases_1_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'e1_escalat96eelations_ida',
        1 => 'e1_escalatae9a_1cases_idb',
      ),
    ),
  ),
);
?>
