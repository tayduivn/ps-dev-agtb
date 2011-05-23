<?php
$searchdefs ['Opportunities'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'account_name' => 
      array (
        'type' => 'relate',
        'link' => 'accounts',
        'label' => 'LBL_ACCOUNT_NAME',
        'width' => '10%',
        'default' => true,
        'name' => 'account_name',
      ),
      'sales_stage' => 
      array (
        'type' => 'enum',
        'default' => true,
        'label' => 'LBL_SALES_STAGE',
        'sortable' => false,
        'width' => '10%',
        'name' => 'sales_stage',
      ),
      'description' => 
      array (
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'sortable' => false,
        'width' => '10%',
        'default' => true,
        'name' => 'description',
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '1',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
