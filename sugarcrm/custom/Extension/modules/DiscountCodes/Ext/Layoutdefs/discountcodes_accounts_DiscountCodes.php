<?php
// created: 2010-07-27 14:46:32
$layout_defs["DiscountCodes"]["subpanel_setup"]["discountcodes_accounts"] = array (
  'order' => 100,
  'module' => 'Accounts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'get_subpanel_data' => 'discountcodes_accounts',
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
