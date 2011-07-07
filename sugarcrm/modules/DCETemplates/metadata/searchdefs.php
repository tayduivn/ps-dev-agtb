<?php
$module_name = 'DCETemplates';
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
      'template_name' => 
      array (
        'name' => 'template_name',
        'label' => 'LBL_TEMPLATE_NAME',
      ),
      'sugar_edition' => 
      array (
        'label' => 'LBL_SUGAR_EDITION',
        'name' => 'sugar_edition',
      ),
      'sugar_version' => 
      array (
        'label' => 'LBL_SUGAR_VERSION',
        'name' => 'sugar_version',
      ),
      'status' => 
      array (
        'label' => 'LBL_STATUS',
        'name' => 'status',
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
