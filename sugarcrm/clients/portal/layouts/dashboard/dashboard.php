<?php

$viewdefs['portal']['layout']['dashboard'] = array(
    'type' => 'columns',
    'components' => array(
        0 => array(
            'layout' => "list"
        ),
        1 => array(
            'layout' => 'list',
            'context' => array(
                'module' => 'Bugs',
            )
        )
    ),
);