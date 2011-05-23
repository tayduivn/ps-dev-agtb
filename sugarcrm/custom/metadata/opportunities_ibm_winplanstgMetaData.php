<?php
// created: 2011-02-17 07:37:10
$dictionary["opportunities_ibm_winplanstg"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'opportunities_ibm_winplanstg' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'ibm_WinPlanSTG',
      'rhs_table' => 'ibm_winplanstg',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'opportunitim_winplanstg_c',
      'join_key_lhs' => 'opportunit8b0bunities_ida',
      'join_key_rhs' => 'opportunitdf18planstg_idb',
    ),
  ),
  'table' => 'opportunitim_winplanstg_c',
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
      'name' => 'opportunit8b0bunities_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'opportunitdf18planstg_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'opportunitiibm_winplanstgspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'opportunitiibm_winplanstg_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opportunit8b0bunities_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'opportunitiibm_winplanstg_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'opportunitdf18planstg_idb',
      ),
    ),
  ),
);
?>
