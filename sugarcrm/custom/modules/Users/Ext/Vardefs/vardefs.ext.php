<?php 
 //WARNING: The contents of this file are auto-generated


//BEGIN USER VARDEFS 
// adding holiday field
$dictionary['User']['fields']['holidays'] = array(
  	'name' => 'holidays',
  	'type' => 'link',
  	'relationship' => 'users_holidays',
  	'source' => 'non-db',
  	'side' => 'right',
  	'vname' => 'LBL_HOLIDAYS',
  );

//END USER VARDEFS


//BEGIN SADEK
$dictionary['User']['fields']['itrequests'] =   array (
    'name' => 'itrequests',
    'type' => 'link',
    'relationship' => 'itrequests_users',
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
** Description: Add user relationship between Cases and User
*/
		
$dictionary['User']['fields']['cases'] =   array (
    'name' => 'cases',
    'type' => 'link',
    'relationship' => 'cases_users',
    'module'=>'Cases',
    'bean_name'=>'Case',
    'source'=>'non-db',
    'vname'=>'LBL_CASES',
);		







/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/
		
$dictionary['User']['fields']['bugs'] =   array (
    'name' => 'bugs',
    'type' => 'link',
    'relationship' => 'bugs_users',
    'module'=>'Bugs',
    'bean_name'=>'Bug',
    'source'=>'non-db',
    'vname'=>'LBL_BUGS',
);		




?>