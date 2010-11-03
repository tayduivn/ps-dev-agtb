<?php


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


?>
