<?php
// created: 2010-07-21 13:42:19
$dictionary["cr_customer_reference_contacts"] = array (
  'true_relationship_type' => 'one-to-one',
  'relationships' => 
  array (
    'cr_customer_reference_contacts' => 
    array (
      'lhs_module' => 'CR_Customer_Reference',
      'lhs_table' => 'cr_customer_reference',
      'lhs_key' => 'id',
      'rhs_module' => 'Contacts',
      'rhs_table' => 'contacts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'cr_customernce_contacts_c',
      'join_key_lhs' => 'cr_custome89f1ference_ida',
      'join_key_rhs' => 'cr_customebbd8ontacts_idb',
    ),
  ),
  'table' => 'cr_customernce_contacts_c',
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
      'name' => 'cr_custome89f1ference_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'cr_customebbd8ontacts_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'cr_customerrence_contactsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'cr_customerrence_contacts_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'cr_custome89f1ference_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'cr_customerrence_contacts_idb2',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'cr_customebbd8ontacts_idb',
      ),
    ),
  ),
);
?>
