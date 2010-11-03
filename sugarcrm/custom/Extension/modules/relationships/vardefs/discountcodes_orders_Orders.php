<?php
// created: 2010-07-27 14:42:23
$dictionary["Orders"]["fields"]["discountcodes_orders"] = array (
  'name' => 'discountcodes_orders',
  'type' => 'link',
  'relationship' => 'discountcodes_orders',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Orders"]["fields"]["discountcodes_orders_name"] = array (
  'name' => 'discountcodes_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco8a18ntcodes_ida',
  'link' => 'discountcodes_orders',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["discountco8a18ntcodes_ida"] = array (
  'name' => 'discountco8a18ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_ORDERS_TITLE',
);
