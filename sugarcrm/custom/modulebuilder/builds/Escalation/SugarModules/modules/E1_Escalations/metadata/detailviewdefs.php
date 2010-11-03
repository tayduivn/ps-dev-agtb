<?php
$module_name = 'E1_Escalations';
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
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'source',
            'label' => 'LBL_SOURCE',
          ),
          1 => 
          array (
            'name' => 'urgency',
            'label' => 'LBL_URGENCY',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'dateescalated',
            'label' => 'LBL_DATEESCALATED',
          ),
          1 => 
          array (
            'name' => 'datereviewed',
            'label' => 'LBL_DATEREVIEWED',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'businessimpact',
            'label' => 'LBL_BUSINESSIMPACT',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'escalationdetails',
            'label' => 'LBL_ESCALATIONDETAILS',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'reviewcomments',
            'label' => 'LBL_REVIEWCOMMENTS',
          ),
        ),
      ),
    ),
  ),
);
?>
