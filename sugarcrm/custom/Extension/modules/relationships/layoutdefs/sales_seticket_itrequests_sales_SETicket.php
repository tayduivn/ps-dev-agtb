<?php
// created: 2010-09-16 06:40:08
$layout_defs["sales_SETicket"]["subpanel_setup"]["sales_seticket_itrequests"] = array (
  'order' => 100,
  'module' => 'ITRequests',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_ITREQUESTS_FROM_ITREQUESTS_TITLE',
  'get_subpanel_data' => 'sales_seticket_itrequests',
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
