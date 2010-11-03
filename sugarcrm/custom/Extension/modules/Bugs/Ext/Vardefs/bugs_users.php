<?php


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
		

?>
