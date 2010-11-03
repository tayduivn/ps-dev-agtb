<?php
// created: 2010-07-19 12:56:39
$viewdefs = array (
  'Touchpoints' => 
  array (
    'DetailView' => 
    array (
      'templateMeta' => 
      array (
        'form' => 
        array (
          'footerTpl' => 'modules/Touchpoints/tpls/rawData.tpl',
          'headerTpl' => 'modules/Touchpoints/tpls/DetailViewHeader.tpl',
          'buttons' => 
          array (
            0 => 'EDIT',
            1 => 'DUPLICATE',
            2 => 'DELETE',
            3 => 
            array (
              'customCode' => '<input title="{$MOD.LBL_SCRUB_TITLE}" accessKey="{$MOD.LBL_SCRUB_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=Touchpoints&action=ScrubView&record={$fields.id.value}&return_module=Touchpoints&return_action=DetailView&return_id={$fields.id.value}\'" name="scrub" value="{$MOD.LBL_SCRUB}" {if !$SHOW_SCRUB}disabled="disabled" {/if}/>',
            ),
            4 => 
            array (
              'customCode' => '<input title="{$MOD.LBL_RESCRUB_TITLE}" accessKey="{$MOD.LBL_RESCRUB_BUTTON_KEY}" type="button" class="button" onClick="if ( confirm(\'{$MOD.LBL_RESCRUB_WARNING}\') ) document.location=\'index.php?module=Touchpoints&action=ScrubView&record={$fields.id.value}&return_module=Touchpoints&return_action=DetailView&return_id={$fields.id.value}&rescrub=true\'" name="convert" value="{$MOD.LBL_RESCRUB}" {if !$SHOW_RESCRUB}disabled="disabled" {/if}/>',
            ),
            5 => 'CONNECTOR',
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
        'useTabs' => false,
      ),
      'panels' => 
      array (
        'LBL_BASIC' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'campaign_id',
              'label' => 'LBL_CAMPAIGN',
            ),
            1 => 
            array (
              'name' => 'lead_source',
              'label' => 'LBL_LEAD_SOURCE',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'first_name',
              'label' => 'LBL_FIRST_NAME',
            ),
            1 => 
            array (
              'name' => 'last_name',
              'label' => 'LBL_LAST_NAME',
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
              'name' => 'phone_work',
              'label' => 'LBL_OFFICE_PHONE',
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
              'name' => 'phone_mobile',
              'label' => 'LBL_MOBILE_PHONE',
            ),
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'company_name',
              'label' => 'LBL_COMPANY_NAME',
            ),
            1 => 
            array (
              'name' => 'phone_home',
              'label' => 'LBL_HOME_PHONE',
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
          6 => 
          array (
            0 => 
            array (
              'name' => 'website',
              'type' => 'link',
              'label' => 'LBL_WEBSITE',
            ),
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'products_of_interest',
              'label' => 'LBL_PRODUCTS_OF_INTEREST',
            ),
            1 => 
            array (
              'name' => 'initial_subscriptions_c',
              'label' => 'Initial_Subscriptions_c',
            ),
          ),
          8 => 
          array (
            0 => 
            array (
              'name' => 'potential_users_c',
              'label' => 'Potential_Users_c',
            ),
            1 => 
            array (
              'name' => 'current_solution',
              'label' => 'LBL_CURRENT_SYSTEM',
            ),
          ),
          9 => 
          array (
            0 => 
            array (
              'name' => 'competitor_1',
              'label' => 'Competitor1__c',
            ),
            1 => 
            array (
              'name' => 'competitor_2',
              'label' => 'Competitor2__c',
            ),
          ),
          10 => 
          array (
            0 => 
            array (
              'name' => 'competitor_3',
              'label' => 'Competitor_3__c',
            ),
            1 => 
            array (
              'name' => 'account_type',
              'label' => 'Type__c',
            ),
          ),
          11 => 
          array (
            0 => 
            array (
              'name' => 'usage_groups_c',
              'label' => 'usage_groups_c',
            ),
            1 => 
            array (
              'name' => 'budget_c',
              'label' => 'Budget__c',
            ),
          ),
          12 => 
          array (
            0 => '',
            1 => 
            array (
              'name' => 'purchasing_timeline',
              'label' => 'LBL_PURCHASING_TIMELINE',
            ),
          ),
        ),
        'lbl_panel1' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'email1',
              'label' => 'LBL_EMAIL_ADDRESS',
            ),
            1 => 
            array (
              'name' => 'email_opt_out',
              'label' => 'LBL_EMAIL_OPT_OUT',
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
          1 => 
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
              'label' => 'LBL_BILLING_ADDRESS_STREET',
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
              'label' => 'LBL_SHIPPING_ADDRESS_STREET',
            ),
          ),
        ),
        'lbl_panel3' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'assigned_user_name',
              'label' => 'LBL_ASSIGNED_TO',
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
              'name' => 'referred_by',
              'label' => 'LBL_REFERED_BY',
            ),
            1 => 
            array (
              'name' => 'partner_assigned_to_c',
              'label' => 'Partner_Assigned_To_c',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'portal_name',
              'label' => 'LBL_PORTAL_NAME',
            ),
            1 => 
            array (
              'name' => 'portal_app',
              'label' => 'LBL_PORTAL_APP',
            ),
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'lead_submitter_c',
              'label' => 'LBL_LEAD_SUBMITTER',
            ),
            1 => '',
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'conversion_date',
              'label' => 'LBL_CONVERSION_DATE',
            ),
            1 => 
            array (
              'name' => 'source_type',
              'label' => 'LBL_SOURCE_TYPE',
            ),
          ),
          5 => 
          array (
            0 => 
            array (
              'name' => 'scrubbed',
              'label' => 'LBL_SCRUBBED',
            ),
            1 => 
            array (
              'name' => 'scrub_result',
              'label' => 'LBL_SCRUB_RESULT',
            ),
          ),
          6 => 
          array (
            0 => 
            array (
              'name' => 'lead_pass_c',
              'label' => 'lead_pass_c',
            ),
            1 => 
            array (
              'name' => 'lead_pass_date_c',
              'label' => 'LBL_LEAD_PASS_DATE_C',
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
        'LBL_DETAILED' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'ticker_symbol',
              'label' => 'LBL_TICKER_SYMBOL',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'ownership',
              'label' => 'LBL_OWNERSHIP',
            ),
            1 => 
            array (
              'name' => 'industry',
              'label' => 'LBL_INDUSTRY',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'area_of_expertise',
              'label' => 'LBL_AREA_OF_EXPERTISE',
            ),
            1 => 
            array (
              'name' => 'back_office_support',
              'label' => 'LBL_BACK_OFFICE_SUPPORT',
            ),
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'email_client',
              'label' => 'Email_Client__c',
            ),
            1 => 
            array (
              'name' => 'gross_annual_sales',
              'label' => 'LBL_GROSS_ANNUAL_SALES',
            ),
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'hosting_servers',
              'label' => 'LBL_HOSTING_SERVERS',
            ),
            1 => 
            array (
              'name' => 'how_they_heard',
              'label' => 'LBL_HOW_THEY_HEARD',
            ),
          ),
          5 => 
          array (
            0 => 
            array (
              'name' => 'initial_contact_point_c',
              'label' => 'Initial_Contact_Point_c',
            ),
            1 => 
            array (
              'name' => 'install_date_2_c',
              'label' => 'Install_Date_0',
            ),
          ),
          6 => 
          array (
            0 => 
            array (
              'name' => 'install_status_c',
              'label' => 'Install_Status_c',
            ),
            1 => 
            array (
              'name' => 'instance_key_c',
              'label' => 'LBL_INSTANCE_KEY',
            ),
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'last_act_date_c',
              'label' => 'last_act_date_c',
            ),
          ),
          8 => 
          array (
            0 => 
            array (
              'name' => 'open_source_exp',
              'label' => 'LBL_OPEN_SOURCE_EXP',
            ),
            1 => 
            array (
              'name' => 'operating_system',
              'label' => 'Operating_System__c',
            ),
          ),
          9 => 
          array (
            0 => 
            array (
              'name' => 'referral_url',
              'label' => 'LBL_REFERRAL_URL',
            ),
            1 => 
            array (
              'name' => 'third_party_validation_c',
              'label' => 'third_party_validation_c',
            ),
          ),
          10 => 
          array (
            0 => 
            array (
              'name' => 'regions_covered',
              'label' => 'LBL_REGIONS_COVERED',
            ),
            1 => 
            array (
              'name' => 'replace_timeline',
              'label' => 'LBL_REPLACE_TIMELINE',
            ),
          ),
          11 => 
          array (
            0 => 
            array (
              'name' => 'registered_eval_c',
              'label' => 'registered_eval_c',
            ),
            1 => 
            array (
              'name' => 'remote_ip_address_c',
              'label' => 'Remote_IP_Address_c',
            ),
          ),
          12 => 
          array (
            0 => 
            array (
              'name' => 'route_status_c',
              'label' => 'Route_Status_c',
            ),
            1 => 
            array (
              'name' => 'site_id_c',
              'label' => 'Site_ID_c',
            ),
          ),
          13 => 
          array (
            0 => 
            array (
              'name' => 'software_resell',
              'label' => 'LBL_SOFTWARE_RESELL',
            ),
            1 => 
            array (
              'name' => 'years_selling_crm',
              'label' => 'LBL_YEARS_SELLING_CRM',
            ),
          ),
          14 => 
          array (
            0 => 
            array (
              'name' => 'vertical_markets_c',
              'label' => 'LBL_VERTICAL_MARKETS',
            ),
            1 => 
            array (
              'name' => 'website',
              'type' => 'link',
              'label' => 'LBL_WEBSITE',
            ),
          ),
          15 => 
          array (
            0 => 
            array (
              'name' => 'employees',
              'label' => 'LBL_NUMBER_OF_EMPLOYEES',
            ),
            1 => 
            array (
              'name' => 'annual_revenue',
              'label' => 'LBL_ANNUAL_REVENUE',
            ),
          ),
          16 => 
          array (
            0 => 
            array (
              'name' => 'lead_group_c',
              'label' => 'Lead_Group_c',
            ),
            1 => 
            array (
              'name' => 'call_back_c',
              'label' => 'call_back_c',
            ),
          ),
          17 => 
          array (
            0 => 
            array (
              'name' => 'phone_other',
              'label' => 'LBL_OTHER_PHONE',
            ),
          ),
          18 => 
          array (
            0 => 
            array (
              'name' => 'score',
              'label' => 'LBL_SCORE',
            ),
          ),
          19 => 
          array (
            0 => 
            array (
              'name' => 'referred_by',
              'label' => 'LBL_REFERED_BY',
            ),
            1 => 
            array (
              'name' => 'phone_fax',
              'label' => 'LBL_FAX_PHONE',
            ),
          ),
          20 => 
          array (
            0 => 
            array (
              'name' => 'region_c',
              'label' => 'Region_c',
            ),
            1 => '',
          ),
          21 => 
          array (
            0 => 
            array (
              'name' => 'discrepancies',
              'displayParams' => 
              array (
                'cols' => 80,
                'rows' => 6,
              ),
              'label' => 'LBL_DISCREPANCIES',
            ),
          ),
          22 => 
          array (
            0 => 
            array (
              'name' => 'date_modified',
              'label' => 'LBL_DATE_MODIFIED',
              'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            ),
            1 => '',
          ),
          23 => 
          array (
            0 => 
            array (
              'name' => 'parent_name',
              'label' => 'parent_name',
            ),
            1 => 
            array (
              'name' => 'date_entered',
              'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
              'label' => 'LBL_DATE_ENTERED',
            ),
          ),
          24 => 
          array (
            0 => 
            array (
              'name' => 'lead_source_description',
              'displayParams' => 
              array (
                'cols' => 80,
                'rows' => 6,
              ),
              'label' => 'LBL_LEAD_SOURCE_DESCRIPTION',
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
