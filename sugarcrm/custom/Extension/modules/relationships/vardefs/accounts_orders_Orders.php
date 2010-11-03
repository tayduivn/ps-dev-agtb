<?php
// created: 2010-07-27 14:40:36
$dictionary["Orders"]["fields"]["accounts_orders"] = array (
  'name' => 'accounts_orders',
  'type' => 'link',
  'relationship' => 'accounts_orders',
  'source' => 'non-db',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
);
$dictionary["Orders"]["fields"]["accounts_orders_name"] = array (
  'name' => 'accounts_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
  'save' => true,
  'id_name' => 'accounts_od749ccounts_ida',
  'link' => 'accounts_orders',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["accounts_od749ccounts_ida"] = array (
  'name' => 'accounts_od749ccounts_ida',
  'type' => 'link',
  'relationship' => 'accounts_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ORDERS_TITLE',
);
