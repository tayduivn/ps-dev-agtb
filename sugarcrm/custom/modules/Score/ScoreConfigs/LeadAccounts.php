<?php
// GENERATED AUTOMATICALLY
$scoreConfig['LeadAccounts'] = array (
  'enabled' => true,
  'apply_mult' => 'record',
  'rules' => 
  array (
    '9e06' => 
    array (
      'prefix' => '9e06',
      'module' => 'LeadAccounts',
      'enabled' => true,
      'ruleClass' => 'CheckboxRule',
      'field' => 'call_back_c',
      'fieldLabel' => 'call_back_c',
      'rows' => 
      array (
        'CHECKED' => 
        array (
          'value' => '_CHECKED',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        'UNCHECKED' => 
        array (
          'value' => '_UNCHECKED',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
      'weight' => '50',
    ),
    '4b77' => 
    array (
      'prefix' => '4b77',
      'module' => 'LeadAccounts',
      'enabled' => true,
      'ruleClass' => 'DropdownRule',
      'field' => 'billing_address_country',
      'fieldLabel' => 'LBL_BILLING_ADDRESS_COUNTRY',
      'fieldOptions' => 'countries_dom',
      'rows' => 
      array (
        0 => 
        array (
          'value' => '_DEFAULT',
          'score' => '3',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        1 => 
        array (
          'value' => 'USA',
          'score' => '5',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        2 => 
        array (
          'value' => 'CANADA',
          'score' => '4',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
      'weight' => '5',
    ),
    'b62f' => 
    array (
      'prefix' => 'b62f',
      'module' => 'LeadAccounts',
      'enabled' => true,
      'ruleClass' => 'DropdownRule',
      'field' => 'purchasing_timeline_c',
      'fieldLabel' => 'LBL_PURCHASING_TIMELINE_c',
      'fieldOptions' => 'Purchasing Timeline',
      'rows' => 
      array (
        0 => 
        array (
          'value' => '_DEFAULT',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        1 => 
        array (
          'value' => 'Immediately',
          'score' => '75',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        2 => 
        array (
          'value' => 'Within 3 Months',
          'score' => '60',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        3 => 
        array (
          'value' => 'Within 6 Months',
          'score' => '45',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        4 => 
        array (
          'value' => 'Within a Year',
          'score' => '30',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        5 => 
        array (
          'value' => 'Just Researching',
          'score' => '15',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
      'weight' => '1',
    ),
  ),
);