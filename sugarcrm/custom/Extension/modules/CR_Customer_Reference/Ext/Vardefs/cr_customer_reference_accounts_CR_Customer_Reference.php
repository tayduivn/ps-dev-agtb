<?php
// created: 2010-07-21 13:42:19
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_accounts"] = array (
  'name' => 'cr_customer_reference_accounts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'required' => true,
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_accounts_name"] = array (
  'name' => 'cr_customer_reference_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'save' => true,
  'id_name' => 'cr_custome6b53ccounts_idb',
  'link' => 'cr_customer_reference_accounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
  'required' => true,
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_custome6b53ccounts_idb"] = array (
  'name' => 'cr_custome6b53ccounts_idb',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'required' => true,
);
