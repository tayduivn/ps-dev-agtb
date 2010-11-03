<?php
$module_name = 'P1_Partners';
$_module_name = 'p1_partners';
$searchdefs [$module_name] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'account_name',
      1 => 'name',
      2 => 
      array (
        'name' => 'opportunity_type',
        'displayParams' => 
        array (
          'size' => 1,
        ),
      ),
      3 => 'date_closed',
      4 => 'next_step_due_date',
      5 => 
      array (
        'name' => 'sixtymin_opp_c',
        'type' => 'bool',
      ),
      6 => 
      array (
        'name' => 'sales_stage',
        'displayParams' => 
        array (
          'size' => 1,
        ),
      ),
      7 => 'evaluation',
      8 => 'evaluation_start_date',
      9 => 'Evaluation_Close_Date_c',
      10 => 'account_billing_country',
      11 => 'conflict_c',
    ),
    'advanced_search' => 
    array (
      'sales_stage' => 
      array (
        'name' => 'sales_stage',
        'label' => 'LBL_SALES_STAGE',
        'default' => true,
        'width' => '10%',
        'displayParams' => 
        array (
          'size' => 4,
        ),
      ),
      'opportunity_type' => 
      array (
        'width' => '10%',
        'label' => 'LBL_TYPE',
        'sortable' => false,
        'default' => true,
        'name' => 'opportunity_type',
        'displayParams' => 
        array (
          'size' => 4,
        ),
      ),
      'revenue_type_c' => 
      array (
        'name' => 'Revenue_Type_c',
        'label' => 'Revenue_Type__c',
        'default' => true,
        'width' => '10%',
        'displayParams' => 
        array (
          'size' => 4,
        ),
      ),
      'account_name' => 
      array (
        'name' => 'account_name',
        'label' => 'LBL_ACCOUNT_NAME',
        'default' => true,
        'width' => '10%',
      ),
      'partner_assigned_to_c' => 
      array (
        'width' => '10%',
        'default' => true,
        'name' => 'partner_assigned_to_c',
        'displayParams' => 
        array (
          'size' => 4,
        ),
      ),
      'accepted_by_partner_c' => 
      array (
        'name' => 'accepted_by_partner_c',
        'label' => 'LBL_ACCEPTED_BY_PARTNER',
        'default' => true,
        'width' => '10%',
        'displayParams' => 
        array (
          'size' => 4,
        ),
      ),
      'date_closed' => 
      array (
        'name' => 'date_closed',
        'default' => true,
        'width' => '10%',
      ),
      'next_step_due_date' => 
      array (
        'name' => 'next_step_due_date',
        'default' => true,
        'width' => '10%',
      ),
      'campaign_name' => 
      array (
        'name' => 'campaign_name',
        'default' => true,
        'width' => '10%',
      ),
      'score_c' => 
      array (
        'name' => 'score_c',
        'default' => true,
        'width' => '10%',
      ),
      'conflict_c' => 
      array (
        'name' => 'conflict_c',
        'default' => true,
        'width' => '10%',
      ),
      'top20deal_c' => 
      array (
        'type' => 'bool',
        'default' => true,
        'label' => 'LBL_TOP20DEAL',
        'width' => '10%',
        'name' => 'top20deal_c',
      ),
      'amount' => 
      array (
        'name' => 'amount',
        'default' => true,
        'width' => '10%',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'type' => 'enum',
        'label' => 'LBL_ASSIGNED_TO',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
        'width' => '10%',
        'default' => true,
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '15',
    ),
  ),
);
?>
