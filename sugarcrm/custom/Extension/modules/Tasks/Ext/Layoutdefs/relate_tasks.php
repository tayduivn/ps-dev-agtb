<?php

// BEGIN sadek - RELATE TASKS TO VARIOUS MODULES
$layout_defs["Tasks"]["subpanel_setup"]["tasks_tasks"] = array (
  'order' => 35,
  'module' => 'Tasks',
  'subpanel_name' => 'default',
  'sort_order' => 'desc',
  'sort_by' => 'date_due_c',
  'title_key' => 'LBL_TASKS_TITLE',
  'get_subpanel_data' => 'tasks_tasks',
  'top_buttons' =>
  array (
    //array('widget_class' => 'SubPanelTopCreateTaskButton'),
    array('widget_class' => 'SubPanelTopButtonQuickCreate'),
    array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect'),
  ),
);
