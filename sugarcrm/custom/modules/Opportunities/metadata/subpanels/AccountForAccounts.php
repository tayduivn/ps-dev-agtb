<?php
// created: 2009-08-03 15:26:47
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_LIST_OPPORTUNITY_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '50%',
    'default' => true,
  ),
  'sales_stage' => 
  array (
    'name' => 'sales_stage',
    'default' => true,
  ),
  'amount' => 
  array (
    'vname' => 'LBL_LIST_AMOUNT',
    'width' => '10%',
    'currency_format' => true,
    'default' => true,
  ),
  'date_closed' => 
  array (
    'name' => 'date_closed',
    'vname' => 'LBL_LIST_DATE_CLOSED',
    'width' => '15%',
    'default' => true,
  ),
  'assigned_user_name' => 
  array (
    'name' => 'assigned_user_name',
    'default' => true,
  ),
  'order_number' => 
  array (
    'name' => 'order_number',
    'widget_class' => 'SubPanelXcartLink',
    'default' => true,
  ),
  'users' => 
  array (
    'name' => 'users',
    'vname' => 'LBL_USERS_1',
    'width' => '10%',
    'default' => true,
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Opportunities',
    'width' => '4%',
    'default' => true,
  ),
  'amount_usdollar' => 
  array (
    'usage' => 'query_only',
    'currency_format' => true,
    'width' => '10%',
    'default' => true,
  ),
);
?>
