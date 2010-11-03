<?php
// created: 2010-07-27 14:42:52
$dictionary["orders_documents"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'orders_documents' => 
    array (
      'lhs_module' => 'Orders',
      'lhs_table' => 'orders',
      'lhs_key' => 'id',
      'rhs_module' => 'Documents',
      'rhs_table' => 'documents',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'orders_documents_c',
      'join_key_lhs' => 'orders_docd099sorders_ida',
      'join_key_rhs' => 'orders_doc3babcuments_idb',
    ),
  ),
  'table' => 'orders_documents_c',
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
      'name' => 'orders_docd099sorders_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'orders_doc3babcuments_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
    5 => 
    array (
      'name' => 'document_revision_id',
      'type' => 'varchar',
      'len' => '36',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'orders_documentsspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'orders_documents_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'orders_docd099sorders_ida',
      ),
    ),
    2 => 
    array (
      'name' => 'orders_documents_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'orders_doc3babcuments_idb',
      ),
    ),
  ),
);
?>
