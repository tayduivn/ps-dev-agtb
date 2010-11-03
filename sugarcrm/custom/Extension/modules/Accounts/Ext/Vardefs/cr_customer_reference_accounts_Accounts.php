<?php
// created: 2010-07-21 13:42:19
$dictionary["Account"]["fields"]["cr_customer_reference_accounts"] = array (
  'name' => 'cr_customer_reference_accounts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);
$dictionary["Account"]["fields"]["cr_customer_reference_accounts_name"] = array (
  'name' => 'cr_customer_reference_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
  'save' => true,
  'id_name' => 'cr_custome30f7ference_ida',
  'link' => 'cr_customer_reference_accounts',
  'table' => 'cr_customer_reference',
  'module' => 'CR_Customer_Reference',
  'rname' => 'name',
);
$dictionary["Account"]["fields"]["cr_custome30f7ference_ida"] = array (
  'name' => 'cr_custome30f7ference_ida',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);
