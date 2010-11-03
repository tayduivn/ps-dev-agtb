<?php
$viewdefs ['Accounts'] = 
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
      'includes' => 
      array (
        0 => 
        array (
          'file' => 'modules/Accounts/Account.js',
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
            'name' => 'description',
            'displayParams' => 
            array (
              'cols' => 80,
              'rows' => 6,
            ),
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'dce_auth_pass_c',
            'label' => 'LBL_DCE_AUTH_PASSWORD',
          ),
          1 => 
          array (
            'name' => 'training_credits_exp_date_c',
            'label' => 'Upcoming_Credits_Expiration_Date__c',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
          ),
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
        4 => 
        array (
          0 => 
          array (
            'name' => 'parent_name',
            'label' => 'LBL_MEMBER_OF',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'employees',
            'label' => 'LBL_EMPLOYEES',
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
            'customCode' => '
<select name="{$fields.account_type.name}" id="{$fields.account_type.name}" title=\'\' tabindex="0" OnChange=\'checkAccountTypeDependentDropdown()\' >
{if isset($fields.account_type.value) && $fields.account_type.value != \'\'}
{html_options options=$fields.account_type.options selected=$fields.account_type.value}
{else}
{html_options options=$fields.account_type.options selected=$fields.account_type.default}
{/if}
</select>
<script src=\'include/javascript/custom_javascript.js\'></script>
',
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
            'label' => 'LBL_REFERENCE_CODE_C',
          ),
          1 => 
          array (
            'name' => 'ref_code_expiration_c',
            'label' => 'LBL_REF_CODE_EXPIRATION',
          ),
        ),
        10 => 
        array (
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
          0 => 
          array (
            'name' => 'Partner_Type_c',
            'label' => 'partner_Type__c',
          ),
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
            'name' => 'team_name',
            'label' => 'LBL_LIST_TEAM',
            'displayParams' => 
            array (
              'display' => true,
            ),
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
        ),
      ),
    ),
  ),
);
?>
