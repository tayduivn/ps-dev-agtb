<?php
// created: 2010-07-27 14:41:48
$dictionary["Orders"]["fields"]["contacts_orders"] = array (
  'name' => 'contacts_orders',
  'type' => 'link',
  'relationship' => 'contacts_orders',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
);
$dictionary["Orders"]["fields"]["contacts_orders_name"] = array (
  'name' => 'contacts_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'contacts_o7603ontacts_ida',
  'link' => 'contacts_orders',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["Orders"]["fields"]["contacts_o7603ontacts_ida"] = array (
  'name' => 'contacts_o7603ontacts_ida',
  'type' => 'link',
  'relationship' => 'contacts_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_ORDERS_TITLE',
);
