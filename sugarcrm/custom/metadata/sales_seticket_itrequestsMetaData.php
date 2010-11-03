<?php
// created: 2010-09-16 06:40:08
$dictionary["sales_seticket_itrequests"] = array (
  'true_relationship_type' => 'many-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'sales_seticket_itrequests' => 
    array (
      'lhs_module' => 'sales_SETicket',
      'lhs_table' => 'sales_seticket',
      'lhs_key' => 'id',
      'rhs_module' => 'ITRequests',
      'rhs_table' => 'itrequests',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'sales_setict_itrequests_c',
      'join_key_lhs' => 'sales_setie72feticket_ida',
      'join_key_rhs' => 'sales_seticb2eequests_idb',
    ),
  ),
  'table' => 'sales_setict_itrequests_c',
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
      'name' => 'sales_setie72feticket_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'sales_seticb2eequests_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'sales_seticket_itrequestsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'sales_seticket_itrequests_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'sales_setie72feticket_ida',
        1 => 'sales_seticb2eequests_idb',
      ),
    ),
  ),
);
?>
