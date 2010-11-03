<?php
$module_name='sales_SETicket';
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
      'popup_module' => 'sales_SETicket',
    ),
  ),
  'where' => '',
  'list_fields' => 
  array (
    'sales_seticket_number' => 
    array (
      'vname' => 'LBL_NUMBER',
      'width' => '5%',
      'default' => true,
    ),
    'name' => 
    array (
      'vname' => 'LBL_SUBJECT',
      'widget_class' => 'SubPanelDetailViewLink',
      'width' => '45%',
      'default' => true,
    ),
    'tickettype' => 
    array (
      'type' => 'multienum',
      'default' => true,
      'studio' => 'visible',
      'vname' => 'LBL_TICKETTYPE',
      'width' => '10%',
    ),
    'status' => 
    array (
      'vname' => 'LBL_STATUS',
      'width' => '15%',
      'default' => true,
    ),
    'assigned_user_name' => 
    array (
      'name' => 'assigned_user_name',
      'vname' => 'LBL_ASSIGNED_TO_NAME',
      'width' => '10%',
      'default' => true,
    ),
    'edit_button' => 
    array (
      'widget_class' => 'SubPanelEditButton',
      'module' => 'sales_SETicket',
      'width' => '4%',
      'default' => true,
    ),
    'remove_button' => 
    array (
      'widget_class' => 'SubPanelRemoveButton',
      'module' => 'sales_SETicket',
      'width' => '5%',
      'default' => true,
    ),
  ),
);