<?php
// created: 2010-10-06 01:04:28
$searchdefs['Contacts'] = array (
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
      0 => 'first_name',
      1 => 'last_name',
      2 => 'account_name',
      3 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
      4 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
    'advanced_search' => 
    array (
      0 => 
      array (
        'name' => 'first_name',
        'label' => 'LBL_FIRST_NAME',
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
        'name' => 'last_name',
        'label' => 'LBL_LAST_NAME',
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
        'name' => 'account_name',
        'label' => 'LBL_ACCOUNT_NAME',
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
        'name' => 'do_not_call',
        'label' => 'LBL_DO_NOT_CALL',
        'default' => true,
        'width' => '10%',
      ),
      9 => 
      array (
        'name' => 'assistant',
        'label' => 'LBL_ASSISTANT',
        'default' => true,
        'width' => '10%',
      ),
      10 => 
      array (
        'name' => 'address_postalcode',
        'label' => 'LBL_POSTAL_CODE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      11 => 
      array (
        'name' => 'primary_address_country',
        'label' => 'LBL_COUNTRY',
        'type' => 'name',
        'options' => 'countries_dom',
        'default' => true,
        'width' => '10%',
      ),
      12 => 
      array (
        'width' => '10%',
        'label' => 'LBL_PORTAL_NAME',
        'default' => true,
        'name' => 'portal_name',
      ),
      13 => 
      array (
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      14 => 
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
      15 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
  ),
);
?>
