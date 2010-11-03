<?php
// created: 2008-02-14 15:40:11
$subpanel_layout['list_fields'] = array (
  'first_name' => 
  array (
    'usage' => 'query_only',
  ),
  'last_name' => 
  array (
    'usage' => 'query_only',
  ),
  'name' => 
  array (
    'vname' => 'LBL_LIST_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'sort_order' => 'asc',
    'sort_by' => 'last_name',
    'module' => 'Leads',
    'width' => '20',
  ),
  'refered_by' => 
  array (
    'vname' => 'LBL_LIST_REFERED_BY',
    'width' => '13',
  ),
  'lead_source' => 
  array (
    'vname' => 'LBL_LIST_LEAD_SOURCE',
    'width' => '13',
  ),
  'phone_work' => 
  array (
    'vname' => 'LBL_LIST_PHONE',
    'width' => '10',
  ),
  'email1' => 
  array (
    'vname' => 'LBL_LIST_EMAIL_ADDRESS',
    'width' => '10',
    'widget_class' => 'SubPanelEmailLink',
  ),
  'lead_source_description' => 
  array (
    'name' => 'lead_source_description',
    'vname' => 'LBL_LIST_LEAD_SOURCE_DESCRIPTION',
    'width' => '26',
    'sortable' => false,
  ),
  'assigned_user_name' => 
  array (
    'name' => 'assigned_user_name',
    'vname' => 'LBL_LIST_ASSIGNED_TO_NAME',
    'width' => '10',
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Leads',
    'width' => '4',
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Leads',
    'width' => '4',
  ),
);
?>
