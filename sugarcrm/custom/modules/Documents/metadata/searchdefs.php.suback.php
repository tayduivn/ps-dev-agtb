<?php
$searchdefs ['Documents'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'document_name',
      1 => 'category_id',
      2 => 'subcategory_id',
    ),
    'advanced_search' => 
    array (
      'document_name' => 
      array (
        'name' => 'document_name',
        'default' => true,
      ),
      'category_id' => 
      array (
        'name' => 'category_id',
        'default' => true,
        'sortable' => false,
      ),
      'subcategory_id' => 
      array (
        'name' => 'subcategory_id',
        'default' => true,
        'sortable' => false,
      ),
      'team_name' => 
      array (
        'width' => '10%',
        'label' => 'LBL_TEAM',
        'default' => true,
        'name' => 'team_name',
      ),
      'active_date' => 
      array (
        'name' => 'active_date',
        'default' => true,
      ),
      'exp_date' => 
      array (
        'name' => 'exp_date',
        'default' => true,
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
