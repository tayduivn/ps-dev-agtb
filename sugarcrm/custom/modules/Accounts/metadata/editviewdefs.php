<?php
$viewdefs ['Accounts'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'SAVE',
          1 => 'CANCEL',
        ),
      ),
      'maxColumns' => '2',
      'useTabs' => true,
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'includes' => 
      array (
        0 => 
        array (
          'file' => 'modules/Accounts/Account.js',
        ),
      ),
    ),
    'panels' => 
    array (
      'lbl_account_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
          1 => 
          array (
            'name' => 'alt_lang_name',
            'comment' => 'Account alternate language name',
            'label' => 'LBL_ALT_ACCOUNT_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
          ),
          1 => '',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'billing_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'billing',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),
          1 => 
          array (
            'name' => 'shipping_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'shipping',
              'copy' => 'billing',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),
        ),
        3 => 
        array (
          0 =>
          array (
            'name' => 'currency_id',
            'label' => 'LBL_CURRENCY',
          ),
          1 => 
          array (
            'name' => 'industry',
            'comment' => 'The company belongs in this industry',
            'label' => 'LBL_INDUSTRY',
          ),
        ),
        4 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'phone_office',
            'label' => 'LBL_PHONE_OFFICE',
            'customCode' => '<input name="phone_office" id="phone_office" size="30" maxlength="100" type="text" value="{$fields.phone_office.value}">&nbsp;&nbsp;<input type="hidden" name="phone_office_suppressed" value="0"> <input type="checkbox" name="phone_office_suppressed" id="phone_office_suppressed" value="1" {if $fields.phone_office_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'tags',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_PHONE_FAX',
            'customCode' => '<input name="phone_fax" id="phone_fax" size="30" maxlength="100" type="text" value="{$fields.phone_fax.value}">&nbsp;&nbsp;<input type="hidden" name="phone_fax_suppressed" value="0"> <input type="checkbox" name="phone_fax_suppressed" id="phone_fax_suppressed" value="1" {if $fields.phone_fax_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'parent_name',
            'label' => 'LBL_MEMBER_OF',
          ),
          1 => '',
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'language_c',
          ),
          1 => 
          array (
            'name' => 'customer_buying_behavior_c',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'account_status',
            'comment' => 'Account Status',
            'label' => 'LBL_ACCOUNT_STATUS',
          ),
          1 => 
          array (
            'name' => 'issuing_country_name_c',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
			1 => array(

			),
        ),

      ),
    ),
  ),
);
?>
