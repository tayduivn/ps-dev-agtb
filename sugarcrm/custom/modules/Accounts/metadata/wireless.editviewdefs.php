<?php
$viewdefs ['Accounts'] = 
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
          'name' => 'name',
          'displayParams' => 
          array (
            'required' => true,
            'wireless_edit_only' => true,
          ),
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'name' => 'alt_lang_name',
          'comment' => 'Account alternate language name',
          'label' => 'LBL_ALT_ACCOUNT_NAME',
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'parent_name',
          'label' => 'LBL_MEMBER_OF',
        ),
      ),
      3 => 
      array (
        0 => 'phone_office',
      ),
      4 => 
      array (
        0 => 
        array (
          'name' => 'website',
          'displayParams' => 
          array (
            'type' => 'link',
          ),
        ),
      ),
      5 => 
      array (
        0 => 'email1',
      ),
      6 => 
      array (
        0 => 'billing_address_street',
      ),
      7 => 
      array (
        0 => 'billing_address_city',
      ),
      8 => 
      array (
        0 => 'billing_address_state',
      ),
      9 => 
      array (
        0 => 'billing_address_postalcode',
      ),
      10 => 
      array (
        0 => 'billing_address_country',
      ),
      11 => 
      array (
        0 => 
        array (
          'name' => 'account_status',
          'comment' => 'Account Status',
          'label' => 'LBL_ACCOUNT_STATUS',
        ),
      ),
      12 => 
      array (
        0 => 'team_name',
      ),
      13 => 
      array (
        0 => 'tags',
      ),
    ),
  ),
);
?>
