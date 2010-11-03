<?php
$module_name = 'Orders';
$listViewDefs [$module_name] = 
array (
  'ORDER_ID' => 
  array (
    'type' => 'int',
    'label' => 'LBL_ORDER_ID',
    'width' => '10%',
    'default' => true,
  ),
  'USERNAME' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_USERNAME',
    'width' => '10%',
    'default' => true,
  ),
  'DATE_ENTERED' => 
  array (
    'type' => 'datetime',
    'label' => 'LBL_DATE_ENTERED',
    'width' => '10%',
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'DESCRIPTION' => 
  array (
    'type' => 'text',
    'label' => 'LBL_DESCRIPTION',
    'sortable' => false,
    'width' => '10%',
    'default' => true,
  ),
  'TOTAL' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_TOTAL',
    'width' => '10%',
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_TEAM',
    'default' => false,
  ),
);
?>
