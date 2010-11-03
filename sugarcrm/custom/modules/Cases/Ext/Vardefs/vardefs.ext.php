<?php 
 //WARNING: The contents of this file are auto-generated


//DEE CUSTOMIZATION - making assigned to user a required field
$dictionary['Case']['fields']['assigned_user_name'] =   array (
      'name' => 'assigned_user_name',
      'link' => 'assigned_user_link',
      'vname' => 'LBL_ASSIGNED_TO_NAME',
      'rname' => 'user_name',
      'type' => 'relate',
      'reportable' => false,
      'source' => 'non-db',
      'table' => 'users',
      'id_name' => 'assigned_user_id',
      'module' => 'Users',
      'duplicate_merge' => 'disabled',
      'required' => true,	
);
//END DEE CUSTOMIZATION



$dictionary["Case"]["fields"]["kbdocuments"] = array (
  'name' => 'kbdocuments',
  'type' => 'link',
  'relationship' => 'cases_kbdocuments',
  'source' => 'non-db',
);



// created: 2010-10-11 16:22:27
$dictionary["Case"]["fields"]["e1_escalations_cases"] = array (
  'name' => 'e1_escalations_cases',
  'type' => 'link',
  'relationship' => 'e1_escalations_cases',
  'source' => 'non-db',
  'vname' => 'LBL_E1_ESCALATIONS_CASES_FROM_E1_ESCALATIONS_TITLE',
);



$dictionary['Case']['indices'][] = array('name' =>'idx_cases_ft', 'type' =>'fulltext', 'fields'=>array('description', 'name', 'resolution'));



$dictionary['Case']['fields']['case_score'] = array (
    'name' => 'case_score',
    'vname' => 'LBL_CASE_SCORE',
    'type' => 'int',
    'size' => '10',
    'required' => true,
    'default' => '0',
    'reportable' => true,
    'audited' => false,
);


//DEE CUSTOMIZATION - ADDING CHECKBOX TO TRACK MY ESCALATED CASES
$dictionary['Case']['fields']['escalate_case'] =   array (
	'massupdate' => false,
       	'name' => 'escalate_case',
        'vname' => 'LBL_ESCALATE_CASE',
        'type' => 'bool',
        'source' => 'non-db',
        'comment' => 'Escalate this case',
	'reportable' => true,
);

$dictionary['Case']['fields']['user_escalation'] =   array (
        'name' => 'user_escalation',
        'vname' => 'LBL_USER_ESCALATION',
        'type' => 'link',
	'relationship' => 'cases_users',
        'source' => 'non-db',
	'reportable' => true,
);
//END DEE CUSTOMIZATION



// Putting this in for IT Request 4615 - There are old relationships stored in the db (for workflow?) that refer to this old relationship
//                                       which was changed from 'account' to 'accounts' in 5.0 for consistency reasons
//                                       Since we still refer to the old ones, we need to maintain this until they are removed
$dictionary['Case']['fields']['account'] = array(
      'name' => 'account',
      'type' => 'link',
      'relationship' => 'account_cases',
      'link_type' => 'one',
      'side' => 'right',
      'source' => 'non-db',
      'vname' => 'LBL_ACCOUNT',
);



//BEGIN CASES VARDEFS 
// adding project field
$dictionary['Case']['fields']['projects'] =   array (
    'name' => 'projects',
    'type' => 'link',
    'relationship' => 'projects_cases',
    'source'=>'non-db',
    'vname'=>'LBL_PROJECTS',
);
//END CASES VARDEFS


//BEGIN SADEK
$dictionary['Case']['fields']['itrequests'] =   array (
    'name' => 'itrequests',
    'type' => 'link',
    'relationship' => 'itrequests_cases',
    'module'=>'ITRequests',
    'bean_name'=>'ITRequest',
    'source'=>'non-db',
    'vname'=>'LBL_ITREQUESTS',
);
//END SADEK









/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add relationship between Cases and User
*/

$dictionary['Case']['fields']['users'] =   array (
    'name' => 'users',
    'type' => 'link',
    'relationship' => 'cases_users',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
    'vname'=>'LBL_USERS',
);		
		




// created: 2010-10-11 16:24:26
$dictionary["Case"]["fields"]["e1_escalations_cases_1"] = array (
  'name' => 'e1_escalations_cases_1',
  'type' => 'link',
  'relationship' => 'e1_escalations_cases_1',
  'source' => 'non-db',
  'vname' => 'LBL_E1_ESCALATIONS_CASES_1_FROM_E1_ESCALATIONS_TITLE',
);


 // created: 2010-11-01 15:40:50
$dictionary['Case']['fields']['exclude_from_stats_c']['enforced']='false';

 
?>