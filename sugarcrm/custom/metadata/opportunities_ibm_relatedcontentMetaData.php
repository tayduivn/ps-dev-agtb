<?php
// created: 2011-02-24 14:47:10
$dictionary["opportunities_ibm_relatedcontent"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'opportunities_ibm_relatedcontent' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'ibm_RelatedContent',
      'rhs_table' => 'ibm_relatedcontent',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'opportunitilatedcontent_c',
      'join_key_lhs' => 'opportunit7d96unities_ida',
      'join_key_rhs' => 'opportunit6d7acontent_idb',
    ),
  ),
  'table' => 'opportunitilatedcontent_c',
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
      'name' => 'opportunit7d96unities_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'opportunit6d7acontent_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'opportunitirelatedcontentspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'opportunitirelatedcontent_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opportunit7d96unities_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'opportunitirelatedcontent_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'opportunit6d7acontent_idb',
      ),
    ),
  ),
);
?>
