<?php
// BEGIN sadek - SIMILAR OPPORTUNITIES SUBPANEL
$dictionary["related_opportunities"] = array (
  'true_relationship_type' => 'many-to-many',
  'relationships' => 
  array (
    'related_opportunities' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'Opportunities',
      'rhs_table' => 'opportunities',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'related_opportunities',
      'join_key_lhs' => 'opp_id_a',
      'join_key_rhs' => 'opp_id_b',
    ),
  ),
  'table' => 'related_opportunities',
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
      'name' => 'opp_id_a',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'opp_id_b',
      'type' => 'varchar',
      'len' => 36,
    ),
    5 => 
    array (
      'name' => 'score',
      'type' => 'int',
      'len' => 3,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'opportunitiespk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_opp_id_a',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opp_id_a',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_opp_id_b',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opp_id_b',
      ),
    ),
    3 => 
    array (
      'name' => 'idx_score',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'score',
      ),
    ),
  ),
);
?>
