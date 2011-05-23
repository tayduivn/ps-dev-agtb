<?php
// created: 2011-02-09 23:01:45
$dictionary["ibm_revenueLineItems"]["fields"]["ibm_revenuelineitems_opportunities"] = array (
  'name' => 'ibm_revenuelineitems_opportunities',
  'type' => 'link',
  'relationship' => 'ibm_revenuelineitems_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_IBM_REVENUELINEITEMS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["ibm_revenueLineItems"]["fields"]["ibm_revenuelineitems_opportunities_name"] = array (
  'name' => 'ibm_revenuelineitems_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_IBM_REVENUELINEITEMS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'ibm_revenud375unities_ida',
  'link' => 'ibm_revenuelineitems_opportunities',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["ibm_revenueLineItems"]["fields"]["ibm_revenuelineitems_opportunities_id"] = array (
  'name' => 'ibm_revenuelineitems_opportunities_id',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_IBM_REVENUELINEITEMS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'ibm_revenud375unities_ida',
  'link' => 'ibm_revenuelineitems_opportunities',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'id',
);
$dictionary["ibm_revenueLineItems"]["fields"]["ibm_revenud375unities_ida"] = array (
  'name' => 'ibm_revenud375unities_ida',
  'type' => 'link',
  'relationship' => 'ibm_revenuelineitems_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_IBM_REVENUELINEITEMS_OPPORTUNITIES_FROM_IBM_REVENUELINEITEMS_TITLE',
);
