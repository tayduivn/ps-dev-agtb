<?php
$module_name = 'DCEDataBases';
$searchdefs = array (
$module_name =>
array (
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
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
      ),
      'cluster_name' => 
      array (
        'label' => 'LBL_CLUSTER',
        'width' => '10',
        'name' => 'cluster_name',
      ),
      'host' => 
      array (
        'label' => 'LBL_HOST',
        'width' => '10',
        'name' => 'host',
      ),
      'primary_role' => 
      array (
        'label' => 'LBL_PRIMARY_ROLE',
        'width' => '5',
        'name' => 'primary_role',
      ),
      'reports_role' => 
      array (
        'label' => 'LBL_REPORTS_ROLE',
        'width' => '5',
        'name' => 'reports_role',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'label' => 'LBL_ASSIGNED_TO',
        'type' => 'enum',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
      ),
    ),
  ),
)
);
?>
