<?php
// created: 2020-11-20 13:15:24
$dictionary["gtb_matches_contacts_1"] = array (
  'true_relationship_type' => 'many-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'gtb_matches_contacts_1' => 
    array (
      'lhs_module' => 'gtb_matches',
      'lhs_table' => 'gtb_matches',
      'lhs_key' => 'id',
      'rhs_module' => 'Contacts',
      'rhs_table' => 'contacts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'gtb_matches_contacts_1_c',
      'join_key_lhs' => 'gtb_matches_contacts_1gtb_matches_ida',
      'join_key_rhs' => 'gtb_matches_contacts_1contacts_idb',
    ),
  ),
  'table' => 'gtb_matches_contacts_1_c',
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
    'gtb_matches_contacts_1gtb_matches_ida' => 
    array (
      'name' => 'gtb_matches_contacts_1gtb_matches_ida',
      'type' => 'id',
    ),
    'gtb_matches_contacts_1contacts_idb' => 
    array (
      'name' => 'gtb_matches_contacts_1contacts_idb',
      'type' => 'id',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'idx_gtb_matches_contacts_1_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_gtb_matches_contacts_1_ida1_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_matches_contacts_1gtb_matches_ida',
        1 => 'deleted',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_gtb_matches_contacts_1_idb2_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_matches_contacts_1contacts_idb',
        1 => 'deleted',
      ),
    ),
    3 => 
    array (
      'name' => 'gtb_matches_contacts_1_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'gtb_matches_contacts_1gtb_matches_ida',
        1 => 'gtb_matches_contacts_1contacts_idb',
      ),
    ),
  ),
);