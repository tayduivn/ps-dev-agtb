<?php
$module_name = 'sales_SETicket';
$OBJECT_NAME = 'SALES_SETICKET';
$listViewDefs [$module_name] = 
array (
  'SALES_SETICKET_NUMBER' => 
  array (
    'width' => '5%',
    'label' => 'LBL_NUMBER',
    'link' => true,
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_SUBJECT',
    'default' => true,
    'link' => true,
  ),
  'STATUS' => 
  array (
    'width' => '10%',
    'label' => 'LBL_STATUS',
    'default' => true,
  ),
  'TICKETTYPE' => 
  array (
    'type' => 'multienum',
    'default' => true,
    'studio' => 'visible',
    'label' => 'LBL_TICKETTYPE',
    'width' => '10%',
  ),
  'EVENT' => 
  array (
    'type' => 'date',
    'label' => 'LBL_EVENT',
    'width' => '10%',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '9%',
    'label' => 'LBL_ASSIGNED_USER',
    'default' => true,
  ),
  'HOURSSPENT' => 
  array (
    'type' => 'decimal',
    'label' => 'LBL_HOURSSPENT',
    'width' => '10%',
    'default' => false,
  ),
  'INPERSON' => 
  array (
    'type' => 'bool',
    'label' => 'LBL_INPERSON',
    'width' => '10%',
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
