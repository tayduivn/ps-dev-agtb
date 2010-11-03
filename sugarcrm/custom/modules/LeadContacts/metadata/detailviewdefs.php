<?php
// created: 2010-03-12 15:05:37
$viewdefs = array (
  'LeadContacts' => 
  array (
    'DetailView' => 
    array (
      'templateMeta' => 
      array (
        'preForm' => '<form name="vcard" action="index.php"><input type="hidden" name="entryPoint" value="vCard"><input type="hidden" name="contact_id" value="{$fields.id.value}"><input type="hidden" name="module" value="LeadContacts"></form>',
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
              'customCode' => '<input title="{$MOD.LBL_CONVERTLEAD_TITLE}" accessKey="{$MOD.LBL_CONVERTLEAD_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=LeadAccounts&action=ConvertLead&record={$fields.leadaccount_id.value}&uid={$fields.id.value}\'" name="convert" value="{$MOD.LBL_CONVERTLEAD}" {if $fields.converted.value == "1" || $fields.leadaccount_status.value == "Dead"}disabled="disabled" {/if}/>',
            ),
            5 => 
            array (
              'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'LeadContacts\'; this.form.return_action.value=\'DetailView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
            ),
          ),
          'headerTpl' => 'modules/LeadContacts/tpls/DetailViewHeader.tpl',
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
      ),
      'panels' => 
      array (
        'default' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'full_name',
              'customCode' => '{$fields.full_name.value}&nbsp;&nbsp;<input type="button" class="button" name="vCardButton" value="{$MOD.LBL_VCARD}" onClick="document.vcard.submit();">',
              'label' => 'LBL_NAME',
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
              'name' => 'title',
              'label' => 'LBL_TITLE',
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
              'name' => 'department',
              'label' => 'LBL_DEPARTMENT',
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
              'name' => 'leadaccount_name',
              'label' => 'LBL_LEADACCOUNT_NAME',
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
              'name' => 'date_entered',
              'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
              'label' => 'LBL_DATE_ENTERED',
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
              'name' => 'date_modified',
              'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
              'label' => 'LBL_DATE_MODIFIED',
            ),
            1 => 
            array (
              'name' => 'phone_fax',
              'label' => 'LBL_FAX_PHONE',
            ),
          ),
          6 => 
          array (
            0 => 
            array (
              'name' => 'do_not_call',
              'label' => 'LBL_DO_NOT_CALL',
            ),
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'email1',
              'label' => 'LBL_EMAIL_ADDRESS',
            ),
          ),
        ),
        'lbl_panel2' => 
        array (
          0 => 
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
              'label' => 'LBL_ALT_ADDRESS',
              'type' => 'address',
              'displayParams' => 
              array (
                'key' => 'alt',
              ),
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
            1 => 
            array (
              'name' => NULL,
              'displayParams' => 
              array (
              ),
            ),
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
            1 => 'portal_name',
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
            ),
            1 => 
            array (
              'name' => 'decision_date_c',
              'label' => 'decision_date_c',
            ),
          ),
          10 => 
          array (
            0 => 
            array (
              'name' => 'next_step_due_date_c',
              'label' => 'LBL_NEXT_STEP_DUE_DATE_C',
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
              'label' => 'LBL_TEAM',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'converted',
              'label' => 'LBL_CONVERTED',
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
        'lbl_panel4' => 
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
  ),
);
?>
