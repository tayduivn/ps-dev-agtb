<?php
$viewdefs ['Accounts'] = 
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
          4 => 'CONNECTOR',
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
          'file' => 'modules/Accounts/Account.js',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'DEFAULT' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
            'displayParams' => 
            array (
              'enableConnectors' => true,
              'module' => 'Accounts',
              'connectors' => 
              array (
                0 => 'ext_rest_linkedin',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'subscription_expiration_c',
            'label' => 'Subscription_Expiration__c',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'high_prio_c',
            'label' => 'High_Priority_Account_c',
          ),
          1 => 
          array (
            'name' => 'phone_office',
            'label' => 'LBL_PHONE_OFFICE',
	    'customCode' => '{fonality_phone value=$fields.phone_office.value this_module=Accounts this_id=$fields.id.value}',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
            'customCode' => '<a href="http://{$fields.website.value}" target="_blank">{$fields.website.value}</a>',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_PHONE_FAX',
	    'customCode' => '{fonality_phone value=$fields.phone_fax.value this_module=Accounts this_id=$fields.id.value}',
          ),
        ),
        3 => 
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
	    'customCode' => '{fonality_phone value=$fields.phone_alternate.value this_module=Accounts this_id=$fields.id.value}',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'parent_name',
            'label' => 'LBL_MEMBER_OF',
          ),
          1 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'employees',
            'label' => 'LBL_EMPLOYEES',
          ),
          1 => 
          array (
            'name' => 'LBL_FILLER',
            'label' => 'LBL_FILLER',
          ),
        ),
        6 => 
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
        7 => 
        array (
          0 => 
          array (
            'name' => 'industry',
            'label' => 'LBL_INDUSTRY',
          ),
          1 => 
          array (
            'name' => 'sic_code',
            'label' => 'LBL_SIC_CODE',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'account_type',
            'label' => 'LBL_TYPE',
          ),
          1 => 
          array (
            'name' => 'annual_revenue',
            'label' => 'LBL_ANNUAL_REVENUE',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'reference_code_c',
            'label' => 'LBL_REFERENCE_CODE',
          ),
          1 => 
          array (
            'name' => 'ref_code_expiration_c',
            'label' => 'LBL_REF_CODE_EXPIRATION',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'contract_version',
            'label' => 'LBL_CONTRACT_VERSION',
          ),
          1 => 
          array (
            'name' => 'code_customized_by_c',
            'label' => 'LBL_CODE_CUSTOMIZED_BY',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'resell_discount',
            'label' => 'LBL_RESELL_DISCOUNT',
          ),
          1 => 
          array (
            'name' => 'Support_Service_Level_c',
            'label' => 'Support Service Level_0',
          ),
        ),
        12 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'deployment_type_c',
            'label' => 'Deployment_Type__c',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'partner_assigned_to_c',
            'studio' => 'visible',
            'label' => 'LBL_PARTNER_ASSIGNED_TO',
          ),
          1 => 
          	array(
          		'name'		=>	'customer_msa_not_required_c',
          		'studio'	=>	'visible',
          		'label'		=>	'LBL_CUSTOMER_MSA_NOT_REQUIRED',
          		),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'Partner_Type_c',
            'label' => 'partner_Type__c',
          ),
          1 => 
          array (
            'name' => 'Partner_Type_c',
            'label' => 'partner_Type__c',
          ),
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'auto_send_renewal_emails_c',
            'label' => 'LBL_AUTO_SEND_RENEWAL_EMAILS',
          ),
          1 => 
          array (
            'name' => 'renewal_contact_c',
            'label' => 'LBL_RENEWAL_CONTACT_C',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_LIST_TEAM',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        18 => 
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
        19 => 
        array (
          0 => 
          array (
            'name' => 'region_c',
            'studio' => 'visible',
            'label' => 'LBL_REGION',
          ),
          1 => 
          array (
            'name' => 'id',
            'type' => 'link',
            'label' => 'LBL_USAGE_GRAPH',
            'customCode' => '<a href="https://sugarinternal.sugarondemand.com/index.php?action=SubscriptionUsageReport&module=Accounts&record={$fields.id.value}">Usage Graph</a>',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'cr_customer_reference_accounts_name',
            'label' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
          ),
          1 => 
          array (
            'name' => 'demo_enviroment_url_c',
            'label' => 'LBL_DEMO_ENVIROMENT_URL_C',
          ),
        ),
        21 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        22 => 
        array (
          0 => 
          array (
            'name' => 'discountcodes_accounts_name',
          ),
          1 =>
                  array(
                      'name' => 'po_order_5k_c',
                      'label' => 'LBL_PO_ORDER_5K'
                  ),
        ),
        23 => 
        array (
          0 => 
	  array (
		 'default' => 'false',
		 'customCode' => '{ if $fields.deployment_type_c.value == "ondemand" || $fields.deployment_type_c.value == "ondemand_ded" || $fields.deployment_type_c.value == ""}<a href="http://ionapi.sugarcrm.com/display.php?arid={$id}" onclick="window.open(this.href,\'window\',\'width=350,height=90,resizable,menubar\'); return false;">On-Demand Account URL</a>{/if}',
		 ),
        ),
      ),
      'lbl_panel7' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'customer_reference_c',
            'label' => 'LBL_CUSTOMER_REFERENCE',
          ),
          1 => 
          array (
            'name' => 'type_of_reference_c',
            'label' => 'LBL_TYPE_OF_REFERENCE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'reference_contact_c',
            'label' => 'LBL_REFERENCE_CONTACT',
          ),
          1 => 
          array (
            'name' => 'last_used_as_reference_c',
            'label' => 'LBL_LAST_USED_AS_REFERENCE',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'reference_status_c',
            'label' => 'LBL_REFERENCE_STATUS',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'reference_notes_c',
            'label' => 'LBL_REFERENCE_NOTES',
          ),
          1 => 
          array (
            'name' => 'last_used_reference_notes_c',
            'label' => 'LBL_LAST_USED_REFERENCE_NOTES',
          ),
        ),
      ),
      'LBL_PANEL1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'training_credits_purchased_c',
            'label' => 'Learning_Credits_Purchased__c',
          ),
          1 => 
          array (
            'name' => 'remaining_training_credits_c',
            'label' => 'Remaining_Learning_Credits__c',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'training_credits_pur_date_c',
            'label' => 'Most_Recent_Credits_Purchase_Date_c',
          ),
          1 => 
          array (
            'name' => 'training_credits_exp_date_c',
            'label' => 'Upcoming_Credits_Expiration_Date__c',
          ),
        ),
      ),
      'LBL_PANEL6' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'support_cases_purchased_c',
            'label' => 'Support_Cases_Purchased__c',
          ),
          1 => 
          array (
            'name' => 'remaining_support_cases_c',
            'label' => 'Remaining_Support_Cases__c',
          ),
        ),
      ),
      'LBL_PANEL4' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'dce_auth_user_c',
            'label' => 'LBL_DCE_AUTH_USER',
          ),
          1 => 
          array (
            'name' => 'dce_app_id_c',
            'label' => 'LBL_DCE_APP_ID',
          ),
        ),
      ),
      'lbl_detailview_panel8' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'discount_percent_c',
            'label' => 'LBL_DISCOUNT_PERCENT',
          ),
          1 =>
                array(
                        'name' => 'discount_amount_c',
                        'label' => 'LBL_DISCOUNT_AMOUNT',
                ),

        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'discount_valid_from_c',
            'label' => 'LBL_DISCOUNT_VALID_FROM',
            'customCode' => '{if $fields.discount_valid_from_c.value != ""}{$fields.discount_valid_from_c.value} to {$fields.discount_valid_to_c.value}{/if}',
          ),
          1 => 
          array (
            'name' => 'discount_no_expiration_c',
            'label' => 'LBL_DISCOUNT_NO_EXPIRATION',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'discount_perpetual_c',
            'label' => 'LBL_DISCOUNT_PERPETUAL',
          ),
          1 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'discount_approval_status_c',
            'label' => 'LBL_DISCOUNT_APPROVAL_STATUS',
          ),
          1 => '',
        ),
      ),
      'lbl_editview_panel8' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'discount_when_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN',
          ),
        ),
        1 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_dollars_c',
            'label' => 'LBL_DISCOUNT_WHEN_DOLLARS',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_prodtemp_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODTEMP',
          ),
        ),
        3 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_prodcat_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODCAT',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'discount_to_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_to_product_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO_PRODUCT',
          ),
        ),
        6 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_to_prodcat_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO_PRODCAT',
          ),
        ),
      ),
    ),
  ),
);
?>
