<?php
// created: 2010-07-26 08:58:38
$dictionary["Contract"]["fields"]["orders_contracts"] = array (
  'name' => 'orders_contracts',
  'type' => 'link',
  'relationship' => 'orders_contracts',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_ORDERS_TITLE',
);
$dictionary["Contract"]["fields"]["orders_contracts_name"] = array (
  'name' => 'orders_contracts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_con055dsorders_ida',
  'link' => 'orders_contracts',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Contract"]["fields"]["orders_con055dsorders_ida"] = array (
  'name' => 'orders_con055dsorders_ida',
  'type' => 'link',
  'relationship' => 'orders_contracts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_CONTRACTS_TITLE',
);
