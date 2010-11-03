<?php
$viewdefs ['Contacts'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'preForm' => '<form name="vcard" action="index.php"><input type="hidden" name="entryPoint" value="vCard"><input type="hidden" name="contact_id" value="{$fields.id.value}"><input type="hidden" name="module" value="Contacts"></form>',
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
            'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
          ),
        ),
      ),
      'maxColumns' => '2',
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
      '
		  ' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'dce_user_name_c',
            'label' => 'LBL_DCE_USER_NAME',
          ),
          1 => 
          array (
            'name' => 'licensing_rights_c',
            'label' => 'LBL_LICENSING_RIGHTS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'phone_home',
            'label' => 'LBL_HOME_PHONE',
          ),
          1 => 
          array (
            'name' => 'lead_source',
            'label' => 'LBL_LEAD_SOURCE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'phone_other',
            'label' => 'LBL_OTHER_PHONE',
          ),
          1 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
          1 => 
          array (
            'name' => 'department',
            'label' => 'LBL_DEPARTMENT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
          1 => 
          array (
            'name' => 'birthdate',
            'label' => 'LBL_BIRTHDATE',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'report_to_name',
            'label' => 'LBL_REPORTS_TO',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'assistant',
            'label' => 'LBL_ASSISTANT',
          ),
          1 => 
          array (
            'name' => 'technical_proficiency_',
            'label' => 'LBL_TECHNICAL_PROFICIENCY_',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'assistant_phone',
            'label' => 'LBL_ASSISTANT_PHONE',
          ),
          1 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'sync_contact',
            'label' => 'LBL_SYNC_CONTACT',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'oppq_active_c',
            'label' => 'LBL_OPPQ_ACTIVE_C',
          ),
          1 => 
          array (
            'name' => 'primary_business_c',
            'label' => 'Primary_Business_Contact__c',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'support_authorized_c',
            'label' => 'Support_Authorized_Contact__c',
          ),
          1 => 
          array (
            'name' => 'university_enabled_c',
            'label' => 'LBL_UNIVERSITY_ENABLED',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'billing_contact_c',
            'label' => 'Billing_Contact__c',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
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
        15 => 
        array (
          0 => 
          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALTERNATE_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
            ),
          ),
          1 => 
          array (
            'name' => 'portal_name',
            'customCode' => '{if $PORTAL_ENABLED}{$fields.portal_name.value}{/if}',
            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_NAME" module="Contacts"}{/if}',
            'label' => 'LBL_PORTAL_NAME',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'portal_active',
            'customCode' => '{if $PORTAL_ENABLED}
	          		         {if strval($fields.portal_active.value) == "1" || strval($fields.portal_active.value) == "yes" || strval($fields.portal_active.value) == "on"}
	          		         {assign var="checked" value="CHECKED"}
                             {else}
                             {assign var="checked" value=""}
                             {/if}
                             <input type="checkbox" class="checkbox" name="{$fields.portal_active.name}" size="{$displayParams.size}" disabled="true" {$checked}>
                             {/if}',
            'customLabel' => '{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_ACTIVE" module="Contacts"}{/if}',
            'label' => 'LBL_PORTAL_ACTIVE',
          ),
          1 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        17 => 
        array (
        ),
      ),
    ),
  ),
);
?>
