<?php
$module_name = 'E1_Escalations';
$searchdefs [$module_name] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'name',
      1 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
      ),
      'urgency' => 
      array (
        'width' => '10%',
        'label' => 'LBL_URGENCY',
        'sortable' => false,
        'default' => true,
        'name' => 'urgency',
      ),
      'source' => 
      array (
        'width' => '10%',
        'label' => 'LBL_SOURCE',
        'sortable' => false,
        'default' => true,
        'name' => 'source',
      ),
      'dateescalated' => 
      array (
        'width' => '10%',
        'label' => 'LBL_DATEESCALATED',
        'default' => true,
        'name' => 'dateescalated',
      ),
      'datereviewed' => 
      array (
        'width' => '10%',
        'label' => 'LBL_DATEREVIEWED',
        'default' => true,
        'name' => 'datereviewed',
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
