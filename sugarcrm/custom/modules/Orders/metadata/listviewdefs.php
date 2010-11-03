<?php
$module_name = 'Orders';
$listViewDefs [$module_name] = 
array (
  'ORDER_ID' => 
  array (
    'type' => 'int',
    'label' => 'LBL_ORDER_ID',
    'width' => '10%',
    'default' => true,
    'link' => true,
  ),
  'STATUS' => 
  array (
    'type' => 'enum',
    'default' => true,
    'studio' => 'visible',
    'label' => 'LBL_STATUS',
    'sortable' => false,
    'width' => '10%',
  ),
  'CONTACTS_ORDERS_NAME' => 
  array (
    'type' => 'relate',
    'link' => 'contacts_orders',
    'label' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
    'width' => '10%',
    'default' => true,
  ),
  'USERNAME' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_USERNAME',
    'width' => '10%',
    'default' => true,
  ),
  'PAYMENT_METHOD' => 
  array (
    'type' => 'enum',
    'default' => true,
    'studio' => 'visible',
    'label' => 'LBL_PAYMENT_METHOD',
    'sortable' => false,
    'width' => '10%',
  ),
  'DATE_ENTERED' => 
  array (
    'type' => 'datetime',
    'label' => 'LBL_DATE_ENTERED',
    'width' => '10%',
    'default' => true,
  ),
  'TOTAL' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_TOTAL',
    'width' => '10%',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'link' => 'assigned_user_link',
    'type' => 'relate',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'width' => '10%',
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_NAME',
    'default' => false,
    'link' => true,
  ),
  'SUBTOTAL' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_SUBTOTAL',
    'width' => '10%',
    'default' => false,
  ),
  'DISCOUNT' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_DISCOUNT',
    'width' => '10%',
    'default' => false,
  ),
  'TAX' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_TAX',
    'width' => '10%',
    'default' => false,
  ),
  'ACCOUNTS_ORDERS_NAME' => 
  array (
    'type' => 'relate',
    'link' => 'accounts_orders',
    'label' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
    'width' => '10%',
    'default' => false,
  ),
  'ORDERS_OPPORTUNITIES_NAME' => 
  array (
    'type' => 'relate',
    'link' => 'orders_opportunities',
    'label' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
    'width' => '10%',
    'default' => false,
  ),
  'DISCOUNTCODES_ORDERS_NAME' => 
  array (
    'type' => 'relate',
    'link' => 'discountcodes_orders',
    'label' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
    'width' => '10%',
    'default' => false,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_TEAM',
    'default' => false,
  ),
);
?>
