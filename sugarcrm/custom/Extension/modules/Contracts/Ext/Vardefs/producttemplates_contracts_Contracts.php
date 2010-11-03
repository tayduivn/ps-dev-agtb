<?php
// created: 2010-07-20 07:36:58
$dictionary["Contract"]["fields"]["producttemplates_contracts"] = array (
  'name' => 'producttemplates_contracts',
  'type' => 'link',
  'relationship' => 'producttemplates_contracts',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_PRODUCTTEMPLATES_TITLE',
);
$dictionary["Contract"]["fields"]["producttemplates_contracts_name"] = array (
  'name' => 'producttemplates_contracts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_PRODUCTTEMPLATES_TITLE',
  'save' => true,
  'id_name' => 'producttemf7aamplates_ida',
  'link' => 'producttemplates_contracts',
  'table' => 'product_templates',
  'module' => 'ProductTemplates',
  'rname' => 'name',
);
$dictionary["Contract"]["fields"]["producttemf7aamplates_ida"] = array (
  'name' => 'producttemf7aamplates_ida',
  'type' => 'link',
  'relationship' => 'producttemplates_contracts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_PRODUCTTEMPLATES_CONTRACTS_FROM_CONTRACTS_TITLE',
);
