<?php
$module_name = 'CR_Customer_Reference';
$searchdefs [$module_name] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'name',
      1 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'user_type' => 
      array (
        'type' => 'multienum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_USER_TYPE',
        'width' => '10%',
        'name' => 'user_type',
      ),
      'solution' => 
      array (
        'type' => 'multienum',
        'studio' => 'visible',
        'label' => 'LBL_SOLUTION',
        'width' => '10%',
        'default' => true,
        'name' => 'solution',
      ),
      'reference' => 
      array (
        'type' => 'enum',
        'studio' => 'visible',
        'label' => 'LBL_REFERENCE',
        'sortable' => false,
        'width' => '10%',
        'default' => true,
        'name' => 'reference',
      ),
      'reference_deliverables' => 
      array (
        'type' => 'multienum',
        'studio' => 'visible',
        'label' => 'LBL_REFERENCE_DELIVERABLES',
        'width' => '10%',
        'default' => true,
        'name' => 'reference_deliverables',
      ),
      'activity_status' => 
      array (
        'type' => 'enum',
        'studio' => 'visible',
        'label' => 'LBL_ACTIVITY_STATUS',
        'sortable' => false,
        'width' => '10%',
        'default' => true,
        'name' => 'activity_status',
      ),
      'reference_type' => 
      array (
        'type' => 'multienum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_REFERENCE_TYPE',
        'width' => '10%',
        'name' => 'reference_type',
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
