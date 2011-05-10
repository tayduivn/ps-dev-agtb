<?php
$module_name = 'DCEInstances';
$listViewDefs = array (
$module_name =>
array (
  'NAME' => 
  array (
    'width' => '10',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'ACCOUNT_NAME' =>
  array(
    'width' => '10', 
    'label' => 'LBL_ACCOUNT', 
    'module' => 'Accounts',
    'id' => 'ACCOUNT_ID',
    'link' => true,
    'default' => true,
    'ACLTag' => 'ACCOUNT',
    'related_fields' => 
    array (
        0 => 'account_id',
    ),
  ),
  'LICENSED_USERS' => 
  array (
    'width' => '5',
    'label' => 'LBL_LICENSED_USERS',
    'default' => true,
  ),
  'DCETEMPLATE_NAME' => array(
    'width' => '10', 
    'label' => 'LBL_TEMPLATE', 
    'module' => 'DCETemplates',
    'id' => 'DCETEMPLATE_ID',
    'link' => true,
    'default' => true,
    'ACLTag' => 'DCETEMPLATE',
    'related_fields' => 
    array (
        0 => 'dcetemplate_id',
    ),
  ),
  'SUGAR_EDITION' => 
  array (
    'width' => '5',
    'label' => 'LBL_SUGAR_EDITION',
    'default' => true,
  ),
  'SUGAR_VERSION' => 
  array (
    'width' => '5',
    'label' => 'LBL_SUGAR_VERSION',
    'default' => true,
  ),
  'TYPE' => 
  array (
    'width' => '6',
    'label' => 'LBL_TYPE',
    'default' => true,
  ),
  'STATUS' => 
  array (
    'width' => '6',
    'label' => 'LBL_STATUS',
    'default' => true,
  ),
  'DATE_ENTERED' => 
  array (
    'width' => '9',
    'label' => 'LBL_DATE_ENTERED',
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '6',
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
    'width' => '6',
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
