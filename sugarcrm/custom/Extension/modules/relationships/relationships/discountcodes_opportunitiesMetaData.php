<?php
// created: 2010-07-27 14:46:59
$dictionary["discountcodes_opportunities"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'discountcodes_opportunities' => 
    array (
      'lhs_module' => 'DiscountCodes',
      'lhs_table' => 'discountcodes',
      'lhs_key' => 'id',
      'rhs_module' => 'Opportunities',
      'rhs_table' => 'opportunities',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'discountcodpportunities_c',
      'join_key_lhs' => 'discountco8282ntcodes_ida',
      'join_key_rhs' => 'discountco2aecunities_idb',
    ),
  ),
  'table' => 'discountcodpportunities_c',
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
      'name' => 'discountco8282ntcodes_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'discountco2aecunities_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'discountcod_opportunitiesspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'discountcod_opportunities_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'discountco8282ntcodes_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'discountcod_opportunities_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'discountco2aecunities_idb',
      ),
    ),
  ),
);
?>
