<?php
$module_name = 'DiscountCodes';
$searchdefs [$module_name] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'discount_code' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_DISCOUNT_CODE',
        'width' => '10%',
        'default' => true,
        'name' => 'discount_code',
      ),
      'current_user_only' => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
      ),
    ),
    'advanced_search' => 
    array (
      'discount_code' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_DISCOUNT_CODE',
        'width' => '10%',
        'default' => true,
        'name' => 'discount_code',
      ),
      'code_type' => 
      array (
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_CODE_TYPE',
        'sortable' => false,
        'width' => '10%',
        'name' => 'code_type',
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
        'default' => true,
        'width' => '10%',
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
