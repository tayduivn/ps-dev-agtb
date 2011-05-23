<?php
// created: 2011-02-17 06:11:18
$dictionary["opportunities_ibm_winplangeneric"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'opportunities_ibm_winplangeneric' => 
    array (
      'lhs_module' => 'Opportunities',
      'lhs_table' => 'opportunities',
      'lhs_key' => 'id',
      'rhs_module' => 'ibm_WinPlanGeneric',
      'rhs_table' => 'ibm_winplangeneric',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'opportunitinplangeneric_c',
      'join_key_lhs' => 'opportuniteefdunities_ida',
      'join_key_rhs' => 'opportunit1890generic_idb',
    ),
  ),
  'table' => 'opportunitinplangeneric_c',
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
      'name' => 'opportuniteefdunities_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'opportunit1890generic_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'opportunitiwinplangenericspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'opportunitiwinplangeneric_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opportuniteefdunities_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'opportunitiwinplangeneric_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'opportunit1890generic_idb',
      ),
    ),
  ),
);
?>
