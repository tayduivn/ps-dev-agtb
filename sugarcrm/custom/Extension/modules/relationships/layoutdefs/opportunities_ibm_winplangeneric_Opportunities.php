<?php
// created: 2011-02-17 06:11:18
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_ibm_winplangeneric"] = array (
  'order' => 100,
  'module' => 'ibm_WinPlanGeneric',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_OPPORTUNITIES_IBM_WINPLANGENERIC_FROM_IBM_WINPLANGENERIC_TITLE',
  'get_subpanel_data' => 'opportunities_ibm_winplangeneric',
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
