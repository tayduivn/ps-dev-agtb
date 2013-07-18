<?php
$viewdefs['portal']['layout']['list'] = array (
  'components' => array (
    array (
      'view' => 'list-headerpane',
    ),
    array (
      'view' => 'filter',
    ),
    array (
      'view' => 'list',
      'primary' => true,
    ),
    array (
      'view' => 'list-bottom',
    ),
  ),
  'type' => 'simple',
  'name' => 'list',
  'span' => 12,
);
