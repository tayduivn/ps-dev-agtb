<?php
$module_name = 'DCETemplates';
$listViewDefs = array (
$module_name =>
array (
  'NAME' => 
  array (
    'width' => '20',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'STATUS' => 
  array (
    'width' => '10',
    'label' => 'LBL_STATUS',
    'sortable' => false,
    'default' => true,
  ),
  'SUGAR_VERSION' => 
  array (
    'width' => '10',
    'label' => 'LBL_SUGAR_VERSION',
    'sortable' => false,
    'default' => true,
  ),
  'SUGAR_EDITION' => 
  array (
    'width' => '10',
    'label' => 'LBL_SUGAR_EDITION',
    'sortable' => false,
    'default' => true,
  ),
  'TEMPLATE_NAME' => 
  array (
    'width' => '10',
    'label' => 'LBL_TEMPLATE_NAME',
    'sortable' => false,
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '9',
    'label' => 'LBL_TEAM',
    'default' => false,
    'module' => 'Teams',
    'id' => 'TEAM_ID',
    'link' => true,
    'related_fields' => 
    array (
      0 => 'team_id',
    ),
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
