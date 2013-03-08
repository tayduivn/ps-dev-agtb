<?php
$viewdefs['base']['view']['news'] = array(
    'dashlets' => array(
        array(
            'name' => 'News',
            'description' => 'Google News feed for Related Account',
            'config' => array(
                'limit' => '3',
            ),
            'preview' => array(
                'limit' => '3',
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                    'Contacts',
                    'Leads',
                ),
                'view' => 'record'
            )
        ),
    ),
);
