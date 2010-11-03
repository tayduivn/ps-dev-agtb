<?php
// created: 2010-08-23 08:04:50
$dictionary["Orders"]["fields"]["orders_subscriptions"] = array (
  'name' => 'orders_subscriptions',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
);
$dictionary["Orders"]["fields"]["orders_subscriptions_name"] = array (
  'name' => 'orders_subscriptions_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
  'save' => true,
  'id_name' => 'orders_subb9eaiptions_idb',
  'link' => 'orders_subscriptions',
  'table' => 'subscriptions',
  'module' => 'Subscriptions',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["orders_subb9eaiptions_idb"] = array (
  'name' => 'orders_subb9eaiptions_idb',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
);
