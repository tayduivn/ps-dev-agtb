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
        'attachments' => array (
            'name' => 'attachments',
            'type' => 'link',
            'relationship' => 'comment_attachments',
            'link_type' => 'many',
            'module' => 'Notes',
            'bean_name' => 'Note',
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

    'relationships' => array(
        // This is called comment_attachments instead of comment_notes because
        // notes in this relationship do not contain attributes of regular notes
        // such as name and description. This relationship is solely for
        // attaching files to a comment on the activity stream.
        'comment_attachments' => array(
            'lhs_module' => 'Comments',
            'lhs_table' => 'comments',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Comments',
        ),
    ),
);

VardefManager::createVardef('ActivityStream/Comments', 'Comment', array('basic'));
