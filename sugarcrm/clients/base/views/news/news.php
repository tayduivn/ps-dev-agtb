<?php
$viewdefs['base']['view']['news'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_NEWS_NAME',
            'description' => 'LBL_DASHLET_NEWS_DESCRIPTION',
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
