<?php
$viewdefs ['ITRequests'] = 
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
            'name' => 'itrequest_number',
            'comment' => 'Visible itrequest identifier',
            'label' => 'LBL_NUMBER',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'priority',
            'comment' => 'The priority of the itrequest',
            'label' => 'LBL_PRIORITY',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'comment' => 'The status of the itrequest',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'escalation_c',
            'label' => 'LBL_ESCALATION',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'target_date',
            'comment' => 'This is the targeted completion date for the request',
            'label' => 'LBL_TARGET_DATE',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'start_date',
            'comment' => 'This is the targeted start date for the request',
            'label' => 'LBL_START_DATE',
          ),
          1 => '',
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'development_time',
            'customCode' => '{if $fields.development_time.value != ""}{$fields.development_time.value} hours{/if}',
          ),
          1 => '',
        ),
        6 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'date_resolved',
            'comment' => 'Date record was resolved',
            'label' => 'LBL_DATE_RESOLVED',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'department_c',
            'studio' => 'visible',
            'label' => 'LBL_DEPARTMENT',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} by <a href="/index.php?module=Employees&action=DetailView&record={$fields.modified_user_id.value}">{$fields.modified_by_name.value}</a>',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'department_category_c',
            'studio' => 'visible',
            'label' => 'LBL_DEPARTMENT_CATEGORY',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} by <a href="/index.php?module=Employees&action=DetailView&record={$fields.created_by.value}">{$fields.created_user_name.value}</a>',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'comment' => 'The subject of the itrequest',
            'label' => 'LBL_LIST_SUBJECT',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'The itrequest description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'resolution',
            'comment' => 'The resolution of the IT Request',
            'label' => 'LBL_RESOLUTION',
            'customCode' => '{$fields.resolution.value|html_entity_decode|nl2br}'
          ),
        ),
      ),
    ),
  ),
);
?>
