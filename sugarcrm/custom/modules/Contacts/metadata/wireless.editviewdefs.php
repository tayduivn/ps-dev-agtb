<?php
$viewdefs ['Contacts'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '1',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      0 => 
      array (
        0 => 
        array (
          'name' => 'first_name',
          'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="15" maxlength="25" type="text" value="{$fields.first_name.value}">',
          'displayParams' => 
          array (
            'wireless_edit_only' => true,
          ),
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'name' => 'last_name',
          'displayParams' => 
          array (
            'required' => true,
            'wireless_edit_only' => true,
          ),
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'alt_lang_first_c',
          'label' => 'LBL_ALT_LANG_FIRST',
        ),
      ),
      3 => 
      array (
        0 => 
        array (
          'name' => 'alt_lang_last_c',
          'label' => 'LBL_ALT_LANG_LAST',
        ),
      ),
      4 => 
      array (
        0 => 'title',
      ),
      5 => 
      array (
        0 => 'account_name',
      ),
      6 => 
      array (
        0 => 'phone_work',
      ),
      7 => 
      array (
        0 => 'phone_mobile',
      ),
      8 => 
      array (
        0 => 'email1',
      ),
      9 => 
      array (
        0 => '',
      ),
      10 => 
      array (
        0 => 'primary_address_street',
      ),
      11 => 
      array (
        0 => 'primary_address_city',
      ),
      12 => 
      array (
        0 => 'primary_address_state',
      ),
      13 => 
      array (
        0 => 'primary_address_postalcode',
      ),
      14 => 
      array (
        0 => 'primary_address_country',
      ),
      15 => 
      array (
        0 => '',
      ),
      16 => 
      array (
        0 => 
        array (
          'name' => 'alt_address_street',
          'comment' => 'Street address for alternate address',
          'label' => 'LBL_ALT_ADDRESS_STREET',
        ),
      ),
      17 => 
      array (
        0 => 
        array (
          'name' => 'alt_address_city',
          'comment' => 'City for alternate address',
          'label' => 'LBL_ALT_ADDRESS_CITY',
        ),
      ),
      18 => 
      array (
        0 => 
        array (
          'name' => 'alt_address_state',
          'comment' => 'State for alternate address',
          'label' => 'LBL_ALT_ADDRESS_STATE',
        ),
      ),
      19 => 
      array (
        0 => 
        array (
          'name' => 'alt_address_postalcode',
          'comment' => 'Postal code for alternate address',
          'label' => 'LBL_ALT_ADDRESS_POSTALCODE',
        ),
      ),
      20 => 
      array (
        0 => 
        array (
          'name' => 'alt_address_country',
          'comment' => 'Country for alternate address',
          'label' => 'LBL_ALT_ADDRESS_COUNTRY',
        ),
      ),
      21 => 
      array (
        0 => 'team_name',
      ),
      22 => 
      array (
        0 => 'tags',
      ),
    ),
  ),
);
?>
