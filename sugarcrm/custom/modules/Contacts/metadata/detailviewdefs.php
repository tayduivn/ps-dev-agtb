<?php
$viewdefs ['Contacts'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 'FIND_DUPLICATES',
          4 => 
          array (
            'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
          ),
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
          'file' => 'modules/Leads/Lead.js',
        ),
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
              'enableConnectors' => true,
              'module' => 'Contacts',
              'connectors' => 
              array (
                0 => 'ext_rest_twitter',
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
            'name' => 'job_function_c',
            'studio' => 'visible',
            'label' => 'LBL_JOB_FUNCTION',
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
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
            'customCode' => '{if $fields.phone_mobile_suppressed.value == "1"}<strike>{/if}{$fields.phone_mobile.value}{if $fields.phone_mobile_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'key_contact_c',
            'studio' => 'visible',
            'label' => 'LBL_KEY_CONTACT',
          ),
          1 => 
          array (
            'name' => 'email1',
            'studio' => 'false',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'language_c',
            'studio' => 'visible',
            'label' => 'LBL_LANGUAGE',
          ),
          1 => 
          array (
            'name' => 'timezone_c',
            'studio' => 'visible',
            'label' => 'LBL_TIMEZONE',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'contact_status_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT_STATUS',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
            ),
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALTERNATE_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
            ),
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
            'displayParams' => 
            array (
              'enableConnectors' => true,
              'module' => 'Contacts',
              'connectors' => 
              array (
                0 => 'ext_rest_linkedin',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'tags',
          ),
        ),
		8 => array(

          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),

			1 => array(

			),

			2 => array(
	            'name' => 'NEW_PANEL',
	            'label' => 'LBL_PANEL_ADVANCED',
	            'default' => 'false',
			),

		),

        9 => 
        array (
          0 => 
          array (
            'name' => 'assistant_name_c',
            'label' => 'LBL_ASSISTANT_NAME',
          ),
          1 => 
          array (
            'name' => 'assistant_number_c',
            'label' => 'LBL_ASSISTANT_NUMBER',
            'customCode' => '{if $fields.assistant_number_c_suppressed.value == "1"}<strike>{/if}{$fields.assistant_number_c.value}{if $fields.assistant_number_c_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
        10 => 
        array (
			0 => array(

			),
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
          1 => 
          array (
            'name' => 'scmn_c',
            'label' => 'LBL_SCMN',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'report_to_name',
            'label' => 'LBL_REPORTS_TO',
          ),
          1 => 
          array (
            'name' => 'confidence_grade_c',
            'label' => 'LBL_CONFIDENCE_GRADE',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'last_interaction_date_c',
            'label' => 'LBL_LAST_INTERACTION_DATE',
          ),
          1 => 
          array (
            'name' => 'contact_interest_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT_INTEREST',
          ),
        ),
		14 => array(
          0 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),

			1 => array(

			),

		),
		15 => array(

          0 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),


		),
        16 => 
        array (
          0 => 
          array (
            'name' => 'portal_name',
            'customCode' => '{if $PORTAL_ENABLED}{$fields.portal_name.value}{else}&nbsp;{/if}',
            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_NAME" module="Contacts"}{else}&nbsp;{/if}',
          ),
          1 => 
          array (
            'name' => 'portal_active',
            'customCode' => '{if $PORTAL_ENABLED}
		          		         {if strval($fields.portal_active.value) == "1" || strval($fields.portal_active.value) == "yes" || strval($fields.portal_active.value) == "on"}
		          		         {assign var="checked" value="CHECKED"}
	                             {else}
	                             {assign var="checked" value=""}
	                             {/if}
	                             <input type="checkbox" class="checkbox" name="{$fields.portal_active.name}" size="{$displayParams.size}" disabled="true" {$checked}>
	                             {else}&nbsp;{/if}',
            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_ACTIVE" module="Contacts"}{else}&nbsp;{/if}',
          ),
        ),
      ),


    ),
  ),
);
?>
