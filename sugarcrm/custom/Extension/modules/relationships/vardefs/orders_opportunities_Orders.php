<?php
// created: 2010-07-27 14:39:47
$dictionary["Orders"]["fields"]["orders_opportunities"] = array (
  'name' => 'orders_opportunities',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["Orders"]["fields"]["orders_opportunities_name"] = array (
  'name' => 'orders_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'orders_opp02e0unities_idb',
  'link' => 'orders_opportunities',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["orders_opp02e0unities_idb"] = array (
  'name' => 'orders_opp02e0unities_idb',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);
