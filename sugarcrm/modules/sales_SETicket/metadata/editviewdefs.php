<?php
$module_name = 'sales_SETicket';
$_object_name = 'sales_seticket';
$viewdefs [$module_name] = 
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
            'name' => 'sales_seticket_number',
            'type' => 'readonly',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'tickettype',
            'studio' => 'visible',
            'label' => 'LBL_TICKETTYPE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'hoursspent',
            'label' => 'LBL_HOURSSPENT',
          ),
          1 => 
          array (
            'name' => 'recurrence',
            'studio' => 'visible',
            'label' => 'LBL_RECURRENCE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'comment' => 'The status of the issue',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'inperson',
            'label' => 'LBL_INPERSON',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'size' => 60,
            ),
          ),
          1 => 
          array (
            'name' => 'event',
            'label' => 'LBL_EVENT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'studio' => 'visible',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'objective',
            'label' => 'LBL_OBJECTIVE',
          ),
        ),
      ),
    ),
  ),
);
?>
