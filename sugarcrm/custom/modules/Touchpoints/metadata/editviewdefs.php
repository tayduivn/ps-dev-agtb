<?php
$viewdefs ['Touchpoints'] = 
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
            'tabindex' => '1',
          ),
          1 => 
          array (
            'name' => 'lead_source',
            'label' => 'LBL_LEAD_SOURCE',
            'tabindex' => '2',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'first_name',
            'label' => 'LBL_FIRST_NAME',
            'tabindex' => '3',
          ),
          1 => 
          array (
            'name' => 'last_name',
            'label' => 'LBL_LAST_NAME',
            'tabindex' => '4',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
            'tabindex' => '5',
          ),
          1 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
            'tabindex' => '6',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'department',
            'label' => 'LBL_DEPARTMENT',
            'tabindex' => '7',
          ),
          1 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
            'tabindex' => '8',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'company_name',
            'label' => 'LBL_COMPANY_NAME',
            'tabindex' => '9',
          ),
          1 => 
          array (
            'name' => 'phone_home',
            'label' => 'LBL_HOME_PHONE',
            'tabindex' => '10',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
            'tabindex' => '11',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
            'tabindex' => '79',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
            'tabindex' => '71',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'products_of_interest',
            'label' => 'LBL_PRODUCTS_OF_INTEREST',
            'tabindex' => '14',
          ),
          1 => 
          array (
            'name' => 'initial_subscriptions_c',
            'label' => 'Initial_Subscriptions_c',
            'tabindex' => '15',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'potential_users_c',
            'label' => 'Potential_Users_c',
            'tabindex' => '16',
          ),
          1 => 
          array (
            'name' => 'current_solution',
            'label' => 'LBL_CURRENT_SYSTEM',
            'tabindex' => '17',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'competitor_1',
            'label' => 'Competitor1__c',
            'tabindex' => '18',
          ),
          1 => 
          array (
            'name' => 'competitor_2',
            'label' => 'Competitor2__c',
            'tabindex' => '19',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'competitor_3',
            'label' => 'Competitor_3__c',
            'tabindex' => '20',
          ),
          1 => 
          array (
            'name' => 'account_type',
            'label' => 'Type__c',
            'tabindex' => '21',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'usage_groups_c',
            'label' => 'usage_groups_c',
            'tabindex' => '22',
          ),
          1 => 
          array (
            'name' => 'budget_c',
            'label' => 'Budget__c',
            'tabindex' => '23',
          ),
        ),
        12 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'purchasing_timeline',
            'label' => 'LBL_PURCHASING_TIMELINE',
            'tabindex' => '24',
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
            'tabindex' => '25',
          ),
          1 => 
          array (
            'name' => 'email_opt_out',
            'label' => 'LBL_EMAIL_OPT_OUT',
            'tabindex' => '26',
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
            'tabindex' => '27',
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
            'tabindex' => '28',
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
            'tabindex' => '29',
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
            'tabindex' => '30',
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
            'tabindex' => '31',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
            'tabindex' => '32',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'referred_by',
            'label' => 'LBL_REFERED_BY',
            'tabindex' => '78',
          ),
          1 => 
          array (
            'name' => 'partner_assigned_to_c',
            'label' => 'Partner_Assigned_To_c',
            'tabindex' => '34',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'portal_name',
            'label' => 'LBL_PORTAL_NAME',
            'tabindex' => '35',
          ),
          1 => 
          array (
            'name' => 'portal_app',
            'label' => 'LBL_PORTAL_APP',
            'tabindex' => '36',
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
            'tabindex' => '37',
          ),
          1 => 
          array (
            'name' => 'source_type',
            'label' => 'LBL_SOURCE_TYPE',
            'tabindex' => '38',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'lead_pass_c',
            'label' => 'lead_pass_c',
            'tabindex' => '41',
          ),
          1 => 
          array (
            'name' => 'lead_pass_date_c',
            'label' => 'LBL_LEAD_PASS_DATE_C',
            'tabindex' => '42',
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
            'tabindex' => '43',
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
            'tabindex' => '44',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'ownership',
            'label' => 'LBL_OWNERSHIP',
            'tabindex' => '45',
          ),
          1 => 
          array (
            'name' => 'industry',
            'label' => 'LBL_INDUSTRY',
            'tabindex' => '46',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'area_of_expertise',
            'label' => 'LBL_AREA_OF_EXPERTISE',
            'tabindex' => '47',
          ),
          1 => 
          array (
            'name' => 'back_office_support',
            'label' => 'LBL_BACK_OFFICE_SUPPORT',
            'tabindex' => '48',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'email_client',
            'label' => 'Email_Client__c',
            'tabindex' => '49',
          ),
          1 => 
          array (
            'name' => 'gross_annual_sales',
            'label' => 'LBL_GROSS_ANNUAL_SALES',
            'tabindex' => '50',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'hosting_servers',
            'label' => 'LBL_HOSTING_SERVERS',
            'tabindex' => '51',
          ),
          1 => 
          array (
            'name' => 'how_they_heard',
            'label' => 'LBL_HOW_THEY_HEARD',
            'tabindex' => '52',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'initial_contact_point_c',
            'label' => 'Initial_Contact_Point_c',
            'tabindex' => '53',
          ),
          1 => 
          array (
            'name' => 'install_date_2_c',
            'label' => 'Install_Date_0',
            'tabindex' => '54',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'install_status_c',
            'label' => 'Install_Status_c',
            'tabindex' => '55',
          ),
          1 => 
          array (
            'name' => 'instance_key_c',
            'label' => 'LBL_INSTANCE_KEY',
            'tabindex' => '56',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'last_act_date_c',
            'label' => 'last_act_date_c',
            'tabindex' => '57',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'open_source_exp',
            'label' => 'LBL_OPEN_SOURCE_EXP',
            'tabindex' => '58',
          ),
          1 => 
          array (
            'name' => 'operating_system',
            'label' => 'Operating_System__c',
            'tabindex' => '59',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'referral_url',
            'label' => 'LBL_REFERRAL_URL',
            'tabindex' => '60',
          ),
          1 => 
          array (
            'name' => 'third_party_validation_c',
            'label' => 'third_party_validation_c',
            'tabindex' => '61',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'regions_covered',
            'label' => 'LBL_REGIONS_COVERED',
            'tabindex' => '62',
          ),
          1 => 
          array (
            'name' => 'replace_timeline',
            'label' => 'LBL_REPLACE_TIMELINE',
            'tabindex' => '63',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'registered_eval_c',
            'label' => 'registered_eval_c',
            'tabindex' => '64',
          ),
          1 => 
          array (
            'name' => 'remote_ip_address_c',
            'label' => 'Remote_IP_Address_c',
            'tabindex' => '65',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'route_status_c',
            'label' => 'Route_Status_c',
            'tabindex' => '66',
          ),
          1 => 
          array (
            'name' => 'site_id_c',
            'label' => 'Site_ID_c',
            'tabindex' => '67',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'software_resell',
            'label' => 'LBL_SOFTWARE_RESELL',
            'tabindex' => '68',
          ),
          1 => 
          array (
            'name' => 'years_selling_crm',
            'label' => 'LBL_YEARS_SELLING_CRM',
            'tabindex' => '69',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'vertical_markets_c',
            'label' => 'LBL_VERTICAL_MARKETS',
            'tabindex' => '70',
          ),
          1 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
            'tabindex' => '71',
          ),
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'employees',
            'label' => 'LBL_NUMBER_OF_EMPLOYEES',
            'tabindex' => '72',
          ),
          1 => 
          array (
            'name' => 'annual_revenue',
            'label' => 'LBL_ANNUAL_REVENUE',
            'tabindex' => '73',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'lead_group_c',
            'label' => 'Lead_Group_c',
            'tabindex' => '74',
          ),
          1 => 
          array (
            'name' => 'call_back_c',
            'label' => 'call_back_c',
            'tabindex' => '75',
          ),
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'phone_other',
            'label' => 'LBL_OTHER_PHONE',
            'tabindex' => '76',
          ),
        ),
        18 => 
        array (
          0 => 
          array (
            'name' => 'score',
            'label' => 'LBL_SCORE',
            'tabindex' => '77',
          ),
        ),
        19 => 
        array (
          0 => 
          array (
            'name' => 'referred_by',
            'label' => 'LBL_REFERED_BY',
            'tabindex' => '78',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
            'tabindex' => '79',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'region_c',
            'label' => 'Region_c',
            'tabindex' => '80',
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
            'tabindex' => '81',
          ),
        ),
        22 => 
        array (
          0 => 
          array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'tabindex' => '82',
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
            'tabindex' => '83',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
            'tabindex' => '84',
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
            'tabindex' => '85',
          ),
        ),
      ),
    ),
  ),
);
?>
