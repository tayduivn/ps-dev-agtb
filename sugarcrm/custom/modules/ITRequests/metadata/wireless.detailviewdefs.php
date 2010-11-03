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
          'name' => 'system_id',
          'comment' => 'The offline client device that created the itrequest',
          'label' => 'LBL_SYSTEM_ID',
        ),
      ),
    ),
  ),
);
?>
