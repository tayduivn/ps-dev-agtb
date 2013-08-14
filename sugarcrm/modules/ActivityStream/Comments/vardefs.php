<?php

$dictionary['Comment'] = array(
    'table' => 'comments',
    'fields' => array(
        // Set unnecessary fields from Basic to non-required/non-db.
        'name' => array (
            'name' => 'name',
            'type' => 'varchar',
            'required' => false,
            'source' => 'non-db',
        ),

        'description' => array (
            'name' => 'description',
            'type' => 'varchar',
            'required' => false,
            'source' => 'non-db',
        ),

        // Add relationship fields.
        'activities' => array(
            'name' => 'activities',
            'type' => 'link',
            'relationship' => 'comments',
            'link_type' => 'one',
            'module' => 'Activities',
            'bean_name' => 'Activity',
            'source' => 'non-db',
        ),

        // Add table columns.
        'parent_id' => array(
            'name'     => 'parent_id',
            'type'     => 'id',
            'len'      => 36,
            'required' => true,
        ),

        'data' => array(
            'name' => 'data',
            'type' => 'json',
            'dbType' => 'longtext',
            'required' => true,
        ),
    ),

    'indices' => array(
        array(
            'name' => 'comment_activities',
            'type' => 'index',
            'fields' => array('parent_id'),
        ),
    ),
);

VardefManager::createVardef('ActivityStream/Comments', 'Comment', array('basic'));
