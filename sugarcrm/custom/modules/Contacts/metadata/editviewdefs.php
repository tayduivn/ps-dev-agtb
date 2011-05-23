<?php
$viewdefs ['Contacts'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'hidden' => 
        array (
          0 => '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
          1 => '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
          2 => '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
          3 => '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
          4 => '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
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
    ),
    'panels' => 
    array (
      'lbl_contact_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'first_name',
            'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
          ),
          1 => 
          array (
            'name' => 'alt_lang_first_c',
            'label' => 'LBL_ALT_LANG_FIRST',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'last_name',
          ),

          1 => 
          array (
            'name' => 'alt_lang_middle_c',
            'label' => 'LBL_ALT_LANG_MIDDLE',
          ),

        ),
		2 => array(

			0 => array(

			),
          1 => 
          array (
            'name' => 'alt_lang_last_c',
            'label' => 'LBL_ALT_LANG_LAST',
          ),

		),
        3 => 
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
        4 => 
        array (
          0 => 
          array (
            'name' => 'phone_work',
            'comment' => 'Work phone number of the contact',
            'label' => 'LBL_OFFICE_PHONE',
            'customCode' => '<input name="phone_work" id="phone_work" size="30" maxlength="100" type="text" value="{$fields.phone_work.value}">&nbsp;&nbsp;<input type="hidden" name="phone_work_suppressed" value="0"> <input type="checkbox" name="phone_work_suppressed" id="phone_work_suppressed" value="1" {if $fields.phone_work_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
          1 => 
          array (
            'name' => 'phone_mobile',
            'comment' => 'Mobile phone number of the contact',
            'label' => 'LBL_MOBILE_PHONE',
            'customCode' => '<input name="phone_mobile" id="phone_mobile" size="30" maxlength="100" type="text" value="{$fields.phone_mobile.value}">&nbsp;&nbsp;<input type="hidden" name="phone_mobile_suppressed" value="0"> <input type="checkbox" name="phone_mobile_suppressed" id="phone_mobile_suppressed" value="1" {if $fields.phone_mobile_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
        ),
        5 => 
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
        6 => 
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
        7 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'contact_status_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT_STATUS',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
              'copy' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'displayParams' => 
            array (
              'key' => 'billing',
              'copy' => 'primary',
              'billingKey' => 'primary',
              'additionalFields' => 
              array (
                'phone_office' => 'phone_work',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'tags',
          ),
        ),

		10 => array(

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
        11 => 
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
            'customCode' => '<input name="assistant_number_c" id="assistant_number_c" size="30" maxlength="100" type="text" value="{$fields.assistant_number_c.value}">&nbsp;&nbsp;<input type="hidden" name="assistant_number_c_suppressed" value="0"> <input type="checkbox" name="assistant_number_c_suppressed" id="assistant_number_c_suppressed" value="1" {if $fields.assistant_number_c_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
        ),
        12 => 
        array (
          0 => array(

          ),
          1 => 
          array (
            'name' => 'home_phone_c',
            'label' => 'LBL_HOME_PHONE',
            'customCode' => '<input name="home_phone_c" id="home_phone_c" size="30" maxlength="100" type="text" value="{$fields.home_phone_c.value}">&nbsp;&nbsp;<input type="hidden" name="home_phone_c_suppressed" value="0"> <input type="checkbox" name="home_phone_c_suppressed" id="home_phone_c_suppressed" value="1" {if $fields.home_phone_c_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'alternate_phone_c',
            'label' => 'LBL_ALTERNATE_PHONE',
            'customCode' => '<input name="alternate_phone_c" id="alternate_phone_c" size="30" maxlength="100" type="text" value="{$fields.alternate_phone_c.value}">&nbsp;&nbsp;<input type="hidden" name="alternate_phone_c_suppressed" value="0"> <input type="checkbox" name="alternate_phone_c_suppressed" id="alternate_phone_c_suppressed" value="1" {if $fields.alternate_phone_c_suppressed.value == 1}CHECKED{/if}> Suppressed',
          ),
          1 => 
          array (
            'name' => 'scmn_c',
            'label' => 'LBL_SCMN',
          ),
        ),
        14 => 
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
        15 => 
        array (
          0 => array(

          ),
          1 => 
          array (
            'name' => 'contact_interest_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT_INTEREST',
          ),
        ),

		16 => array(

          0 => 
          array (
            'name' => 'phone_fax',
            'comment' => 'Contact fax number',
            'label' => 'LBL_FAX_PHONE',
          ),

			1 => array(

			),

		),

      ),
      'lbl_portal_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'portal_name',
            'customCode' => '<table border="0" cellspacing="0" cellpadding="0"><tr><td>
	                           <input id="portal_name" name="portal_name" type="text" size="30" maxlength="30" value="{$fields.portal_name.value}" autocomplete="off">
	                           <input type="hidden" id="portal_name_existing" value="{$fields.portal_name.value}" autocomplete="off">
	                           </td><tr><tr><td><input type="hidden" id="portal_name_verified" value="true"></td></tr></table>',
          ),
          1 => 
          array (
            'name' => 'portal_active',
            'comment' => 'Indicator whether this contact is a portal user',
            'label' => 'LBL_PORTAL_ACTIVE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'portal_password1',
            'type' => 'password',
            'customCode' => '<input id="portal_password1" name="portal_password1" type="password" size="32" maxlength="32" value="{$fields.portal_password.value}" autocomplete="off">',
            'label' => 'LBL_PORTAL_PASSWORD',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'portal_password',
            'customCode' => '<input id="portal_password" name="portal_password" type="password" size="32" maxlength="32" value="{$fields.portal_password.value}" autocomplete="off"><input name="old_portal_password" type="hidden" value="{$fields.portal_password.value}" autocomplete="off">',
            'label' => 'LBL_CONFIRM_PORTAL_PASSWORD',
          ),
        ),
      ),

    ),
  ),
);
?>
