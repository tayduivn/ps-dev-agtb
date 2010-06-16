<?php
$module_name='DCEClusters';
$subpanel_layout = array (
  'top_buttons' => 
  array (
  ),
  'where' => '',
  'list_fields' => 
  array (
    'name' => 
    array (
      'vname' => 'LBL_NAME',
      'widget_class' => 'SubPanelDetailViewLink',
      'width' => '30',
    ),
    'url' => 
    array (
      'name' => 'url',
      'vname' => '',
      'width' => '15',
    ),
    'server_status' => 
    array (
      'name' => 'server_status',
      'vname' => '',
      'width' => '10',
    ),
    'date_modified' => 
    array (
      'vname' => 'LBL_DATE_MODIFIED',
      'width' => '15',
    ),
    'edit_button' => 
    array (
      'widget_class' => 'SubPanelEditButton',
      'module' => 'DCEClusters',
      'width' => '4',
    ),
  ),
);