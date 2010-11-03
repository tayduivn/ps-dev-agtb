<?php
$searchdefs ['Cases'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'case_number',
      1 => 
      array (
        'name' => 'keyword',
        'type' => 'input',
        'label' => 'LBL_KEYWORD_SEARCH',
      ),
      2 => 'name',
      3 => 'account_name',
      4 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'case_number' => 
      array (
        'name' => 'case_number',
        'default' => true,
        'label' => 'LBL_NUMBER',
      ),
      'keyword' => 
      array (
        'name' => 'keyword',
        'type' => 'input',
        'label' => 'LBL_KEYWORD_SEARCH',
        'default' => true,
      ),
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
        'label' => 'LBL_SUBJECT',
      ),
      'account_name' => 
      array (
        'name' => 'account_name',
        'default' => true,
        'label' => 'LBL_ACCOUNT_NAME',
      ),
      'status' => 
      array (
        'name' => 'status',
        'default' => true,
        'sortable' => false,
        'label' => 'LBL_STATUS',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'type' => 'enum',
        'label' => 'LBL_ASSIGNED_TO',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
        'default' => true,
        'sortable' => false,
      ),
      'priority_level' => 
      array (
        'width' => '10%',
        'label' => 'LBL_PRIORITY_LEVEL',
        'sortable' => false,
        'default' => true,
        'name' => 'priority_level',
      ),
      'product_category_c' => 
      array (
        'width' => '10%',
        'label' => 'Category__c',
        'sortable' => false,
        'default' => true,
        'name' => 'product_category_c',
      ),
      'support_service_level_c' => 
      array (
        'width' => '10%',
        'label' => 'Support_Service_Level_c_1',
        'sortable' => false,
        'default' => true,
        'name' => 'support_service_level_c',
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
