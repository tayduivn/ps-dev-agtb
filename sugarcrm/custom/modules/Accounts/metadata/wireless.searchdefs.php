<?php
$searchdefs ['Accounts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'alt_lang_name' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_ALT_ACCOUNT_NAME',
        'width' => '10%',
        'default' => true,
        'name' => 'alt_lang_name',
      ),
      'client_id' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_CLIENT_ID',
        'width' => '10%',
        'default' => true,
        'name' => 'client_id',
      ),
      'cmr_number' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_CMR_NUMBER',
        'width' => '10%',
        'default' => true,
        'name' => 'cmr_number',
      ),
      'billing_address_city' => 
      array (
        'type' => 'varchar',
        'label' => 'LBL_BILLING_ADDRESS_CITY',
        'width' => '10%',
        'default' => true,
        'name' => 'billing_address_city',
      ),
      'billing_address_country' => 
      array (
        'type' => 'enum',
        'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
        'sortable' => false,
        'width' => '10%',
        'default' => true,
        'name' => 'billing_address_country',
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
