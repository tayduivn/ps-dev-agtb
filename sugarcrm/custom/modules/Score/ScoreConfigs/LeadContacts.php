<?php
// GENERATED AUTOMATICALLY
$scoreConfig['LeadContacts'] = array (
  'enabled' => true,
  'apply_mult' => 'record',
  'rules' => 
  array (
    'Cc109' => 
    array (
      'prefix' => 'Cc109',
      'module' => 'LeadContacts',
      'weight' => '1',
      'enabled' => false,
      'ruleClass' => 'CheckboxRule',
      'field' => 'invalid_email',
      'fieldLabel' => 'LBL_INVALID_EMAIL',
      'rows' => 
      array (
        'CHECKED' => 
        array (
          'value' => '_CHECKED',
          'score' => '-200',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        'UNCHECKED' => 
        array (
          'value' => '_UNCHECKED',
          'score' => '0',
          'mul' => '0.0%',
          'enabled' => false,
        ),
      ),
    ),
    'Cb8d4' => 
    array (
      'prefix' => 'Cb8d4',
      'module' => 'LeadContacts',
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
    'C94c4' => 
    array (
      'prefix' => 'C94c4',
      'module' => 'LeadContacts',
      'weight' => '1',
      'enabled' => true,
      'ruleClass' => 'CheckboxRule',
      'field' => 'call_back_c',
      'fieldLabel' => 'LBL_CALL_BACK',
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
    ),
    'C91f2' => 
    array (
      'prefix' => 'C91f2',
      'module' => 'LeadContacts',
      'weight' => '1',
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
          'enabled' => 'true',
        ),
        4 => 
        array (
          'value' => 'GERMANY',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        5 => 
        array (
          'value' => 'FRANCE',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        6 => 
        array (
          'value' => 'ITALY',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        7 => 
        array (
          'value' => 'JAPAN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        8 => 
        array (
          'value' => 'NETHERLANDS',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        9 => 
        array (
          'value' => 'SPAIN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        10 => 
        array (
          'value' => 'SWITZERLAND',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        11 => 
        array (
          'value' => 'SWEDEN',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        12 => 
        array (
          'value' => 'SOUTH AFRICA',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        13 => 
        array (
          'value' => 'DUBAI',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        14 => 
        array (
          'value' => 'UNITED ARAB EMIRATES',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        15 => 
        array (
          'value' => 'AUSTRALIA',
          'score' => '50',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
        16 => 
        array (
          'value' => 'NEW ZEALAND',
          'score' => '25',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
    ),
    'Cb6d6' => 
    array (
      'prefix' => 'Cb6d6',
      'module' => 'LeadContacts',
      'weight' => '1',
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
    ),
    'Ca02e' => 
    array (
      'prefix' => 'Ca02e',
      'module' => 'LeadContacts',
      'weight' => '1',
      'enabled' => true,
      'ruleClass' => 'TextRule',
      'field' => 'title',
      'fieldLabel' => 'LBL_TITLE',
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
          'score' => '100',
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
          'score' => '-100',
          'mul' => '0.0%',
          'enabled' => 'true',
        ),
      ),
    ),
  ),
);