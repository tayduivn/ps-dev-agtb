<?php
// created: 2010-07-27 14:39:47
$dictionary["Opportunity"]["fields"]["orders_opportunities"] = array (
  'name' => 'orders_opportunities',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
);
$dictionary["Opportunity"]["fields"]["orders_opportunities_name"] = array (
  'name' => 'orders_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_opp69easorders_ida',
  'link' => 'orders_opportunities',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Opportunity"]["fields"]["orders_opp69easorders_ida"] = array (
  'name' => 'orders_opp69easorders_ida',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_ORDERS_TITLE',
);
