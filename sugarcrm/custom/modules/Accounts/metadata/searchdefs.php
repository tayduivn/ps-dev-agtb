<?php
// created: 2010-10-06 01:04:21
$searchdefs['Accounts'] = array (
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
      0 => 
      array (
        'name' => 'name',
        'label' => 'LBL_NAME',
        'default' => true,
      ),
      1 => 
      array (
        'name' => 'billing_address_city',
        'label' => 'LBL_BILLING_ADDRESS_CITY',
        'default' => true,
      ),
      2 => 
      array (
        'name' => 'phone_office',
        'label' => 'LBL_PHONE_OFFICE',
        'default' => true,
      ),
      3 => 
      array (
        'name' => 'address_street',
        'label' => 'LBL_BILLING_ADDRESS',
        'type' => 'name',
        'group' => 'billing_address_street',
        'default' => true,
      ),
      4 => 
      array (
        'width' => '10%',
        'label' => 'LBL_WEBSITE',
        'default' => true,
        'name' => 'website',
      ),
      5 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
      ),
      6 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
    'advanced_search' => 
    array (
      0 => 
      array (
        'name' => 'name',
        'label' => 'LBL_NAME',
        'default' => true,
        'width' => '10%',
      ),
      1 => 
      array (
        'name' => 'address_street',
        'label' => 'LBL_ANY_ADDRESS',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      2 => 
      array (
        'name' => 'phone',
        'label' => 'LBL_ANY_PHONE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      3 => 
      array (
        'name' => 'website',
        'label' => 'LBL_WEBSITE',
        'default' => true,
        'width' => '10%',
      ),
      4 => 
      array (
        'name' => 'address_city',
        'label' => 'LBL_CITY',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      5 => 
      array (
        'name' => 'email',
        'label' => 'LBL_ANY_EMAIL',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      6 => 
      array (
        'name' => 'annual_revenue',
        'label' => 'LBL_ANNUAL_REVENUE',
        'default' => true,
        'width' => '10%',
      ),
      7 => 
      array (
        'name' => 'address_state',
        'label' => 'LBL_STATE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      8 => 
      array (
        'name' => 'employees',
        'label' => 'LBL_EMPLOYEES',
        'default' => true,
        'width' => '10%',
      ),
      9 => 
      array (
        'name' => 'address_postalcode',
        'label' => 'LBL_POSTAL_CODE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      10 => 
      array (
        'name' => 'billing_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'enum',
        'options' => 'countries_dom',
        'default' => true,
        'width' => '10%',
      ),
      11 => 
      array (
        'name' => 'ticker_symbol',
        'label' => 'LBL_TICKER_SYMBOL',
        'default' => true,
        'width' => '10%',
      ),
      12 => 
      array (
        'name' => 'sic_code',
        'label' => 'LBL_SIC_CODE',
        'default' => true,
        'width' => '10%',
      ),
      13 => 
      array (
        'name' => 'rating',
        'label' => 'LBL_RATING',
        'default' => true,
        'width' => '10%',
      ),
      14 => 
      array (
        'name' => 'ownership',
        'label' => 'LBL_OWNERSHIP',
        'default' => true,
        'width' => '10%',
      ),
      15 => 
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
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      16 => 
      array (
        'name' => 'account_type',
        'label' => 'LBL_TYPE',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      17 => 
      array (
        'name' => 'industry',
        'label' => 'LBL_INDUSTRY',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      18 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
  ),
);
?>
