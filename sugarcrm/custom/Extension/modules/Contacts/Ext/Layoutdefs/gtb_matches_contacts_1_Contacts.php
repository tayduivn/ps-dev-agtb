<?php
 // created: 2020-11-20 13:15:24
$layout_defs["Contacts"]["subpanel_setup"]['gtb_matches_contacts_1'] = array (
  'order' => 100,
  'module' => 'gtb_matches',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_GTB_MATCHES_CONTACTS_1_FROM_GTB_MATCHES_TITLE',
  'get_subpanel_data' => 'gtb_matches_contacts_1',
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
