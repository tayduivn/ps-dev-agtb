<?php
// GENERATED AUTOMATICALLY
$scoreConfig['Touchpoints'] = array (
  'enabled' => true,
  'apply_mult' => 'record',
  'rules' => 
  array (
    'Cc6c3' => 
    array (
      'prefix' => 'Cc6c3',
      'module' => 'Touchpoints',
      'weight' => '1',
      'enabled' => false,
      'ruleClass' => 'TextRule',
      'field' => 'primary_address_state',
      'fieldLabel' => 'LBL_PRIMARY_ADDRESS_STATE',
      'rows' => 
      array (
        0 => 
        array (
          'value' => '_DEFAULT',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
    ),
    'C88fd' => 
    array (
      'prefix' => 'C88fd',
      'module' => 'Touchpoints',
      'weight' => '1',
      'enabled' => true,
      'ruleClass' => 'CheckboxRule',
      'field' => 'third_party_validation_c',
      'fieldLabel' => 'third_party_validation_c',
      'rows' => 
      array (
        'CHECKED' => 
        array (
          'value' => '_CHECKED',
          'score' => '100',
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
    ),
    't90c' => 
    array (
      'prefix' => 't90c',
      'module' => 'Touchpoints',
      'enabled' => true,
      'weight' => 1,
      'ruleClass' => 'CampaignRule',
    ),
    'te06' => 
    array (
      'prefix' => 'te06',
      'module' => 'Touchpoints',
      'enabled' => true,
      'ruleClass' => 'CheckboxRule',
      'field' => 'call_back_c',
      'fieldLabel' => 'Call_Back_c',
      'rows' => 
      array (
        'CHECKED' => 
        array (
          'value' => '_CHECKED',
          'score' => '4000',
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
      'weight' => '1',
    ),
    'tb77' => 
    array (
      'prefix' => 'tb77',
      'module' => 'Touchpoints',
      'enabled' => true,
      'ruleClass' => 'DropdownRule',
      'field' => 'primary_address_country',
      'fieldLabel' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
      'fieldOptions' => 'countries_dom',
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
          'value' => 'USA',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        2 => 
        array (
          'value' => 'CANADA',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        3 => 
        array (
          'value' => 'UNITED KINGDOM',
          'score' => '75',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        4 => 
        array (
          'value' => 'AUSTRALIA',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        5 => 
        array (
          'value' => 'FRANCE',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        6 => 
        array (
          'value' => 'ITALY',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        7 => 
        array (
          'value' => 'JAPAN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        8 => 
        array (
          'value' => 'NETHERLANDS',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        9 => 
        array (
          'value' => 'SPAIN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        10 => 
        array (
          'value' => 'SWITZERLAND',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        11 => 
        array (
          'value' => 'SWEDEN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        12 => 
        array (
          'value' => 'SOUTH AFRICA',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        13 => 
        array (
          'value' => 'DUBAI',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        14 => 
        array (
          'value' => 'UNITED ARAB EMIRATES',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        15 => 
        array (
          'value' => 'GERMANY',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => false,
        ),
        16 => 
        array (
          'value' => 'NEW ZEALAND',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => false,
        ),
      ),
      'weight' => '1',
    ),
    't62f' => 
    array (
      'prefix' => 't62f',
      'module' => 'Touchpoints',
      'enabled' => true,
      'ruleClass' => 'DropdownRule',
      'field' => 'purchasing_timeline',
      'fieldLabel' => 'LBL_PURCHASING_TIMELINE',
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
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        2 => 
        array (
          'value' => 'Within 3 Months',
          'score' => '150',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        3 => 
        array (
          'value' => 'Within 6 Months',
          'score' => '75',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        4 => 
        array (
          'value' => 'Within a Year',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        5 => 
        array (
          'value' => 'Just Researching',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
      'weight' => '1',
    ),
    't76c' => 
    array (
      'prefix' => 't76c',
      'module' => 'Touchpoints',
      'enabled' => true,
      'ruleClass' => 'TextRule',
      'field' => 'title',
      'fieldLabel' => 'LBL_TITLE',
      'fieldOptions' => NULL,
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
          'value' => 'CEO/President/Gen Mgr',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        2 => 
        array (
          'value' => 'CTO/CIO',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        3 => 
        array (
          'value' => 'VP, Marketing',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        4 => 
        array (
          'value' => 'VP, Sales',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        5 => 
        array (
          'value' => 'VP, Interactive/E-Business',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        6 => 
        array (
          'value' => 'VP, IT/IS/MIS',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        7 => 
        array (
          'value' => 'VP, Engineering',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        8 => 
        array (
          'value' => 'VP, Customer Support',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        9 => 
        array (
          'value' => 'VP, Product Development',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        10 => 
        array (
          'value' => 'CFO/VP, Finance',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        11 => 
        array (
          'value' => 'Dir/Mgr, Marketing',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        12 => 
        array (
          'value' => 'Dir/Mgr, Sales',
          'score' => '200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        13 => 
        array (
          'value' => 'Dir/Mgr, Interactive/E-Business',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        14 => 
        array (
          'value' => 'Dir/Mgr, IT/IS/MIS',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        15 => 
        array (
          'value' => 'Dir/Mgr, Engineering',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        16 => 
        array (
          'value' => 'Dir/Mgr, Customer Support',
          'score' => '100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        17 => 
        array (
          'value' => 'Dir/Mgr, Product Development',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        18 => 
        array (
          'value' => 'Dir/Mgr, Finance',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        19 => 
        array (
          'value' => 'Government',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        20 => 
        array (
          'value' => 'Industry Analyst',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        21 => 
        array (
          'value' => 'Student/Researcher',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
      'weight' => '1',
    ),
  ),
);