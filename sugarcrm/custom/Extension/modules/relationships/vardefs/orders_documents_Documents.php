<?php
// created: 2010-07-27 14:42:52
$dictionary["Document"]["fields"]["orders_documents"] = array (
  'name' => 'orders_documents',
  'type' => 'link',
  'relationship' => 'orders_documents',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_DOCUMENTS_FROM_ORDERS_TITLE',
);
$dictionary["Document"]["fields"]["orders_documents_name"] = array (
  'name' => 'orders_documents_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_DOCUMENTS_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_docd099sorders_ida',
  'link' => 'orders_documents',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Document"]["fields"]["orders_docd099sorders_ida"] = array (
  'name' => 'orders_docd099sorders_ida',
  'type' => 'link',
  'relationship' => 'orders_documents',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ORDERS_DOCUMENTS_FROM_DOCUMENTS_TITLE',
);
