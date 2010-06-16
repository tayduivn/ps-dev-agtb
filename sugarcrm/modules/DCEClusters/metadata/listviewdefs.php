<?php
$module_name = 'DCEClusters';
$listViewDefs = array (
$module_name =>
array (
  'NAME' => 
  array (
    'width' => '32',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'URL' => 
  array (
    'width' => '10',
    'label' => 'LBL_CLUSTER_SHELL_USER',
    'sortable' => false,
    'default' => true,
  ),
  'URL_FORMAT' => 
  array (
    'width' => '10',
    'label' => 'LBL_URL_FORMAT',
    'sortable' => false,
    'default' => false,
  ),
  'SERVER_STATUS' => 
  array (
    'width' => '10',
    'label' => 'LBL_SERVER_STATUS',
    'sortable' => false,
    'default' => true,
  ),
  'DATE_MODIFIED' => 
  array (
    'width' => '10',
    'label' => 'LBL_DATE_MODIFIED',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '9',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => true,
    'module' => 'Users',
    'id' => 'ASSIGNED_USER_ID',
    'link' => true,
    'related_fields' => 
    array (
      0 => 'assigned_user_id',
    ),
  ),
)
);
?>
