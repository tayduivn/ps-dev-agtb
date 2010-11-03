<?php


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
		

?>
