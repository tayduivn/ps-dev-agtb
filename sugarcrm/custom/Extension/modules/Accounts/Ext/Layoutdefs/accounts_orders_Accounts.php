<?php
// created: 2010-07-27 14:40:36
$layout_defs["Accounts"]["subpanel_setup"]["accounts_orders"] = array (
  'order' => 100,
  'module' => 'Orders',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_ACCOUNTS_ORDERS_FROM_ORDERS_TITLE',
  'get_subpanel_data' => 'accounts_orders',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);
