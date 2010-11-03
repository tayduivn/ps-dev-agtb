<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-26 08:58:38
$layout_defs["Orders"]["subpanel_setup"]["orders_contracts"] = array (
  'order' => 100,
  'module' => 'Contracts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_ORDERS_CONTRACTS_FROM_CONTRACTS_TITLE',
  'get_subpanel_data' => 'orders_contracts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
  ),
);


// created: 2010-07-27 14:42:52
$layout_defs["Orders"]["subpanel_setup"]["orders_documents"] = array (
  'order' => 100,
  'module' => 'Documents',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_ORDERS_DOCUMENTS_FROM_DOCUMENTS_TITLE',
  'get_subpanel_data' => 'orders_documents',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
  ),
);


// created: 2010-07-27 14:43:42
$layout_defs["Orders"]["subpanel_setup"]["activities"] = array (
  'order' => 10,
  'sort_order' => 'desc',
  'sort_by' => 'date_start',
  'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
  'type' => 'collection',
  'subpanel_name' => 'activities',
  'module' => 'Activities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateTaskButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopScheduleMeetingButton',
    ),
    2 => 
    array (
      'widget_class' => 'SubPanelTopScheduleCallButton',
    ),
    3 => 
    array (
      'widget_class' => 'SubPanelTopComposeEmailButton',
    ),
  ),
  'collection_list' => 
  array (
    'meetings' => 
    array (
      'module' => 'Meetings',
      'subpanel_name' => 'ForActivities',
      'get_subpanel_data' => 'orders_activities_meetings',
    ),
    'tasks' => 
    array (
      'module' => 'Tasks',
      'subpanel_name' => 'ForActivities',
      'get_subpanel_data' => 'orders_activities_tasks',
    ),
    'calls' => 
    array (
      'module' => 'Calls',
      'subpanel_name' => 'ForActivities',
      'get_subpanel_data' => 'orders_activities_calls',
    ),
  ),
  'get_subpanel_data' => 'activities',
);
$layout_defs["Orders"]["subpanel_setup"]["history"] = array (
  'order' => 20,
  'sort_order' => 'desc',
  'sort_by' => 'date_modified',
  'title_key' => 'LBL_HISTORY',
  'type' => 'collection',
  'subpanel_name' => 'history',
  'module' => 'History',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateNoteButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopArchiveEmailButton',
    ),
    2 => 
    array (
      'widget_class' => 'SubPanelTopSummaryButton',
    ),
  ),
  'collection_list' => 
  array (
    'meetings' => 
    array (
      'module' => 'Meetings',
      'subpanel_name' => 'ForHistory',
      'get_subpanel_data' => 'orders_activities_meetings',
    ),
    'tasks' => 
    array (
      'module' => 'Tasks',
      'subpanel_name' => 'ForHistory',
      'get_subpanel_data' => 'orders_activities_tasks',
    ),
    'calls' => 
    array (
      'module' => 'Calls',
      'subpanel_name' => 'ForHistory',
      'get_subpanel_data' => 'orders_activities_calls',
    ),
    'notes' => 
    array (
      'module' => 'Notes',
      'subpanel_name' => 'ForHistory',
      'get_subpanel_data' => 'orders_activities_notes',
    ),
    'emails' => 
    array (
      'module' => 'Emails',
      'subpanel_name' => 'ForHistory',
      'get_subpanel_data' => 'orders_activities_emails',
    ),
  ),
  'get_subpanel_data' => 'history',
);


// created: 2010-07-27 14:39:12
$layout_defs["Orders"]["subpanel_setup"]["orders_products"] = array (
  'order' => 100,
  'module' => 'Products',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_ORDERS_PRODUCTS_FROM_PRODUCTS_TITLE',
  'get_subpanel_data' => 'orders_products',
  'top_buttons' => array(),
);


//auto-generated file DO NOT EDIT
$layout_defs['Orders']['subpanel_setup']['orders_products']['override_subpanel_name'] = 'Ordersdefault';

?>