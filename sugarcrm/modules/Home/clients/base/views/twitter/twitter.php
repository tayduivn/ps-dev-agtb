<?php

$viewdefs['Home']['base']['view']['twitter'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_TWITTER_NAME',
            'description' => 'LBL_TWITTER_DESCRIPTION',
            'config' => array(
                'limit' => '20',
            ),
            'preview' => array(
                'title' => 'LBL_TWITTER_MY_ACCOUNT',
                'twitter' => 'sugarcrm',
                'limit' => '3',
            ),
        ),
    ),
    'config' => array(
        'fields' => array(
            array(
                'type' => 'base',
                'name' => 'twitter',
                'label' => "LBL_TWITTER_ID",
            ),
            array(
                'name' => 'limit',
                'label' => 'LBL_TWITTER_DISPLAY_ROWS',
                'type' => 'enum',
                'options' => array(
                    5 => 5,
                    10 => 10,
                    15 => 15,
                    20 => 20,
                    50 => 50,
                ),
            ),
        ),
    ),
);
