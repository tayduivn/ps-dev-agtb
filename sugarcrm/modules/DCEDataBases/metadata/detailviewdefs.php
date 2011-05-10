<?php
$module_name = 'DCEDataBases';
$viewdefs = array (
$module_name =>
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
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
          1 => 
          array (
            'name' => 'cluster_name',
            'label' => 'LBL_CLUSTER',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'host',
            'label' => 'LBL_HOST',
          ),
          1 => 
          array (
            'name' => 'primary_role',
            'label' => 'LBL_PRIMARY_ROLE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'user_name',
            'label' => 'LBL_USER_NAME',
          ),
          1 => 
          array (
            'name' => 'reports_role',
            'label' => 'LBL_REPORTS_ROLE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'user_pass',
            'label' => 'LBL_USER_PASS',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
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
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
      ),
    ),
  ),
)
);
?>
