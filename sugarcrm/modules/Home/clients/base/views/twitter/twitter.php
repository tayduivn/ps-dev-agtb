<?php

$viewdefs['Home']['base']['view']['twitter'] = array(
    'dashlets' => array(
        array(
            'name' => 'Twitter',
            'description' => 'Twitter for hash key',
            'config' => array(
                'limit' => '20',
            ),
            'preview' => array(
                'title' => 'My Account',
                'twitter' => 'sugarcrm',
                'limit' => '3',
            ),
        ),
        array(
            'name' => 'Twitter',
            'description' => 'Twitter for Related Account',
            'config' => array(
                'limit' => '20',
                'requiredModel' => true
            ),
            'preview' => array(
                'title' => 'My Account',
                'twitter' => 'sugarcrm',
                'limit' => '3',
            ),
            'filter' => array(
                'module' => array(
                    'Accounts', 'Contacts'
                ),
                'view' => 'record'
            )
        ),
    ),
    'config' => array(
        'fields' => array(
            array(
                'type' => 'base',
                'name' => 'twitter',
                'label' => "Twitter ID",
            ),
            array(
                'name' => 'limit',
                'label' => 'Display Rows',
                'type' => 'enum',
                'options' => array(
                    5 => 5,
                    10 => 10,
                    15 => 15,
                    20 => 20,
                    50 => 50,
                )
            ),
        )
    ),
);
