<?php

$dictionary['activities_users'] = array(
    'table' => 'activities_users',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'len' => 36,
            'required' => true,
        ),

        'activity_id' => array(
            'name' => 'activity_id',
            'type' => 'id',
            'len' => 36,
            'required' => true,
        ),

        'parent_type' => array(
            'name' => 'parent_type',
            'type' => 'varchar',
            'len'  => 100,
        ),

        'parent_id' => array(
            'name'     => 'parent_id',
            'type'     => 'id',
            'len'      => 36,
        ),

        'fields' => array(
            'name' => 'fields',
            'type' => 'json',
            'dbType' => 'longtext',
            'required' => true,
        ),

        'deleted' => array (
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'default' => '0',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'activities_users_pk',
            'type' => 'primary',
            'fields' => array('id'),
        ),
        array(
            'name' => 'activities_records',
            'type' => 'index',
            'fields' => array('parent_type', 'parent_id'),
        ),
    ),

    'relationships' => array(
        'activities_users' => array(
            'lhs_module' => 'Activities',
            'lhs_table' => 'activities',
            'lhs_key' => 'id',
            'rhs_module' => 'Users',
            'rhs_table' => 'users',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'activities_users',
            'join_key_lhs' => 'activity_id',
            'join_key_rhs' => 'parent_id',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Users'
        ),
    )
);
