<?php
$searchdefs ['Contacts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'first_name' => 
      array (
        'name' => 'first_name',
        'default' => true,
        'width' => '10%',
      ),
      'last_name' => 
      array (
        'name' => 'last_name',
        'default' => true,
        'width' => '10%',
      ),
      'alt_lang_first_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'LBL_ALT_LANG_FIRST',
        'width' => '10%',
        'name' => 'alt_lang_first_c',
      ),
      'alt_lang_last_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'LBL_ALT_LANG_LAST',
        'width' => '10%',
        'name' => 'alt_lang_last_c',
      ),
      'account_name' => 
      array (
        'type' => 'relate',
        'link' => 'accounts',
        'label' => 'LBL_ACCOUNT_NAME',
        'width' => '10%',
        'default' => true,
        'name' => 'account_name',
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
