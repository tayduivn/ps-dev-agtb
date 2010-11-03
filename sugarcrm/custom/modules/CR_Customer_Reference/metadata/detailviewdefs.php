<?php
$module_name = 'CR_Customer_Reference';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'cr_customer_reference_accounts_name',
            'label' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
          ),
          1 => 
          array (
            'name' => 'reference',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'reference_type',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE_TYPE',
          ),
          1 => 
          array (
            'name' => 'user_type',
            'studio' => 'visible',
            'label' => 'LBL_USER_TYPE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'reference_activity',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE_ACTIVITY',
          ),
          1 => 
          array (
            'name' => 'reference_deliverables',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE_DELIVERABLES',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'follow_up',
            'label' => 'LBL_FOLLOW_UP',
          ),
          1 => 
          array (
            'name' => 'cr_customer_reference_contacts_name',
            'label' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CONTACTS_TITLE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'activity_status',
            'studio' => 'visible',
            'label' => 'LBL_ACTIVITY_STATUS',
          ),
          1 => 
          array (
            'name' => 'reference_score',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE_SCORE',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'gifts_recieved',
            'studio' => 'visible',
            'label' => 'LBL_GIFTS_RECIEVED',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'cr_date_published_c',
            'label' => 'LBL_CR_DATE_PUBLISHED',
          ),
          1 => 
          array (
            'name' => 'origination_date_c',
            'label' => 'LBL_ORIGINATION_DATE',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'solution',
            'studio' => 'visible',
            'label' => 'LBL_SOLUTION',
          ),
          1 => '',
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'activities_completed_date',
            'studio' => 'visible',
            'label' => 'LBL_ACTIVITIES_COMPLETED_DATE ',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'cr_resources_c',
            'studio' => 'visible',
            'label' => 'LBL_CR_RESOURCES',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'reference_notes',
            'studio' => 'visible',
            'label' => 'LBL_REFERENCE_NOTES',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
      ),
    ),
  ),
);
?>
