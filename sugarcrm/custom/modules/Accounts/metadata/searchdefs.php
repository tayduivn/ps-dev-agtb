<?php
$searchdefs ['Accounts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'current_user_only' => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
      ),
      'favorites_only' => 
      array (
        'name' => 'favorites_only',
        'label' => 'LBL_FAVORITES_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
      ),
    ),
    'advanced_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'customer_number_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'Customer Number',
        'width' => '10%',
        'name' => 'customer_number_c',
      ),
      'phone_office' => 
      array (
        'type' => 'phone',
        'label' => 'LBL_PHONE_OFFICE',
        'width' => '10%',
        'default' => true,
        'name' => 'phone_office',
      ),
      'account_kana_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'Account (Kana)',
        'width' => '10%',
        'name' => 'account_kana_c',
      ),
      'alt_lang_name' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_ALT_ACCOUNT_NAME',
        'width' => '10%',
        'default' => true,
        'name' => 'alt_lang_name',
      ),
      'legal_name_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'Legal Name',
        'width' => '10%',
        'name' => 'legal_name_c',
      ),
      'address_city' => 
      array (
        'name' => 'address_city',
        'label' => 'LBL_CITY',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'address_state' => 
      array (
        'name' => 'address_state',
        'label' => 'LBL_STATE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'address_postalcode' => 
      array (
        'name' => 'address_postalcode',
        'label' => 'LBL_POSTAL_CODE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'coverage_id_c' => 
      array (
        'type' => 'varchar',
        'default' => true,
        'label' => 'Coverage ID',
        'width' => '10%',
        'name' => 'coverage_id_c',
      ),
      'industry' => 
      array (
        'name' => 'industry',
        'default' => true,
        'width' => '10%',
      ),
      'billing_address_country' => 
      array (
        'name' => 'billing_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'enum',
        'options' => 'country_options',
        'default' => true,
        'width' => '10%',
      ),
      'tags' =>
      array (
        'name' => 'tags',
        'label' => 'LBL_TAGS',
        'type' => 'tag',
        'default' => true,
        'width' => '10%',
      ),
      'current_user_only' => 
      array (
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
        'name' => 'current_user_only',
      ),
      'favorites_only' => 
      array (
        'name' => 'favorites_only',
        'label' => 'LBL_FAVORITES_FILTER',
        'type' => 'bool',
        'default' => true,
        'width' => '10%',
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
