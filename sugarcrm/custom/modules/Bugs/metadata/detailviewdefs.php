<?php
$viewdefs ['Bugs'] = 
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
          3 => 
          array (
            'customCode' => '<input title="{$APP.LBL_DUP_MERGE}"                     accesskey="M"                     class="button"                     onclick="this.form.return_module.value=\'Bugs\';this.form.return_action.value=\'DetailView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Step1\'; this.form.module.value=\'MergeRecords\';"                     name="button"                     value="{$APP.LBL_DUP_MERGE}"                     type="submit">',
          ),
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
      '' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'bug_number',
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
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
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
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'source',
            'label' => 'LBL_SOURCE',
          ),
          1 => 
          array (
            'name' => 'regression_c',
            'label' => 'LBL_REGRESSION',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'product_category',
            'label' => 'LBL_PRODUCT_CATEGORY',
          ),
          1 => 
          array (
            'name' => 'portal_name_c',
            'label' => 'Portal_Name_c',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'subcategory_c',
            'label' => 'LBL_SUBCATEGORY',
          ),
          1 => 
          array (
            'name' => 'display_in_portal_c',
            'label' => 'Display_in_Portal__c',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'product_c',
            'studio' => 'visible',
            'label' => 'LBL_PRODUCT',
          ),
          1 => 
          array (
            'name' => 'triaged_c',
            'label' => 'LBL_TRIAGED',
          ),
        ),
        8 => 
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
        9 => 
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
        10 => 
        array (
          0 => 
          array (
            'name' => 'name',
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
      'lbl_detailview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'internal_status_c',
            'label' => 'LBL_INTERNAL_STATUS',
          ),
          1 => 
          array (
            'name' => 'requirements_status_c',
            'studio' => 'visible',
            'label' => 'LBL_REQUIREMENTS_STATUS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'feature_backlog_priority_num_c',
            'label' => 'LBL_FEATURE_BACKLOG_PRIORITY_NUM',
          ),
          1 => 
          array (
            'name' => 'feature_backlog_group_c',
            'studio' => 'visible',
            'label' => 'LBL_FEATURE_BACKLOG_GROUP',
          ),
        ),
      ),
      'lbl_detailview_panel2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'estimated_time_spent_c',
            'label' => 'LBL_ESTIMATED_TIME_SPENT',
          ),
          1 => 
          array (
            'name' => 'due_date_c',
            'label' => 'LBL_DUE_DATE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'actual_time_spent_c',
            'label' => 'LBL_ACTUAL_TIME_SPENT',
          ),
        ),
      ),
      'lbl_detailview_panel3' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'code_impacts_c',
            'label' => 'LBL_CODE_IMPACTS',
          ),
          1 => 
          array (
            'name' => 'ui_changes_c',
            'label' => 'LBL_UI_CHANGES_C',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'fix_proposed_c',
            'label' => 'LBL_FIX_PROPOSED',
          ),
          1 => 
          array (
            'name' => 'release_notes_c',
            'label' => 'release_notes_c',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'contribution_agreement_c',
            'label' => 'LBL_CONTRIBUTION_AGREEMENT',
          ),
          1 => 
          array (
            'name' => 'souceforge_id',
            'label' => 'LBL_SOUCEFORGE_ID',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'code_review_complete_c',
            'label' => 'LBL_CODE_REVIEW_COMPLETE',
          ),
          1 => '',
        ),
      ),
    ),
  ),
);
?>
