<?php
$viewdefs ['Contracts'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
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
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_CONTRACT_NAME',
          ),
          1 => 
          array (
            'name' => 'start_date',
            'comment' => 'The effective date of the contract',
            'label' => 'LBL_START_DATE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'agreement_type_c',
            'studio' => 'visible',
            'label' => 'LBL_AGREEMENT_TYPE',
          ),
          1 => 
          array (
            'name' => 'end_date',
            'comment' => 'The date in which the contract is no longer effective',
            'label' => 'LBL_END_DATE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'comment' => 'The contract status',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'contract_term_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTRACT_TERM',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
          1 => 
          array (
            'name' => 'currency_name',
            'comment' => 'Currency name used for Meta-data framework',
            'label' => 'LBL_CURRENCY',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'opportunity_name',
            'label' => 'LBL_OPPORTUNITY',
          ),
          1 => 
          array (
            'name' => 'orders_contracts_name',
            'label' => 'LBL_ORDERS_CONTRACTS_FROM_ORDERS_TITLE',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'moofcart_agreement_type_c',
            'studio' => 'visible',
            'label' => 'LBL_MOOFCART_AGREEMENT_TYPE',
          ),
          1 => 
          array (
            'name' => 'signing_method_c',
            'studio' => 'visible',
            'label' => 'LBL_SIGNING_METHOD',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'execution_status_c',
            'studio' => 'visible',
            'label' => 'LBL_EXECUTION_STATUS',
          ),
          1 => 
          array (
            'name' => 'products_contracts_name',
            'label' => 'LBL_PRODUCTS_CONTRACTS_FROM_PRODUCTS_TITLE',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value}&nbsp;{$APP.LBL_BY}&nbsp;{$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value}&nbsp;{$APP.LBL_BY}&nbsp;{$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
      ),
    ),
  ),
);
?>
