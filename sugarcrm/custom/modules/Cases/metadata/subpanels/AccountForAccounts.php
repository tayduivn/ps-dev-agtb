<?php
// created: 2008-12-09 16:07:46
$subpanel_layout['list_fields'] = array (
  'case_number' => 
  array (
    'vname' => 'LBL_LIST_NUMBER',
    'width' => '6%',
    'default' => true,
  ),
  'name' => 
  array (
    'vname' => 'LBL_LIST_SUBJECT',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '50%',
    'default' => true,
  ),
  'status' => 
  array (
    'vname' => 'LBL_LIST_STATUS',
    'width' => '10%',
    'default' => true,
  ),
  'date_entered' => 
  array (
    'vname' => 'LBL_LIST_DATE_CREATED',
    'width' => '10%',
    'default' => true,
  ),
  'assigned_user_name' => 
  array (
    'name' => 'assigned_user_name',
    'default' => true,
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Cases',
    'width' => '4%',
    'default' => true,
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Cases',
    'width' => '10%',
    'default' => true,
  ),
  'system_id' => 
  array (
    'usage' => 'query_only',
  ),
);
?>
