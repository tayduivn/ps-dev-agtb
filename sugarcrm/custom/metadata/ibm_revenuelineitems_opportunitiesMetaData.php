<?php
// created: 2011-02-09 23:01:45
$dictionary["ibm_revenuelineitems_opportunities"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    'ibm_revenuelineitems_opportunities' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'ibm_revenueLineItems',
      'rhs_table' => 'ibm_revenuelineitems',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'ibm_revenuepportunities_c',
      'join_key_lhs' => 'ibm_revenud375unities_ida',
      'join_key_rhs' => 'ibm_revenu04e3neitems_idb',
    ),
  ),
  'table' => 'ibm_revenuepportunities_c',
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
      'name' => 'ibm_revenud375unities_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'ibm_revenu04e3neitems_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'ibm_revenue_opportunitiesspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'ibm_revenue_opportunities_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'ibm_revenud375unities_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'ibm_revenue_opportunities_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'ibm_revenu04e3neitems_idb',
      ),
    ),
  ),
);
?>
