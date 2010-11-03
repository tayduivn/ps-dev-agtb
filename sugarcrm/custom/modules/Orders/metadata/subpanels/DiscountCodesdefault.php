<?php
// created: 2010-10-21 21:13:08
$subpanel_layout['list_fields'] = array (
  'order_id' => 
  array (
    'vname' => 'LBL_NUMBER',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '45%',
    'default' => true,
  ),
  'status' => 
  array (
    'type' => 'enum',
    'default' => true,
    'studio' => 'visible',
    'vname' => 'LBL_STATUS',
    'sortable' => false,
    'width' => '10%',
  ),
  'payment_method' => 
  array (
    'type' => 'enum',
    'default' => true,
    'studio' => 'visible',
    'vname' => 'LBL_PAYMENT_METHOD',
    'sortable' => false,
    'width' => '10%',
  ),
  'date_entered' => 
  array (
    'type' => 'datetime',
    'vname' => 'LBL_DATE_ENTERED',
    'width' => '10%',
    'default' => true,
  ),
  'total' => 
  array (
    'type' => 'decimal',
    'vname' => 'LBL_TOTAL',
    'width' => '10%',
    'default' => true,
  ),
  'assigned_user_name' => 
  array (
    'link' => 'assigned_user_link',
    'type' => 'relate',
    'vname' => 'LBL_ASSIGNED_TO_NAME',
    'width' => '10%',
    'default' => true,
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Orders',
    'width' => '4%',
    'default' => true,
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Orders',
    'width' => '5%',
    'default' => true,
  ),
);
?>
