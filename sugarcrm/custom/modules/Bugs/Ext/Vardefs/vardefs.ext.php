<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2008-10-06 05:00:56
$dictionary["Bug"]["fields"]["bugs_e1_escalations"] = array (
  'name' => 'bugs_e1_escalations',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);


// created: 2009-06-08 17:15:59
$dictionary["Bug"]["fields"]["bugs_bugs"] = array (
  'name' => 'bugs_bugs',
  'type' => 'link',
  'relationship' => 'bugs_bugs',
  'source' => 'non-db',
);


// created: 2009-06-08 17:15:59
$dictionary["Bug"]["fields"]["bugs_bugs"] = array (
  'name' => 'bugs_bugs',
  'type' => 'link',
  'relationship' => 'bugs_bugs',
  'source' => 'non-db',
);



 // created: 2010-06-17 13:31:13
$dictionary['Bug']['fields']['work_log']['rows']='12';
$dictionary['Bug']['fields']['work_log']['cols']='120';

 


$dictionary['Bug']['indices'][] = array('name' =>'bugs_ft', 'type' =>'fulltext', 'fields'=>array('name', 'description', 'work_log'));


// created: 2008-10-06 05:00:56
$dictionary["Bug"]["fields"]["bugs_e1_escalations"] = array (
  'name' => 'bugs_e1_escalations',
  'type' => 'link',
  'relationship' => 'bugs_e1_escalations',
  'source' => 'non-db',
);



//BEGIN BUGS VARDEFS 
// adding project field
$dictionary['Bug']['fields']['projects'] = array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_bugs',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END BUGS VARDEFS


//BEGIN SADEK
$dictionary['Bug']['fields']['itrequests'] =   array (
    'name' => 'itrequests',
    'type' => 'link',
    'relationship' => 'itrequests_bugs',
    'module'=>'ITRequests',
    'bean_name'=>'ITRequest',
    'source'=>'non-db',
    'vname'=>'LBL_ITREQUESTS',
);

//END SADEK



// created: 2009-11-18 15:33:24
$dictionary["Bug"]["fields"]["spec_usecases_bugs"] = array (
  'name' => 'spec_usecases_bugs',
  'type' => 'link',
  'relationship' => 'spec_usecases_bugs',
  'source' => 'non-db',
  'vname' => 'LBL_SPEC_USECASES_BUGS_FROM_SPEC_USECASES_TITLE',
);



 // created: 2010-06-17 13:29:41
$dictionary['Bug']['fields']['description']['rows']='12';
$dictionary['Bug']['fields']['description']['cols']='120';

 

 // created: 2010-10-14 13:46:04

 



/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$dictionary['Bug']['fields']['users'] =   array (
    'name' => 'users',
    'type' => 'link',
    'relationship' => 'bugs_users',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
    'vname'=>'LBL_USERS',
);		
		




 // created: 2010-10-07 13:50:38

 

 // created: 2010-10-14 11:08:38

 

 // created: 2010-10-18 10:01:32

 

 // created: 2010-10-18 13:03:30
$dictionary['Bug']['fields']['feature_backlog_priority_num_c']['enforced']='false';

 
?>