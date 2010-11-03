<?php
$viewdefs ['Tasks'] = 
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
            'customCode' => '{if $fields.status.value != "Completed"} <input type="hidden" name="isSaveAndNew" value="false">  <input type="hidden" name="status" value="">  <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  accesskey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}"  class="button"  onclick="this.form.status.value=\'Completed\'; this.form.action.value=\'Save\'; this.form.return_module.value=\'Tasks\'; this.form.isDuplicate.value=true; this.form.isSaveAndNew.value=true; this.form.return_action.value=\'EditView\'; this.form.isDuplicate.value=true; this.form.return_id.value=\'{$fields.id.value}\';"  name="button"  value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
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
            'name' => 'name',
            'label' => 'LBL_SUBJECT',
          ),
          1 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'date_start',
            'label' => 'LBL_START_DATE',
          ),
          1 => 
          array (
            'name' => 'parent_name',
            'label' => '{$fields.parent_type.value}',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'date_due',
            'label' => 'LBL_DUE_DATE',
          ),
          1 => 
          array (
            'name' => 'contact_name',
            'label' => 'LBL_CONTACT',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'priority',
            'label' => 'LBL_PRIORITY',
          ),
          1 => 
          array (
            'name' => 'modified_by_name',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
        4 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'created_by_name',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'contact_phone',
            'type' => 'phone',
            'label' => 'LBL_PHONE',
          ),
          1 => 
          array (
            'name' => 'contact_email',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'task_type_c',
            'label' => 'LBL_TASK_TYPE',
          ),
          1 => 
          array (
            'name' => 'task_completion_c',
            'studio' => 'visible',
            'label' => 'LBL_TASK_COMPLETION',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'estimated_effort_c',
            'label' => 'LBL_ESTIMATED_EFFORT',
          ),
          1 => 
          array (
            'name' => 'actual_effort_c',
            'label' => 'LBL_ACTUAL_EFFORT',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'sales_seticket_activities_tasks_name',
          ),
        ),
      ),
      'lbl_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'sales_management_approval_c',
            'label' => 'LBL_SALES_MANAGEMENT_APPROVAL_C',
          ),
        ),
      ),
    ),
  ),
);
?>
