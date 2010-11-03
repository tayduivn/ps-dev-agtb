<?php
$dashletData['BugsDashlet']['searchFields'] = array (
  'priority' => 
  array (
    'default' => '',
  ),
  'found_in_release' => 
  array (
    'default' => '',
  ),
  'fixed_in_release' => 
  array (
    'default' => '',
  ),
  'type' => 
  array (
    'default' => '',
  ),
  'status' => 
  array (
    'default' => '',
  ),
  'product_category' => 
  array (
    'default' => '',
  ),
  'subcategory_c' => 
  array (
    'default' => '',
  ),
  'date_entered' => 
  array (
    'default' => '',
  ),
  'date_modified' => 
  array (
    'default' => '',
  ),
  'team_id' => 
  array (
    'default' => '',
  ),
);
$dashletData['BugsDashlet']['columns'] = array (
  'bug_number' => 
  array (
    'width' => '5%',
    'label' => 'LBL_NUMBER',
    'default' => true,
    'name' => 'bug_number',
  ),
  'name' => 
  array (
    'width' => '40%',
    'label' => 'LBL_LIST_SUBJECT',
    'link' => true,
    'default' => true,
    'name' => 'name',
  ),
  'priority' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRIORITY',
    'default' => true,
    'name' => 'priority',
  ),
  'status' => 
  array (
    'width' => '10%',
    'label' => 'LBL_STATUS',
    'default' => true,
    'name' => 'status',
  ),
  'resolution' => 
  array (
    'width' => '15%',
    'label' => 'LBL_RESOLUTION',
    'name' => 'resolution',
    'default' => false,
  ),
  'release_name' => 
  array (
    'width' => '15%',
    'label' => 'LBL_FOUND_IN_RELEASE',
    'related_fields' => 
    array (
      0 => 'found_in_release',
    ),
    'name' => 'release_name',
    'default' => false,
  ),
  'product_category' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_PRODUCT_CATEGORY',
    'sortable' => false,
    'width' => '10%',
    'default' => false,
  ),
  'type' => 
  array (
    'width' => '15%',
    'label' => 'LBL_TYPE',
    'name' => 'type',
    'default' => false,
  ),
  'fixed_in_release_name' => 
  array (
    'width' => '15%',
    'label' => 'LBL_FIXED_IN_RELEASE',
    'name' => 'fixed_in_release_name',
    'default' => false,
  ),
  'source' => 
  array (
    'width' => '15%',
    'label' => 'LBL_SOURCE',
    'name' => 'source',
    'default' => false,
  ),
  'date_entered' => 
  array (
    'width' => '15%',
    'label' => 'LBL_DATE_ENTERED',
    'name' => 'date_entered',
    'default' => false,
  ),
  'date_modified' => 
  array (
    'width' => '15%',
    'label' => 'LBL_DATE_MODIFIED',
    'name' => 'date_modified',
    'default' => false,
  ),
  'created_by' => 
  array (
    'width' => '8%',
    'label' => 'LBL_CREATED',
    'name' => 'created_by',
    'default' => false,
  ),
  'assigned_user_name' => 
  array (
    'width' => '8%',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'name' => 'assigned_user_name',
    'default' => false,
  ),
  'team_name' => 
  array (
    'width' => '15%',
    'label' => 'LBL_LIST_TEAM',
    'name' => 'team_name',
    'default' => false,
  ),
);
