<?php
$dictionary['Call']['fields']['inline_contacts'] = array(
    'name' => 'inline_contacts',
    'vname' => 'LBL_INLINE_CONTACTS',
    'type' => 'InlineOneToMany',
    'massupdate' => false,
    'source' => 'non-db',
	'inline_module' => 'Contacts',
	'inline_link_table' => 'calls_contacts',
	'inline_parent_link_field' => 'call_id',
	'inline_child_link_field' => 'contact_id',
);
