<?php

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 5
 * Dashlet Code for Sales Ops to see pending orders.  
 */

$dashletData['SalesOpPendingOrdersDashlet']['searchFields'] = array (
  'date_entered' => 
  array (
    'default' => '',
  ),
  'date_modified' => 
  array (
    'default' => '',
  ),
);
$dashletData['SalesOpPendingOrdersDashlet']['columns'] = array (
  'order_id' => 
  array (
    'type' => 'int',
    'label' => 'LBL_ORDER_ID',
    'width' => '10%',
    'default' => true,
    'link' => true,
  ),
  'total' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_TOTAL',
    'width' => '10%',
    'default' => true,
  ),
  'assigned_user_name' =>
  array (
    'type' => 'varchar',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'width' => '10%',
    'default' => true,
  ),
  'date_modified' =>
  array (
    'width' => '15%',
    'label' => 'LBL_DATE_MODIFIED',
    'name' => 'date_modified',
    'default' => true,
  ),
  'orders_opportunities_name' =>
  array(
	'width' => '15%',
	'label' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
	'name' => 'orders_opportunities_name',
	'default'=>true,
  ),
  'date_entered' => 
  array (
    'width' => '15%',
    'label' => 'LBL_DATE_ENTERED',
    'default' => false,
    'name' => 'date_entered',
  ),
  'accounts_orders_name' =>
  array(
        'width' => '15%',
        'label' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
        'name' => 'accounts_orders_name',
        'default'=>false,
  ),
  'contacts_orders_name' =>
  array(
        'width' => '15%',
        'label' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
        'name' => 'contacts_orders_name',
        'default'=>false,
  ),

);
