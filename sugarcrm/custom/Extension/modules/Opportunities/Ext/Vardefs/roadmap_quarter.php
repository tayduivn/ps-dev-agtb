<?php

$dictionary['Opportunity']['fields']['roadmap_quarter'] =
    array (
      'type' => 'enum',
      'options' => 'ibm_qtr_yr_list',
      'enforced' => 'false',
      'required' => false,
      'name' => 'roadmap_quarter',
      'vname' => 'LBL_ROADMAP_QUARTER',
      'massupdate' => '0',
      'default' => NULL,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => false,
      'reportable' => true,
      'calculated' => false,
      'len' => '255',
      'size' => '20',
    );
