<?php
$dashletData['DocumentsDashlet']['searchFields'] = array (
  'date_entered' => 
  array (
    'default' => '',
  ),
  'document_name' => 
  array (
    'default' => '',
  ),
  'category_id' => 
  array (
    'default' => '',
  ),
  'doc_type' => 
  array (
    'default' => '',
  ),
  'status_id' => 
  array (
    'default' => '',
  ),
  'active_date' => 
  array (
    'default' => '',
  ),
  'team_id' => 
  array (
    'default' => '',
    'label' => 'LBL_TEAMS',
  ),
);
$dashletData['DocumentsDashlet']['columns'] = array (
  'document_name' => 
  array (
    'width' => '40%',
    'label' => 'LBL_NAME',
    'link' => '1',
    'default' => true,
    'name' => 'document_name',
  ),
  'category_id' => 
  array (
    'width' => '8%',
    'label' => 'LBL_CATEGORY',
    'default' => true,
    'name' => 'category_id',
  ),
  'status_id' => 
  array (
    'width' => '8%',
    'label' => 'LBL_STATUS',
    'default' => true,
    'name' => 'status_id',
  ),
  'active_date' => 
  array (
    'width' => '8%',
    'label' => 'LBL_ACTIVE_DATE',
    'default' => true,
    'name' => 'active_date',
  ),
  'subcategory_id' => 
  array (
    'width' => '8%',
    'label' => 'LBL_SUBCATEGORY',
    'default' => false,
    'name' => 'subcategory_id',
  ),
  'is_template' => 
  array (
    'width' => '8%',
    'label' => 'LBL_IS_TEMPLATE',
    'default' => false,
    'name' => 'is_template',
  ),
  'exp_date' => 
  array (
    'width' => '8%',
    'label' => 'LBL_EXPIRATION_DATE',
    'default' => false,
    'name' => 'exp_date',
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
