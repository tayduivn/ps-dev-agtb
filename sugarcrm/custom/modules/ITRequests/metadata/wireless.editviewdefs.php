<?php
$viewdefs ['ITRequests'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '1',
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
      0 => 
      array (
        0 => 
        array (
          'name' => 'priority',
          'comment' => 'The priority of the itrequest',
          'label' => 'LBL_PRIORITY',
        ),
      ),
      1 => 
      array (
        0 => 'assigned_user_name',
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'category',
          'comment' => 'The category of the itrequest',
          'label' => 'LBL_CATEGORY',
        ),
      ),
      3 => 
      array (
        0 => 'team_name',
      ),
      4 => 
      array (
        0 => 'name',
      ),
      5 => 
      array (
        0 => 
        array (
          'name' => 'description',
          'comment' => 'The itrequest description',
          'label' => 'LBL_DESCRIPTION',
        ),
      ),
      6 => 
      array (
        0 => 
        array (
          'name' => 'resolution',
          'comment' => 'The resolution of the IT Request',
          'label' => 'LBL_RESOLUTION',
        ),
      ),
    ),
  ),
);
?>
