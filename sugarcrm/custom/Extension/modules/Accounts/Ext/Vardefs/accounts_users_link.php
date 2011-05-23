<?php

$dictionary['Account']['fields']['users'] = array (
    'name' => 'users',
    'type' => 'link',
    'relationship' => 'accounts_users',
    'source'=>'non-db',
	'module'=>'Users',
	'bean_name'=>'User',
	'rel_fields'=>array('user_role'=>array('type'=>'enum', 'options'=>'accounts_users_roles_dom')),
    'vname'=>'LBL_ACCOUNT_TEAM',
);
