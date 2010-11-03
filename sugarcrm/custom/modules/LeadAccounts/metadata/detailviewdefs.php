<?php
// created: 2010-03-12 15:05:37
$viewdefs = array (
  'LeadAccounts' => 
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
              'customCode' => '<input title="{$MOD.LBL_CONVERTLEAD_TITLE}" accessKey="{$MOD.LBL_CONVERTLEAD_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=LeadAccounts&action=ConvertLead&record={$fields.id.value}\'" name="convert" value="{$MOD.LBL_CONVERTLEAD}" {if $FULLY_CONVERTED}disabled="disabled" {/if}/>',
            ),
          ),
          'headerTpl' => 'modules/LeadAccounts/tpls/DetailViewHeader.tpl',
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
              'name' => 'name',
              'label' => 'LBL_NAME',
            ),
            1 => 
            array (
              'name' => 'phone_office',
              'label' => 'LBL_PHONE_OFFICE',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'parent_name',
              'label' => 'LBL_MEMBER_OF',
            ),
            1 => 
            array (
              'name' => 'phone_fax',
              'label' => 'LBL_FAX',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'ticker_symbol',
              'label' => 'LBL_TICKER_SYMBOL',
            ),
            1 => 
            array (
              'name' => 'phone_alternate',
              'label' => 'LBL_OTHER_PHONE',
            ),
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'industry',
              'label' => 'LBL_INDUSTRY',
            ),
            1 => 
            array (
              'name' => 'converted',
              'label' => 'LBL_CONVERTED',
            ),
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'ownership',
              'label' => 'LBL_OWNERSHIP',
            ),
            1 => 
            array (
              'name' => 'rating',
              'label' => 'LBL_RATING',
            ),
          ),
          5 => 
          array (
            0 => 
            array (
              'name' => 'billing_address_street',
              'label' => 'LBL_BILLING_ADDRESS',
              'type' => 'address',
              'displayParams' => 
              array (
                'key' => 'billing',
              ),
            ),
            1 => 
            array (
              'name' => 'shipping_address_street',
              'label' => 'LBL_SHIPPING_ADDRESS',
              'type' => 'address',
              'displayParams' => 
              array (
                'key' => 'shipping',
              ),
            ),
          ),
          6 => 
          array (
            0 => 
            array (
              'name' => 'email1',
              'label' => 'LBL_EMAIL',
            ),
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'last_interaction_date',
              'label' => 'LBL_LAST_INTERACTION_DATE',
            ),
            1 => 
            array (
              'name' => 'status',
              'label' => 'LBL_STATUS',
            ),
          ),
          8 => 
          array (
            0 => 
            array (
              'name' => 'lead_source',
              'label' => 'LBL_LEAD_SOURCE',
            ),
            1 => 
            array (
              'name' => 'account_type',
              'label' => 'Type__c',
            ),
          ),
          9 => 
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
          10 => 
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
          11 => 
          array (
            0 => 
            array (
              'name' => 'hosting_servers',
              'label' => 'LBL_HOSTING_SERVERS',
            ),
          ),
          12 => 
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
          13 => 
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
          14 => 
          array (
            0 => 
            array (
              'name' => 'last_act_date_c',
              'label' => 'last_act_date_c',
            ),
          ),
          15 => 
          array (
            0 => 
            array (
              'name' => 'open_source_exp',
              'label' => 'LBL_OPEN_SOURCE_EXP',
            ),
          ),
          16 => 
          array (
            0 => 
            array (
              'name' => 'referral_url',
              'label' => 'LBL_REFERRAL_URL',
            ),
          ),
          17 => 
          array (
            0 => 
            array (
              'name' => 'regions_covered',
              'label' => 'LBL_REGIONS_COVERED',
            ),
          ),
          18 => 
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
          19 => 
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
          20 => 
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
          21 => 
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
        ),
        'LBL_QUALIFICATION' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'score',
              'label' => 'LBL_SCORE',
            ),
            1 => 
            array (
              'name' => NULL,
              'displayParams' => 
              array (
              ),
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'employees',
              'label' => 'LBL_EMPLOYEES',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'annual_revenue',
              'label' => 'LBL_ANNUAL_REVENUE',
            ),
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'decision_date_c',
              'label' => 'decision_date_c',
            ),
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'call_back_c',
              'label' => 'call_back_c',
            ),
          ),
          5 => 
          array (
            0 => NULL,
          ),
          6 => 
          array (
            0 => NULL,
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'next_step_c',
              'label' => 'LBL_NEXT_STEP_C',
            ),
            1 => NULL,
          ),
          8 => 
          array (
            0 => 
            array (
              'name' => 'next_step_due_date_c',
              'label' => 'LBL_NEXT_STEP_DUE_DATE_C',
            ),
            1 => NULL,
          ),
        ),
        'LBL_ASSIGNMENT' => 
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
              'name' => 'date_entered',
              'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
              'label' => 'LBL_DATE_ENTERED',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'team_name',
              'displayParams' => 
              array (
                'display' => true,
              ),
              'label' => 'LBL_TEAM',
            ),
            1 => 
            array (
              'name' => 'date_modified',
              'label' => 'LBL_DATE_MODIFIED',
              'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'referred_by',
              'label' => 'LBL_REFERED_BY',
            ),
            1 => NULL,
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'partner_assigned_to_c',
              'label' => 'Partner_Assigned_To_c',
            ),
          ),
          4 => 
          array (
            0 => NULL,
          ),
          5 => 
          array (
            0 => 
            array (
              'name' => 'description',
              'displayParams' => 
              array (
                'cols' => 80,
                'rows' => 6,
              ),
              'label' => 'LBL_DESCRIPTION',
            ),
          ),
          6 => 
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
