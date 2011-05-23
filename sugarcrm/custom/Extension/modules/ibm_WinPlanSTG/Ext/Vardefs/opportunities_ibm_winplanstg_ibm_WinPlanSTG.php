<?php
// created: 2011-02-17 07:37:10
$dictionary["ibm_WinPlanSTG"]["fields"]["opportunities_ibm_winplanstg"] = array (
  'name' => 'opportunities_ibm_winplanstg',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplanstg',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSTG_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["ibm_WinPlanSTG"]["fields"]["opportunities_ibm_winplanstg_name"] = array (
  'name' => 'opportunities_ibm_winplanstg_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSTG_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'opportunit8b0bunities_ida',
  'link' => 'opportunities_ibm_winplanstg',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["ibm_WinPlanSTG"]["fields"]["opportunit8b0bunities_ida"] = array (
  'name' => 'opportunit8b0bunities_ida',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplanstg',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSTG_FROM_IBM_WINPLANSTG_TITLE',
);
