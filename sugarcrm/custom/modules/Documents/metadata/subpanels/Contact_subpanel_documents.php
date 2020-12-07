<?php
// created: 2020-11-14 07:20:57
$subpanel_layout['list_fields'] = array (
  'document_name' => 
  array (
    'name' => 'document_name',
    'vname' => 'LBL_LIST_DOCUMENT_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => 10,
    'default' => true,
  ),
  'filename' => 
  array (
    'name' => 'filename',
    'vname' => 'LBL_LIST_FILENAME',
    'width' => 10,
    'module' => 'Documents',
    'sortable' => false,
    'displayParams' => 
    array (
      'module' => 'Documents',
    ),
    'default' => true,
  ),
  'template_type' => 
  array (
    'type' => 'enum',
    'vname' => 'LBL_TEMPLATE_TYPE',
    'width' => 10,
    'default' => true,
  ),
  'doc_type' => 
  array (
    'name' => 'doc_type',
    'vname' => 'LBL_LIST_DOC_TYPE',
    'width' => 10,
    'default' => true,
  ),
  'status_id' => 
  array (
    'name' => 'status_id',
    'vname' => 'LBL_LIST_STATUS',
    'width' => 10,
    'default' => true,
  ),
  'active_date' => 
  array (
    'name' => 'active_date',
    'vname' => 'LBL_LIST_ACTIVE_DATE',
    'width' => 10,
    'default' => true,
  ),
  'document_revision_id' => 
  array (
    'name' => 'document_revision_id',
    'usage' => 'query_only',
  ),
);