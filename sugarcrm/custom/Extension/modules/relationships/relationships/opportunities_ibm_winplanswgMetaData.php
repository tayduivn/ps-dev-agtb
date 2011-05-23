<?php
// created: 2011-02-17 07:38:18
$dictionary["opportunities_ibm_winplanswg"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'opportunities_ibm_winplanswg' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'ibm_WinPlanSWG',
      'rhs_table' => 'ibm_winplanswg',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'opportunitim_winplanswg_c',
      'join_key_lhs' => 'opportunitb5cfunities_ida',
      'join_key_rhs' => 'opportunitec86planswg_idb',
    ),
  ),
  'table' => 'opportunitim_winplanswg_c',
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
      'name' => 'opportunitb5cfunities_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'opportunitec86planswg_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'opportunitiibm_winplanswgspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'opportunitiibm_winplanswg_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opportunitb5cfunities_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'opportunitiibm_winplanswg_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'opportunitec86planswg_idb',
      ),
    ),
  ),
);
?>
