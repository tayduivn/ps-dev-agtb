<?php
// created: 2011-02-17 07:38:18
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_ibm_winplanswg"] = array (
  'order' => 100,
  'module' => 'ibm_WinPlanSWG',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_OPPORTUNITIES_IBM_WINPLANSWG_FROM_IBM_WINPLANSWG_TITLE',
  'get_subpanel_data' => 'opportunities_ibm_winplanswg',
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
