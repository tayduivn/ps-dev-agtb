<?php

$viewdefs['base']['view']['countrychart'] = array(
    'dashlets' => array(
        array(
            'name' => 'Sales by Country',
            'description' => 'Displays the sales chart by country map.',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                ),
                'view' => 'records'
            )
        ),
        array(
            'name' => 'Sales by Country',
            'description' => 'Displays the sales chart by country map.',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                ),
                'view' => 'record'
            )
        ),
    ),
);
