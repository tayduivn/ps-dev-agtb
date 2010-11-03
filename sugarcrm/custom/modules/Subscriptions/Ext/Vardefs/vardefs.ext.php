<?php 
 //WARNING: The contents of this file are auto-generated


 // created: 2010-11-02 19:31:06

 

// created: 2010-08-23 08:04:50
$dictionary["Subscription"]["fields"]["orders_subscriptions"] = array (
  'name' => 'orders_subscriptions',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_ORDERS_TITLE',
);
$dictionary["Subscription"]["fields"]["orders_subscriptions_name"] = array (
  'name' => 'orders_subscriptions_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_ORDERS_TITLE',
  'save' => true,
  'id_name' => 'orders_subef4esorders_ida',
  'link' => 'orders_subscriptions',
  'table' => 'orders',
  'module' => 'Orders',
  'rname' => 'name',
);
$dictionary["Subscription"]["fields"]["orders_subef4esorders_ida"] = array (
  'name' => 'orders_subef4esorders_ida',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_ORDERS_TITLE',
);

?>