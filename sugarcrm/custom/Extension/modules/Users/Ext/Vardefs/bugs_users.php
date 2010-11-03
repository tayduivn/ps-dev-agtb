<?php


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
