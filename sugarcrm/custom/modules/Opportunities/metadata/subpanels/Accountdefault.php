<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// created: 2006-03-08 14:23:08
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_LIST_OPPORTUNITY_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '50%',
  ),
  'sales_stage' => 
  array (
    'name' => 'sales_stage',
  ),
  'date_closed' => 
  array (
    'name' => 'date_closed',
    'vname' => 'LBL_LIST_DATE_CLOSED',
    'width' => '15%',
  ),
  'assigned_user_name' => 
  array (
    'name' => 'assigned_user_name',
  ),
  'order_number' => 
  array (
    'name' => 'order_number',
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Opportunities',
    'width' => '4%',
  ),
);
?>
