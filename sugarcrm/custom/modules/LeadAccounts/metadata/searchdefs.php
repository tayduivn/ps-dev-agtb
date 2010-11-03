<?php
$searchdefs ['LeadAccounts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'name',
      1 => 'converted',
      2 => 'lead_source',
      3 => 'status',
      4 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'label' => 'LBL_NAME',
        'default' => true,
      ),
      'phone' => 
      array (
        'name' => 'phone',
        'label' => 'LBL_ANY_PHONE',
        'type' => 'name',
        'default' => true,
      ),
      'lead_source' => 
      array (
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'default' => true,
      ),
      'email' => 
      array (
        'name' => 'email',
        'label' => 'LBL_ANY_EMAIL',
        'type' => 'name',
        'default' => true,
      ),
      'address_street' => 
      array (
        'name' => 'address_street',
        'label' => 'LBL_ANY_ADDRESS',
        'type' => 'name',
        'default' => true,
      ),
      'score' => 
      array (
        'name' => 'score',
        'default' => true,
        'width' => '10%',
        'label' => 'LBL_SCORE',
      ),
      'address_city' => 
      array (
        'name' => 'address_city',
        'label' => 'LBL_CITY',
        'type' => 'name',
        'default' => true,
      ),
      'address_state' => 
      array (
        'name' => 'address_state',
        'label' => 'LBL_STATE',
        'type' => 'name',
        'default' => true,
      ),
      'address_postalcode' => 
      array (
        'name' => 'address_postalcode',
        'label' => 'LBL_POSTAL_CODE',
        'type' => 'name',
        'default' => true,
      ),
      'region_c' => 
      array (
        'name' => 'region_c',
        'label' => 'Region_c',
        'default' => true,
      ),
      'billing_address_country' => 
      array (
        'name' => 'billing_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'enum',
        'options' => 'countries_dom',
        'default' => true,
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
        'default' => true,
      ),
      'call_back_c' => 
      array (
        'name' => 'call_back_c',
        'label' => 'call_back_c',
        'default' => true,
      ),
      'converted' => 
      array (
        'name' => 'converted',
        'label' => 'LBL_CONVERTED',
        'default' => true,
      ),
      'current_user_only' => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
        'default' => true,
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '2',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
