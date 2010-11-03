<?php
$searchdefs ['Bugs'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'bug_number' => 
      array (
        'name' => 'bug_number',
        'label' => 'LBL_NUMBER',
        'default' => true,
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
        'label' => 'LBL_SUBJECT',
        'default' => true,
      ),
      'current_user_only' => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
      ),
    ),
    'advanced_search' => 
    array (
      'bug_number' => 
      array (
        'name' => 'bug_number',
        'default' => true,
        'label' => 'LBL_NUMBER',
        'width' => '10%',
      ),
      'keyword' => 
      array (
        'name' => 'keyword',
        'type' => 'input',
        'label' => 'LBL_KEYWORD_SEARCH',
        'default' => true,
        'width' => '10%',
      ),
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
        'label' => 'LBL_SUBJECT',
        'width' => '10%',
      ),
      'status' => 
      array (
        'name' => 'status',
        'default' => true,
        'sortable' => false,
        'label' => 'LBL_STATUS',
        'width' => '10%',
      ),
      'product_category' => 
      array (
        'label' => 'LBL_PRODUCT_CATEGORY',
        'width' => '10%',
        'name' => 'product_category',
        'default' => true,
        'sortable' => false,
      ),
      'assigned_user_id' => 
      array (
        'label' => 'LBL_ASSIGNED_TO',
        'name' => 'assigned_user_id',
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
        'sortable' => false,
        'width' => '10%',
      ),
      'resolution' => 
      array (
        'name' => 'resolution',
        'default' => true,
        'sortable' => false,
        'label' => 'LBL_RESOLUTION',
        'width' => '10%',
      ),
      'fixed_in_release' => 
      array (
        'label' => 'LBL_FIXED_IN_RELEASE',
        'width' => '10%',
        'name' => 'fixed_in_release',
        'default' => true,
      ),
      'type' => 
      array (
        'name' => 'type',
        'default' => true,
        'sortable' => false,
        'label' => 'LBL_TYPE',
        'width' => '10%',
      ),
      'priority' => 
      array (
        'name' => 'priority',
        'default' => true,
        'sortable' => false,
        'label' => 'LBL_PRIORITY',
        'width' => '10%',
      ),
      'found_in_release' => 
      array (
        'name' => 'found_in_release',
        'default' => true,
        'label' => 'LBL_FOUND_IN_RELEASE',
        'width' => '10%',
      ),
      'feature_backlog_group_c' => 
      array (
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_FEATURE_BACKLOG_GROUP',
        'sortable' => false,
        'width' => '10%',
        'name' => 'feature_backlog_group_c',
      ),
      'created_by_name' => 
      array (
        'type' => 'relate',
        'link' => 'created_by_link',
        'label' => 'LBL_CREATED',
        'width' => '10%',
        'default' => true,
        'name' => 'created_by_name',
      ),
      'triaged_c' => 
      array (
        'label' => 'LBL_TRIAGED',
        'width' => '10%',
        'name' => 'triaged_c',
        'default_value' => '',
        'default' => true,
        'sortable' => false,
      ),
      'release_notes_c' => 
      array (
        'label' => 'release_notes_c',
        'width' => '10%',
        'name' => 'release_notes_c',
        'default_value' => '',
        'default' => true,
        'sortable' => false,
      ),
      'date_entered' => 
      array (
        'width' => '10%',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
        'name' => 'date_entered',
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
