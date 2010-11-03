<?php
// created: 2010-07-27 10:20:48
$dictionary["sales_seticket_opportunities"] = array (
  'true_relationship_type' => 'many-to-many',
  'relationships' => 
  array (
    'sales_seticket_opportunities' => 
    array (
      'lhs_module' => 'sales_SETicket',
      'lhs_table' => 'sales_seticket',
      'lhs_key' => 'id',
      'rhs_module' => 'Opportunities',
      'rhs_table' => 'opportunities',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'sales_seticpportunities_c',
      'join_key_lhs' => 'sales_seti427aeticket_ida',
      'join_key_rhs' => 'sales_seti39b9unities_idb',
    ),
  ),
  'table' => 'sales_seticpportunities_c',
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
      'name' => 'sales_seti427aeticket_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'sales_seti39b9unities_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'sales_setic_opportunitiesspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'sales_setic_opportunities_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'sales_seti427aeticket_ida',
        1 => 'sales_seti39b9unities_idb',
      ),
    ),
  ),
);
?>
