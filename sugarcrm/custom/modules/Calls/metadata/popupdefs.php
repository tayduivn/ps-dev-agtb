<?php
$popupMeta = array (
    'moduleMain' => 'Calls',
    'varName' => 'Call',
    'orderBy' => 'calls.name',
    'whereClauses' => array (
  'name' => 'calls.name',
  'current_user_only' => 'calls.current_user_only',
  'direction' => 'calls.direction',
  'status' => 'calls.status',
  'assigned_user_id' => 'calls.assigned_user_id',
  0 => 'calls.0',
),
    'searchInputs' => array (
  1 => 'name',
  3 => 'status',
  4 => 'current_user_only',
  5 => 'direction',
  6 => 'assigned_user_id',
),
    'searchdefs' => array (
  'name' => 
  array (
    'name' => 'name',
    'width' => '10%',
  ),
  'current_user_only' => 
  array (
    'name' => 'current_user_only',
    'label' => 'LBL_CURRENT_USER_FILTER',
    'type' => 'bool',
    'width' => '10%',
  ),
  'direction' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_DIRECTION',
    'width' => '10%',
    'name' => 'direction',
  ),
  'status' => 
  array (
    'name' => 'status',
    'width' => '10%',
  ),
  'assigned_user_id' => 
  array (
    'name' => 'assigned_user_id',
    'type' => 'enum',
    'label' => 'LBL_ASSIGNED_TO',
    'function' => 
    array (
      'name' => 'get_user_array',
      'params' => 
      array (
        0 => false,
      ),
    ),
    'width' => '10%',
  ),
  0 => 
  array (
    'name' => 'favorites_only',
    'label' => 'LBL_FAVORITES_FILTER',
    'type' => 'bool',
    'width' => '10%',
  ),
),
);
