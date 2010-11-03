<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// created: 2006-02-22 21:40:26
$subpanel_layout['list_fields'] = array (
  'system_id' => 
  array (
    'usage' => 'query_only',
  ),
  'case_number' => 
  array (
    'vname' => 'LBL_LIST_NUMBER',
    'width' => '6%',
  ),
  'name' => 
  array (
    'vname' => 'LBL_LIST_SUBJECT',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '50%',
  ),
  'assigned_user_name' => 
  array (
    'name' => 'assigned_user_name',
  ),
  'status' => 
  array (
    'vname' => 'LBL_LIST_STATUS',
    'width' => '10%',
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Cases',
    'width' => '4%',
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Cases',
    'width' => '5%',
  ),
);
?>
