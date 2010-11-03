<?php
// created: 2010-09-07 18:08:41
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_LIST_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'module' => 'Contacts',
    'width' => '23%',
    'default' => true,
  ),
  'title' => 
  array (
    'name' => 'title',
    'default' => true,
    'width' => '10%',
  ),
  'email1' => 
  array (
    'name' => 'email1',
    'vname' => 'LBL_LIST_EMAIL',
    'widget_class' => 'SubPanelEmailLink',
    'width' => '30%',
    'default' => true,
  ),
  'portal_active' => 
  array (
    'width' => '10%',
    'vname' => 'LBL_PORTAL_ACTIVE',
    'default' => true,
  ),
  'portal_name' => 
  array (
    'type' => 'varchar',
    'vname' => 'LBL_PORTAL_NAME',
    'width' => '10%',
    'default' => true,
  ),
  'phone_work' => 
  array (
    'name' => 'phone_work',
    'vname' => 'LBL_LIST_PHONE',
    'width' => '15%',
    'default' => true,
    'widget_class' => 'Fieldfonalityphone',
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Contacts',
    'width' => '5%',
    'default' => true,
  ),
  'first_name' => 
  array (
    'name' => 'first_name',
    'usage' => 'query_only',
  ),
  'last_name' => 
  array (
    'name' => 'last_name',
    'usage' => 'query_only',
  ),
  'account_id' => 
  array (
    'usage' => 'query_only',
  ),
);
?>
