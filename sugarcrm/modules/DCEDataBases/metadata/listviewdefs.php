<?php
$module_name = 'DCEDataBases';
$listViewDefs = array (
$module_name =>
array (
  'NAME' => 
  array (
    'width' => '30',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'CLUSTER_NAME' => 
  array (
    'width' => '20',
    'label' => 'LBL_CLUSTER',
    'default' => true,
    'link' => true,
  ),
  'PRIMARY_ROLE' => 
  array (
    'width' => '5',
    'label' => 'LBL_PRIMARY_ROLE',
    'sortable' => false,
    'default' => true,
  ),
  'REPORTS_ROLE' => 
  array (
    'width' => '5',
    'label' => 'LBL_REPORTS_ROLE',
    'sortable' => false,
    'default' => true,
  ),
  'HOST' => 
  array (
    'width' => '15',
    'label' => 'LBL_HOST',
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
