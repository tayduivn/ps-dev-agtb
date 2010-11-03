<?php
// created: 2008-10-06 05:00:56
$layout_defs["Bugs"]["subpanel_setup"]["bugs_e1_escalations"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_BUGS_E1_ESCALATIONS_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'bugs_e1_escalations',
);
?>
<?php
// created: 2009-06-08 17:15:59
$layout_defs["Bugs"]["subpanel_setup"]["bugs_bugs"] = array (
  'order' => 100,
  'module' => 'Bugs',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_BUGS_BUGS_FROM_BUGS_TITLE',
  'get_subpanel_data' => 'bugs_bugs',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'Bugs',
      'mode' => 'MultiSelect',
    ),
  ),
);
?>
