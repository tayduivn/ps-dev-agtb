<?php
// created: 2010-07-27 14:46:32
$dictionary["Account"]["fields"]["discountcodes_accounts"] = array (
  'name' => 'discountcodes_accounts',
  'type' => 'link',
  'relationship' => 'discountcodes_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Account"]["fields"]["discountcodes_accounts_name"] = array (
  'name' => 'discountcodes_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco4170ntcodes_ida',
  'link' => 'discountcodes_accounts',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'name',
);
$dictionary["Account"]["fields"]["discountco4170ntcodes_ida"] = array (
  'name' => 'discountco4170ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_ACCOUNTS_TITLE',
);
