<?php
$module_name = 'E1_Escalations';
$listViewDefs [$module_name] = 
array (
  'NAME' => 
  array (
    'width' => '32%',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'SOURCE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_SOURCE',
    'sortable' => false,
    'default' => true,
  ),
  'URGENCY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_URGENCY',
    'sortable' => false,
    'default' => true,
  ),
  'DATEESCALATED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATEESCALATED',
    'default' => true,
  ),
  'DATEREVIEWED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATEREVIEWED',
    'default' => true,
  ),
  'ESCALATIONDETAILS' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ESCALATIONDETAILS',
    'sortable' => false,
    'default' => false,
  ),
  'REVIEWCOMMENTS' => 
  array (
    'width' => '10%',
    'label' => 'LBL_REVIEWCOMMENTS',
    'sortable' => false,
    'default' => false,
  ),
  'BUSINESSIMPACT' => 
  array (
    'width' => '10%',
    'label' => 'LBL_BUSINESSIMPACT',
    'sortable' => false,
    'default' => false,
  ),
);
?>
