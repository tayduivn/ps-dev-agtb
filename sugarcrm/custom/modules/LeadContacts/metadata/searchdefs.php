<?php
$searchdefs ['LeadContacts'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
      2 => 'converted',
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
      ),
      'phone' => 
      array (
        'name' => 'phone',
        'label' => 'LBL_ANY_PHONE',
        'type' => 'name',
        'default' => true,
      ),
      'last_name' => 
      array (
        'name' => 'last_name',
        'label' => 'LBL_LAST_NAME',
        'default' => true,
      ),
      'email' => 
      array (
        'name' => 'email',
        'label' => 'LBL_ANY_EMAIL',
        'type' => 'name',
        'default' => true,
      ),
      'score' => 
      array (
        'width' => '10%',
        'label' => 'LBL_SCORE',
        'default' => true,
        'name' => 'score',
      ),
      'title' => 
      array (
        'name' => 'title',
        'label' => 'LBL_TITLE',
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
      'address_street' => 
      array (
        'name' => 'address_street',
        'label' => 'LBL_ANY_ADDRESS',
        'type' => 'name',
        'default' => true,
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
      'primary_address_country' => 
      array (
        'name' => 'primary_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'enum',
        'options' => 'countries_dom',
        'default' => true,
      ),
      'converted' => 
      array (
        'name' => 'converted',
        'label' => 'LBL_CONVERTED',
        'default' => true,
      ),
      'status' => 
      array (
        'name' => 'status',
        'label' => 'LBL_STATUS',
        'default' => true,
      ),
      'lead_group_c' => 
      array (
        'name' => 'lead_group_c',
        'label' => 'Lead_Group_c',
        'type' => 'enum',
        'options' => 'lead_group_dom',
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
