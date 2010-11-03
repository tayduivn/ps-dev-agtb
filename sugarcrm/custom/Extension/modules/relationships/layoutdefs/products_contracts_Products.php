<?php
// created: 2010-07-21 07:19:23
$layout_defs["Products"]["subpanel_setup"]["products_contracts"] = array (
  'order' => 100,
  'module' => 'Contracts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_PRODUCTS_CONTRACTS_FROM_CONTRACTS_TITLE',
  'get_subpanel_data' => 'products_contracts',
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
