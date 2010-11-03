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
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'status_c',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'type_c',
            'studio' => 'visible',
            'label' => 'LBL_TYPE',
          ),
        ),
        2 => 
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
        3 => 
        array (
          0 => 
          array (
            'name' => 'created_by_name',
            'label' => 'LBL_CREATED',
          ),
          1 => 
          array (
            'name' => 'modified_by_name',
            'label' => 'LBL_MODIFIED_NAME',
          ),
        ),
        4 => 
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
        5 => 
        array (
          0 => 
          array (
            'name' => 'businessimpact',
            'label' => 'LBL_BUSINESSIMPACT',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'escalationdetails',
            'label' => 'LBL_ESCALATIONDETAILS',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'reviewcomments',
            'label' => 'LBL_REVIEWCOMMENTS',
          ),
          1 => 
          array (
            'name' => 'bugs_e1_escalations_name',
            'label' => 'LBL_BUGS_E1_ESCALATIONS_FROM_BUGS_TITLE',
          ),
        ),
      ),
    ),
  ),
);
?>
