<?php
$viewdefs ['Accounts'] = 
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
        0 => 'name',
      ),
      1 => 
      array (
        0 => 'phone_office',
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'website',
          'type' => 'link',
        ),
      ),
      3 => 
      array (
        0 => 'email1',
      ),
      4 => 
      array (
        0 => 'assigned_user_name',
      ),
      5 => 
      array (
        0 => 'team_name',
      ),
    ),
  ),
);
?>
