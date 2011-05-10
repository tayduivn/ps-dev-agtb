<?php
$module_name = 'DCEDataBases';
$viewdefs = array (
$module_name =>
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
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
        10 => 
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
        20 => 
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
        25 => 
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
        30 => 
        array (
          0 => 
          array (
            'name' => 'user_pass',
            'label' => 'LBL_USER_PASS',
          ),
        ),
        40 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        50 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
        ),
      ),
    ),
  ),
)
);
?>
