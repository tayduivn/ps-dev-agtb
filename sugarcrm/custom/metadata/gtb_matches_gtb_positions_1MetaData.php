<?php
// created: 2020-11-20 13:25:14
$dictionary["gtb_matches_gtb_positions_1"] = array (
  'true_relationship_type' => 'many-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'gtb_matches_gtb_positions_1' => 
    array (
      'lhs_module' => 'gtb_matches',
      'lhs_table' => 'gtb_matches',
      'lhs_key' => 'id',
      'rhs_module' => 'gtb_positions',
      'rhs_table' => 'gtb_positions',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'gtb_matches_gtb_positions_1_c',
      'join_key_lhs' => 'gtb_matches_gtb_positions_1gtb_matches_ida',
      'join_key_rhs' => 'gtb_matches_gtb_positions_1gtb_positions_idb',
    ),
  ),
  'table' => 'gtb_matches_gtb_positions_1_c',
  'fields' => 
  array (
    'id' => 
    array (
      'name' => 'id',
      'type' => 'id',
    ),
    'date_modified' => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    'deleted' => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'default' => 0,
    ),
    'gtb_matches_gtb_positions_1gtb_matches_ida' => 
    array (
      'name' => 'gtb_matches_gtb_positions_1gtb_matches_ida',
      'type' => 'id',
    ),
    'gtb_matches_gtb_positions_1gtb_positions_idb' => 
    array (
      'name' => 'gtb_matches_gtb_positions_1gtb_positions_idb',
      'type' => 'id',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'idx_gtb_matches_gtb_positions_1_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_gtb_matches_gtb_positions_1_ida1_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_matches_gtb_positions_1gtb_matches_ida',
        1 => 'deleted',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_gtb_matches_gtb_positions_1_idb2_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_matches_gtb_positions_1gtb_positions_idb',
        1 => 'deleted',
      ),
    ),
    3 => 
    array (
      'name' => 'gtb_matches_gtb_positions_1_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'gtb_matches_gtb_positions_1gtb_matches_ida',
        1 => 'gtb_matches_gtb_positions_1gtb_positions_idb',
      ),
    ),
  ),
);