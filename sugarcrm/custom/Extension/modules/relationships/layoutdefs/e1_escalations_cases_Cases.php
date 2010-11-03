<?php
// created: 2010-10-11 16:22:27
$layout_defs["Cases"]["subpanel_setup"]["e1_escalations_cases"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_E1_ESCALATIONS_CASES_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'e1_escalations_cases',
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
