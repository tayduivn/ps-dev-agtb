<?php
$module_name = 'DiscountCodes';
$listViewDefs [$module_name] = 
array (
  'DISCOUNT_CODE' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_DISCOUNT_CODE',
    'width' => '10%',
    'default' => true,
    'link' => true,
  ),
  'DISCOUNT' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_DISCOUNT',
    'width' => '10%',
    'default' => true,
  ),
  'EXPIRES_ON' => 
  array (
    'type' => 'datetimecombo',
    'label' => 'LBL_EXPIRES_ON',
    'width' => '10%',
    'default' => true,
  ),
  'NUMBER_OF_USES' => 
  array (
    'type' => 'int',
    'label' => 'LBL_NUMBER_OF_USES',
    'width' => '10%',
    'default' => true,
  ),
  'STATUS' => 
  array (
    'type' => 'enum',
    'default' => true,
    'studio' => 'visible',
    'label' => 'LBL_STATUS',
    'sortable' => false,
    'width' => '10%',
  ),
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_NAME',
    'default' => false,
    'link' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => false,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_TEAM',
    'default' => false,
  ),
);
?>
