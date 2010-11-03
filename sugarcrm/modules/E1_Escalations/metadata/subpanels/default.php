<?php
$module_name='E1_Escalations';
$subpanel_layout = array (
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'E1_Escalations',
    ),
  ),
  'where' => '',
  'list_fields' => 
  array (
    'name' => 
    array (
      'vname' => 'LBL_NAME',
      'widget_class' => 'SubPanelDetailViewLink',
      'width' => '45%',
      'default' => true,
    ),
    'dateescalated' => 
    array (
      'width' => '10%',
      'vname' => 'LBL_DATEESCALATED',
      'default' => true,
    ),
    'datereviewed' => 
    array (
      'width' => '10%',
      'vname' => 'LBL_DATEREVIEWED',
      'default' => true,
    ),
    'urgency' => 
    array (
      'width' => '10%',
      'vname' => 'LBL_URGENCY',
      'sortable' => false,
      'default' => true,
    ),
    'source' => 
    array (
      'width' => '10%',
      'vname' => 'LBL_SOURCE',
      'sortable' => false,
      'default' => true,
    ),
    'edit_button' => 
    array (
      'widget_class' => 'SubPanelEditButton',
      'module' => 'E1_Escalations',
      'width' => '4%',
      'default' => true,
    ),
    'remove_button' => 
    array (
      'widget_class' => 'SubPanelRemoveButton',
      'module' => 'E1_Escalations',
      'width' => '5%',
      'default' => true,
    ),
  ),
);