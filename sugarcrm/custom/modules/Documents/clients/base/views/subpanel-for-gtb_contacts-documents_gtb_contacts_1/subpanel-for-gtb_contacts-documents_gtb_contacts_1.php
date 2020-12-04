<?php
// created: 2020-11-14 03:30:35
$viewdefs['Documents']['base']['view']['subpanel-for-gtb_contacts-documents_gtb_contacts_1'] = array (
  'panels' => 
  array (
    0 => 
    array (
      'name' => 'panel_header',
      'label' => 'LBL_PANEL_1',
      'fields' => 
      array (
        0 => 
        array (
          'name' => 'document_name',
          'label' => 'LBL_LIST_DOCUMENT_NAME',
          'enabled' => true,
          'default' => true,
          'link' => true,
        ),
        1 => 
        array (
          'name' => 'filename',
          'label' => 'LBL_LIST_FILENAME',
          'enabled' => true,
          'default' => true,
          'readonly' => true,
        ),
        2 => 
        array (
          'name' => 'template_type',
          'label' => 'LBL_TEMPLATE_TYPE',
          'enabled' => true,
          'default' => true,
        ),
        3 => 
        array (
          'name' => 'doc_type',
          'label' => 'LBL_LIST_DOC_TYPE',
          'enabled' => true,
          'default' => true,
          'readonly' => true,
        ),
        4 => 
        array (
          'name' => 'status_id',
          'label' => 'LBL_LIST_STATUS',
          'enabled' => true,
          'default' => true,
        ),
        5 => 
        array (
          'name' => 'active_date',
          'label' => 'LBL_LIST_ACTIVE_DATE',
          'enabled' => true,
          'default' => true,
        ),
      ),
    ),
  ),
  'rowactions' => 
  array (
    'actions' => 
    array (
      0 => 
      array (
        'type' => 'rowaction',
        'name' => 'edit_button',
        'icon' => 'fa-pencil',
        'label' => 'LBL_EDIT_BUTTON',
        'event' => 'list:editrow:fire',
        'acl_action' => 'edit',
      ),
      1 => 
      array (
        'type' => 'unlink-action',
        'name' => 'unlink_button',
        'icon' => 'fa-chain-broken',
        'label' => 'LBL_UNLINK_BUTTON',
      ),
    ),
  ),
  'type' => 'subpanel-list',
);