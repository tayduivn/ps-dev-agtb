<?php
// created: 2010-07-27 14:46:59
$dictionary["Opportunity"]["fields"]["discountcodes_opportunities"] = array (
  'name' => 'discountcodes_opportunities',
  'type' => 'link',
  'relationship' => 'discountcodes_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Opportunity"]["fields"]["discountcodes_opportunities_name"] = array (
  'name' => 'discountcodes_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco8282ntcodes_ida',
  'link' => 'discountcodes_opportunities',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'name',
);
$dictionary["Opportunity"]["fields"]["discountco8282ntcodes_ida"] = array (
  'name' => 'discountco8282ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);
