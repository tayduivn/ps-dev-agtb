<?php
$module_name = 'P1_Partners';
$_object_name = 'p1_partners';
$viewdefs [$module_name] = 
array (
  'QuickCreate' => 
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
      'javascript' => '{$PROBABILITY_SCRIPT}',
    ),
    'panels' => 
    array (
      'lbl_sale_information' => 
      array (
        0 => 
        array (
          0 => 'name',
          1 => 'amount',
        ),
        1 => 
        array (
          0 => 'p1_partners_type',
          1 => 'date_closed',
        ),
        2 => 
        array (
          0 => 'lead_source',
          1 => 'next_step',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
          1 => 
          array (
            'name' => 'sales_stage',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
          1 => 'probability',
        ),
        5 => 
        array (
          0 => 'description',
          1 => '',
        ),
      ),
    ),
  ),
);
?>
