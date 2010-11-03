<?php
// created: 2010-07-21 13:20:25
$subpanel_layout['list_fields'] = array (
  'subscription_id' => 
  array (
    'vname' => 'LBL_LIST_SUBSCRIPTION_ID',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '40%',
    'default' => true,
  ),
  'account_name' => 
  array (
    'vname' => 'LBL_ACCOUNT_NAME',
    'width' => '40%',
    'default' => true,
  ),
  'expiration_date' => 
  array (
    'type' => 'date',
    'vname' => 'LBL_EXPIRATION_DATE',
    'width' => '10%',
    'default' => true,
  ),
  'status' => 
  array (
    'vname' => 'LBL_LIST_STATUS',
    'width' => '10%',
    'default' => true,
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Subscriptions',
    'width' => '5%',
    'default' => true,
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Subscriptions',
    'width' => '5%',
    'default' => true,
  ),
);
?>
