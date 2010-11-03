<?php
$viewdefs ['LeadContacts'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'form' => 
      array (
        'footerTpl' => 'modules/LeadContacts/tpls/footer.tpl',
      ),
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
            'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
            'label' => 'LBL_FIRST_NAME',
          ),
          1 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
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
            ),
            'label' => 'LBL_LAST_NAME',
          ),
          1 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
          1 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'department',
            'label' => 'LBL_DEPARTMENT',
          ),
          1 => 
          array (
            'name' => 'phone_home',
            'label' => 'LBL_HOME_PHONE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'leadaccount_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'customCode' => '{if $smarty.request.prospect_id}{literal}<script type="text/javascript">document.getElementById(\'leadaccount_name_label\').innerHTML = \'\';</script>{/literal}{else}<input type="text" name="leadaccount_name" class="sqsEnabled" tabindex="l" id="leadaccount_name" size="" value="{$fields.leadaccount_name.value}" title="" autocomplete="off"  >
<input type="hidden" name="leadaccount_id" id="leadaccount_id" value="{$fields.leadaccount_id.value}">
<input type="button" name="btn_leadaccount_name" tabindex="l" title="Select [Alt+T]" accessKey="T" class="button" value="Select" onclick=\'{literal}open_popup("LeadAccounts", 600, 400, "", true, false, {"call_back_function":"set_return","form_name":"{/literal}{$form_name}{literal}","field_to_name_array":{"id":"leadaccount_id","name":"leadaccount_name"}}, "single", true);{/literal}\'>
<input type="button" name="btn_clr_leadaccount_name" tabindex="l" title="Clear [Alt+C]" accessKey="C" class="button" onclick="this.form.leadaccount_name.value = \'\'; this.form.leadaccount_id.value = \'\';" value="Clear">
{/if}',
            'label' => 'LBL_LEADACCOUNT_NAME',
          ),
          1 => 
          array (
            'name' => 'phone_other',
            'label' => 'LBL_OTHER_PHONE',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
        ),
      ),
      'lbl_email_addresses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
      ),
      'lbl_address_information' => 
      array (
        0 => 
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
            'label' => 'LBL_PRIMARY_ADDRESS_STREET',
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
            'label' => 'LBL_ALT_ADDRESS_STREET',
          ),
        ),
      ),
      'lbl_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'products_of_interest_c',
            'label' => 'LBL_PRODUCTS_OF_INTEREST_c',
          ),
          1 => 
          array (
            'name' => 'score',
            'label' => 'LBL_SCORE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'initial_subscriptions_c',
            'label' => 'Initial_Subscriptions_c',
          ),
          1 => 
          array (
            'name' => 'usage_groups_c',
            'label' => 'usage_groups_c',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'potential_users_c',
            'label' => 'LBL_POTENTIAL_USERS_c',
          ),
          1 => NULL,
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'current_solution_c',
            'label' => 'LBL_CURRENT_SOLUTION_c',
          ),
          1 => NULL,
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'competitor_1_c',
            'label' => 'Competitor_1_c',
          ),
          1 => 
          array (
            'name' => 'budget_c',
            'label' => 'BUDGET_c',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'competitor_2_c',
            'label' => 'Competitor_2_c',
          ),
          1 => 
          array (
            'name' => 'purchasing_timeline_c',
            'label' => 'LBL_PURCHASING_TIMELINE_c',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'competitor_3_c',
            'label' => 'Competitor_3_c',
          ),
          1 => NULL,
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'lead_group_c',
            'label' => 'Lead_Group_c',
          ),
          1 => 
          array (
            'name' => 'region_c',
            'label' => 'Region_c',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'how_they_heard_c',
            'label' => 'LBL_HOW_THEY_HEARD_c',
          ),
          1 => 
          array (
            'name' => 'third_party_validation_c',
            'label' => 'third_party_validation_c',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'next_step_c',
            'label' => 'LBL_NEXT_STEP_C',
            'tabindex' => '19',
          ),
          1 => 
          array (
            'name' => 'decision_date_c',
            'label' => 'decision_date_c',
            'tabindex' => '21',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'next_step_due_date_c',
            'label' => 'LBL_NEXT_STEP_DUE_DATE_C',
            'tabindex' => '20',
          ),
          1 => NULL,
        ),
      ),
      'lbl_panel3' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'converted',
            'label' => 'LBL_CONVERTED',
            'displayParams' => 
            array (
              'field' => 
              array (
                'disabled' => 'true',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'lead_pass_c',
            'label' => 'lead_pass_c',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'partner_assigned_to_c',
            'label' => 'Partner_Assigned_To_c',
          ),
        ),
      ),
      'lbl_panel2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
      ),
    ),
  ),
);
?>
