<?php

$viewdefs['base']['view']['interactionschart'] = array(
    'dashlets' => array(
        array(
            'name' => 'Interactions Chart',
            'description' => 'Displays Account interactions on chart.',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Accounts'
                ),
                'view' => 'record'
            )
        ),
    ),
);
