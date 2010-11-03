<?php
// created: 2010-10-06 01:04:28
$searchdefs['Documents'] = array (
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'document_name',
      1 => 'category_id',
      2 => 'subcategory_id',
      3 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
    'advanced_search' => 
    array (
      0 => 
      array (
        'name' => 'document_name',
        'default' => true,
      ),
      1 => 
      array (
        'name' => 'category_id',
        'default' => true,
        'sortable' => false,
      ),
      2 => 
      array (
        'name' => 'subcategory_id',
        'default' => true,
        'sortable' => false,
      ),
      3 => 
      array (
        'width' => '10%',
        'label' => 'LBL_TEAM',
        'default' => true,
        'name' => 'team_name',
      ),
      4 => 
      array (
        'name' => 'active_date',
        'default' => true,
      ),
      5 => 
      array (
        'name' => 'exp_date',
        'default' => true,
      ),
      6 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
  ),
);
?>
