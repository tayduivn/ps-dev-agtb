<?php
// created: 2011-02-17 07:38:18
$dictionary["ibm_WinPlanSWG"]["fields"]["opportunities_ibm_winplanswg"] = array (
  'name' => 'opportunities_ibm_winplanswg',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplanswg',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSWG_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["ibm_WinPlanSWG"]["fields"]["opportunities_ibm_winplanswg_name"] = array (
  'name' => 'opportunities_ibm_winplanswg_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSWG_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'opportunitb5cfunities_ida',
  'link' => 'opportunities_ibm_winplanswg',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["ibm_WinPlanSWG"]["fields"]["opportunitb5cfunities_ida"] = array (
  'name' => 'opportunitb5cfunities_ida',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplanswg',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANSWG_FROM_IBM_WINPLANSWG_TITLE',
);
