<?php
// created: 2011-02-17 06:11:18
$dictionary["ibm_WinPlanGeneric"]["fields"]["opportunities_ibm_winplangeneric"] = array (
  'name' => 'opportunities_ibm_winplangeneric',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplangeneric',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANGENERIC_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["ibm_WinPlanGeneric"]["fields"]["opportunities_ibm_winplangeneric_name"] = array (
  'name' => 'opportunities_ibm_winplangeneric_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANGENERIC_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'opportuniteefdunities_ida',
  'link' => 'opportunities_ibm_winplangeneric',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["ibm_WinPlanGeneric"]["fields"]["opportuniteefdunities_ida"] = array (
  'name' => 'opportuniteefdunities_ida',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_winplangeneric',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_OPPORTUNITIES_IBM_WINPLANGENERIC_FROM_IBM_WINPLANGENERIC_TITLE',
);
