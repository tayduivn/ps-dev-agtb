<?php
$module_name = 'Orders';
$searchdefs [$module_name] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'order_id' => 
      array (
        'type' => 'int',
        'label' => 'LBL_NUMBER',
        'width' => '10%',
        'default' => true,
        'name' => 'order_id',
      ),
      'username' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_USERNAME',
        'width' => '10%',
        'default' => true,
        'name' => 'username',
      ),
      'current_user_only' => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
      ),
    ),
    'advanced_search' => 
    array (
      'order_id' => 
      array (
        'type' => 'int',
        'label' => 'LBL_NUMBER',
        'width' => '10%',
        'default' => true,
        'name' => 'order_id',
      ),
      'username' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_USERNAME',
        'width' => '10%',
        'default' => true,
        'name' => 'username',
      ),
      'current_user_only' => 
      array (
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
        'name' => 'current_user_only',
      ),
      'status' => 
      array (
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_STATUS',
        'sortable' => false,
        'width' => '10%',
        'name' => 'status',
      ),
      'payment_method' => 
      array (
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_PAYMENT_METHOD',
        'sortable' => false,
        'width' => '10%',
        'name' => 'payment_method',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'label' => 'LBL_ASSIGNED_TO',
        'type' => 'enum',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
        'default' => true,
        'width' => '10%',
      ),
      'contacts_orders_name' => 
      array (
        'type' => 'relate',
        'link' => 'contacts_orders',
        'label' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
        'width' => '10%',
        'default' => true,
        'name' => 'contacts_orders_name',
      ),
      'orders_opportunities_name' => 
      array (
        'type' => 'relate',
        'link' => 'orders_opportunities',
        'label' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
        'width' => '10%',
        'default' => true,
        'name' => 'orders_opportunities_name',
      ),
      'billing_county' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_BILLING_COUNTY',
        'width' => '10%',
        'default' => true,
        'name' => 'billing_county',
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
