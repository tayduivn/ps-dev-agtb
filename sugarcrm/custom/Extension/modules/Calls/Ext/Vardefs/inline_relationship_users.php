<?php
$dictionary['Call']['fields']['inline_users'] = array(
    'name' => 'inline_users',
    'vname' => 'LBL_INLINE_USERS',
    'type' => 'InlineOneToMany',
    'massupdate' => false,
    'source' => 'non-db',
	'inline_module' => 'Users',
	'inline_link_table' => 'calls_users',
	'inline_parent_link_field' => 'call_id',
	'inline_child_link_field' => 'user_id',
);
