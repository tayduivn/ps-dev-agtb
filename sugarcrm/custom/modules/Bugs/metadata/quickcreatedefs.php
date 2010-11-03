<?php
$viewdefs ['Bugs'] = 
array (
  'QuickCreate' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'hidden' => 
        array (
          0 => '<input type="hidden" name="account_id" value="{$smarty.request.account_id}">',
          1 => '<input type="hidden" name="contact_id" value="{$smarty.request.contact_id}">',
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
    ),
    'panels' => 
    array (
      'DEFAULT' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'bug_number',
            'type' => 'readonly',
            'label' => 'LBL_NUMBER',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'priority',
            'label' => 'LBL_PRIORITY',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'release_notes_c',
            'label' => 'release_notes_c',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'type',
            'label' => 'LBL_TYPE',
          ),
          1 => 
          array (
            'name' => 'ui_changes_c',
            'label' => 'LBL_UI_CHANGES_C',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'source',
            'label' => 'LBL_SOURCE',
          ),
          1 => NULL,
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'souceforge_id',
            'label' => 'LBL_SOUCEFORGE_ID',
          ),
          1 => 
          array (
            'name' => 'triaged_c',
            'label' => 'LBL_TRIAGED',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'product_category',
            'label' => 'LBL_PRODUCT_CATEGORY',
          ),
          1 => 
          array (
            'name' => 'internal_status_c',
            'label' => 'LBL_INTERNAL_STATUS',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'sugar_edition_c',
            'label' => 'Sugar_Edition_c',
          ),
          1 => 
          array (
            'name' => 'resolution',
            'label' => 'LBL_RESOLUTION',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'found_in_release',
            'label' => 'LBL_FOUND_IN_RELEASE',
          ),
          1 => 
          array (
            'name' => 'fixed_in_release',
            'label' => 'LBL_FIXED_IN_RELEASE',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'portal_name_c',
            'label' => 'Portal_Name_c',
          ),
          1 => 
          array (
            'name' => 'display_in_portal_c',
            'label' => 'Display_in_Portal__c',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'size' => 60,
              'required' => true,
            ),
            'label' => 'LBL_SUBJECT',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'work_log',
            'label' => 'LBL_WORK_LOG',
          ),
        ),
      ),
    ),
  ),
);
?>
