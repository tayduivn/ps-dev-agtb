<?php

$dictionary['Subscription'] = array(
    'table' => 'subscriptions',
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

        // Add table columns.
        'parent_type' => array(
            'name'     => 'parent_type',
            'type'     => 'varchar',
            'len'      => 100,
            'required' => true,
        ),

        'parent_id' => array(
            'name'     => 'parent_id',
            'type'     => 'id',
            'len'      => 36,
            'required' => true,
        ),
    ),
);

VardefManager::createVardef('ActivityStream/Subscriptions', 'Subscription', array('basic'));
