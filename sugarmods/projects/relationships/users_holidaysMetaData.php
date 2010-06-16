<?php
// adding user-to-holiday relationship
$dictionary['users_holidays'] = array (
    'table' => 'users_holidays',
    'fields' => array (
        array('name' => 'id', 'type' => 'varchar', 'len' => '36'),
        array('name' => 'user_id', 'type' => 'varchar', 'len' => '36'),
        array('name' => 'holiday_id', 'type' => 'varchar', 'len' => '36'),
        array('name' => 'date_modified', 'type' => 'datetime'),
        array('name' => 'deleted', 'type' => 'bool', 'len' => '1', 'default' => '0', 'required' => true),
    ),
    'indices' => array (
        array('name' => 'users_holidays_pk', 'type' =>'primary', 'fields'=>array('id')),
        array('name' => 'idx_user_holi_user', 'type' =>'index', 'fields'=>array('user_id')),
        array('name' => 'idx_user_holi_holi', 'type' =>'index', 'fields'=>array('holiday_id')),
        array('name' => 'users_quotes_alt', 'type'=>'alternate_key', 'fields'=>array('user_id','holiday_id')),
    ),
    'relationships' => array (
        'users_holidays' => array(
			'lhs_module' => 'Users', 
			'lhs_table' => 'users', 
			'lhs_key' => 'id',
			'rhs_module' => 'Holidays', 
			'rhs_table' => 'holidays', 
			'rhs_key' => 'person_id',
			'relationship_type' => 'one-to-many', 
			'relationship_role_column' => 'related_module', 
			'relationship_role_column_value' => NULL,
		),
    ),
);

?>
