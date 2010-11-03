<?php
$searchdefs ['Contacts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
      2 => 'account_name',
      3 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'first_name' => 
      array (
        'name' => 'first_name',
        'label' => 'LBL_FIRST_NAME',
        'default' => true,
        'width' => '10%',
      ),
      'address_street' => 
      array (
        'name' => 'address_street',
        'label' => 'LBL_ANY_ADDRESS',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'phone' => 
      array (
        'name' => 'phone',
        'label' => 'LBL_ANY_PHONE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'last_name' => 
      array (
        'name' => 'last_name',
        'label' => 'LBL_LAST_NAME',
        'default' => true,
        'width' => '10%',
      ),
      'address_city' => 
      array (
        'name' => 'address_city',
        'label' => 'LBL_CITY',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'email' => 
      array (
        'name' => 'email',
        'label' => 'LBL_ANY_EMAIL',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'account_name' => 
      array (
        'name' => 'account_name',
        'label' => 'LBL_ACCOUNT_NAME',
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
      'do_not_call' => 
      array (
        'name' => 'do_not_call',
        'label' => 'LBL_DO_NOT_CALL',
        'default' => true,
        'width' => '10%',
      ),
      'assistant' => 
      array (
        'name' => 'assistant',
        'label' => 'LBL_ASSISTANT',
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
      'primary_address_country' => 
      array (
        'name' => 'primary_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'name',
        'options' => 'countries_dom',
        'default' => true,
        'width' => '10%',
      ),
      'portal_name' => 
      array (
        'width' => '10%',
        'label' => 'LBL_PORTAL_NAME',
        'default' => true,
        'name' => 'portal_name',
      ),
      'lead_source' => 
      array (
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
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
        'sortable' => false,
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
