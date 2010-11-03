<?php
// created: 2008-10-06 05:00:56
$dictionary["E1_Escalations"]["fields"]["bugs_e1_escalations"] = array (
  'name' => 'bugs_e1_escalations',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);
?>
<?php
// created: 2008-10-06 05:00:56
$dictionary["E1_Escalations"]["fields"]["bugs_e1_escalations_name"] = array (
  'name' => 'bugs_e1_escalations_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_BUGS_E1_ESCALATIONS_FROM_BUGS_TITLE',
  'save' => true,
  'id_name' => 'bugs_e1_escationsbugs_ida',
  'link' => 'bugs_e1_escalations',
  'table' => 'bugs',
  'module' => 'Bugs',
  'rname' => 'name',
);
?>
<?php
// created: 2008-10-06 05:00:56
$dictionary["E1_Escalations"]["fields"]["bugs_e1_escationsbugs_ida"] = array (
  'name' => 'bugs_e1_escationsbugs_ida',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);
?>
