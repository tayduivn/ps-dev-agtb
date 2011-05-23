<?php
$viewdefs ['Contacts'] =
array (
  'Livetextview' =>
  array (
    'templateMeta' =>
    array (
      'form' =>
      array (
        'buttons' =>
        array (
        ),
      ),
      'maxColumns' => '2',
      'useTabs' => false,
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
      ),
    ),
    'panels' => 
    array (
      'lbl_contact_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'full_name',
            'label' => 'LBL_NAME',
            'displayParams' => 
            array (
              'enableConnectors' => false,
              'module' => 'Contacts',
              'connectors' => 
              array (
              ),
            ),
          ),
          1 => array(
			'name' => 'alt_lang_first_c',
			'label' => 'LBL_ALT_LANG_NAME',
			'customCode' => '{$fields.alt_lang_first_c.value} {$fields.alt_lang_middle_c.value} {$fields.alt_lang_last_c.value}',

			),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'title',
            'comment' => 'The title of the contact',
            'label' => 'LBL_TITLE',
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
            'displayParams' => 
            array (
              'enableConnectors' => false,
              'module' => 'Contacts',
              'connectors' => 
              array (
              ),
            ),
          ),

        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
            'customCode' => '{if $fields.phone_work_suppressed.value == "1"}<strike>{/if}{$fields.phone_work.value}{if $fields.phone_work_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
          1 => 
          array (
            'name' => 'email1',
            'studio' => 'false',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
            'customCode' => '{if $fields.phone_mobile_suppressed.value == "1"}<strike>{/if}{$fields.phone_mobile.value}{if $fields.phone_mobile_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
          1 => 
          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
            ),
          ),

        ),
        10 => 
        array (
          1 => 
          array (
            'name' => 'home_phone_c',
            'label' => 'LBL_HOME_PHONE',
            'customCode' => '{if $fields.home_phone_c_suppressed.value == "1"}<strike>{/if}{$fields.home_phone_c.value}{if $fields.home_phone_c_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'alternate_phone_c',
            'label' => 'LBL_ALTERNATE_PHONE',
            'customCode' => '{if $fields.alternate_phone_c_suppressed.value == "1"}<strike>{/if}{$fields.alternate_phone_c.value}{if $fields.alternate_phone_c_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
		14 => array(
          0 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),

			1 => array(
				'customLabel' => '<a href="index.php?module=Contacts&action=DetailView&record={$fields.id.value}" target="_blank">View full record</a>',

			),

		),
      ),


    ),
  ),
);
?>
