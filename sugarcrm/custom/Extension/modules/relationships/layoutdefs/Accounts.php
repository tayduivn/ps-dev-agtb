<?php
// created: 2009-02-13 17:29:10
$layout_defs["Accounts"]["subpanel_setup"]["opportunities_accounts"] = array (
  'order' => 100,
  'module' => 'Opportunities',
  'subpanel_name' => 'ForAccounts',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_OPPORTUNITIES_ACCOUNTS_FROM_OPPORTUNITIES_TITLE',
  'get_subpanel_data' => 'opportunities_accounts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'Opportunities',
      'mode' => 'MultiSelect',
    ),
  ),
);
?>
