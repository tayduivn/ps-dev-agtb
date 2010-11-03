<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2008-10-06 05:00:56
$dictionary["E1_Escalations"]["fields"]["bugs_e1_escalations"] = array (
  'name' => 'bugs_e1_escalations',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);


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


// created: 2008-10-06 05:00:56
$dictionary["E1_Escalations"]["fields"]["bugs_e1_escationsbugs_ida"] = array (
  'name' => 'bugs_e1_escationsbugs_ida',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);



// created: 2010-10-11 16:22:27
$dictionary["E1_Escalations"]["fields"]["e1_escalations_cases"] = array (
  'name' => 'e1_escalations_cases',
  'type' => 'link',
  'relationship' => 'e1_escalations_cases',
  'source' => 'non-db',
  'vname' => 'LBL_E1_ESCALATIONS_CASES_FROM_CASES_TITLE',
);


// created: 2010-10-11 16:24:26
$dictionary["E1_Escalations"]["fields"]["e1_escalations_cases_1"] = array (
  'name' => 'e1_escalations_cases_1',
  'type' => 'link',
  'relationship' => 'e1_escalations_cases_1',
  'source' => 'non-db',
  'vname' => 'LBL_E1_ESCALATIONS_CASES_1_FROM_CASES_TITLE',
);


 // created: 2010-10-11 16:27:59
$dictionary['E1_Escalations']['fields']['source']['calculated']=false;
$dictionary['E1_Escalations']['fields']['source']['dependency']=false;

 

 // created: 2010-11-01 08:57:26

 

 // created: 2010-10-11 16:30:52

 
?>