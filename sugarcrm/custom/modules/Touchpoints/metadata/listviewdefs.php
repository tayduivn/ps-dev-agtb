<?php
$listViewDefs ['Touchpoints'] = 
array (
  'FULL_NAME' => 
  array (
    'name' => 'full_name',
    'rname' => 'full_name',
    'vname' => 'LBL_NAME',
    'type' => 'name',
    'fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'sort_on' => 'last_name',
    'source' => 'non-db',
    'group' => 'last_name',
    'len' => '510',
    'db_concat_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'width' => '80%',
    'link' => true,
    'label' => 'LBL_NAME',
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'orderBy' => 'last_name',
    'default' => true,
  ),
  'SCORE' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCORE',
    'default' => true,
  ),
  'SCRUBBED' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCRUBBED',
    'default' => true,
  ),
  'CAMPAIGN_ID' => 
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_CAMPAIGN_NAME',
    'default' => true,
  ),
  'SCRUB_RESULT' => 
  array (
    'width' => '20%',
    'label' => 'LBL_SCRUB_RESULT',
    'default' => true,
    'customCode' => '<span {$SCRUBBED_STYLE}>{$SCRUB_RESULT}</span>',
  ),
  'ASSIGNED_USER_NAME' =>
  array (
    'width' => '10%',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => true,
  ),
  'TITLE' => 
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_TITLE',
    'default' => false,
  ),
  'COMPANY_NAME' => 
  array (
    'width' => '20%',
    'label' => 'LBL_COMPANY_NAME',
    'default' => false,
  ),
);
?>
