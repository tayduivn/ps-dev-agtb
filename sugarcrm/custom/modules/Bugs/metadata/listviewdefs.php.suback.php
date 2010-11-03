<?php
$listViewDefs ['Bugs'] = 
array (
  'BUG_NUMBER' => 
  array (
    'width' => '5%',
    'label' => 'LBL_LIST_NUMBER',
    'link' => true,
    'default' => true,
  ),
  'PRODUCT_CATEGORY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRODUCT_CATEGORY',
    'sortable' => false,
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_LIST_SUBJECT',
    'default' => true,
    'link' => true,
  ),
  'STATUS' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_STATUS',
    'default' => true,
    'sortable' => false,
  ),
  'TYPE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_TYPE',
    'default' => true,
    'sortable' => false,
  ),
  'PRIORITY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_PRIORITY',
    'default' => true,
    'sortable' => false,
  ),
  'FIXED_IN_RELEASE_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_FIXED_IN_RELEASE',
    'default' => true,
    'related_fields' => 
    array (
      0 => 'fixed_in_release',
    ),
    'module' => 'Releases',
    'id' => 'FIXED_IN_RELEASE',
    'link' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_LIST_TEAM',
    'default' => true,
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
    'width' => '9%',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'default' => true,
    'module' => 'Users',
    'id' => 'ASSIGNED_USER_ID',
    'link' => true,
    'related_fields' => 
    array (
      0 => 'assigned_user_id',
    ),
  ),
  'RELEASE_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_FOUND_IN_RELEASE',
    'default' => false,
    'related_fields' => 
    array (
      0 => 'found_in_release',
    ),
    'module' => 'Releases',
    'id' => 'FOUND_IN_RELEASE',
    'link' => true,
  ),
  'RESOLUTION' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_RESOLUTION',
    'default' => false,
    'sortable' => false,
  ),
  'TRIAGED_C' => 
  array (
    'width' => '10%',
    'label' => 'LBL_TRIAGED',
    'sortable' => false,
    'default' => false,
  ),
  'CONTRIBUTION_AGREEMENT_C' => 
  array (
    'width' => '10%',
    'label' => 'LBL_CONTRIBUTION_AGREEMENT',
    'sortable' => false,
    'default' => false,
  ),
  'DATE_MODIFIED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATE_MODIFIED',
    'default' => false,
  ),
  'MODIFIED_BY_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_MODIFIED_NAME',
    'default' => false,
  ),
);
?>
