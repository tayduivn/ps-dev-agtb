<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// created: 2005-09-15 18:10:25
$subpanel_layout['list_fields'] = array (
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
  'contact_id' => 
  array (
    'usage' => 'query_only',
  ),
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_LIST_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'module' => 'Contacts',
    'width' => '23%',
  ),
  'title' => 
  array (
    'name' => 'title',
  ),
  'email1' => 
  array (
    'name' => 'email1',
    'vname' => 'LBL_LIST_EMAIL',
    'widget_class' => 'SubPanelEmailLink',
    'width' => '30%',
  ),
  'phone_home' => 
  array (
    'name' => 'phone_home',
    'vname' => 'LBL_LIST_PHONE',
    'width' => '15%',
  ),
  'edit_button' => 
  array (
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Contacts',
    'width' => '5%',
  ),
  'remove_button' => 
  array (
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Contacts',
    'width' => '5%',
  ),
);
?>
