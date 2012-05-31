<?php

// TODO: Grab module list programatically to show
$shownModules = array();

$viewdefs['Cases']['portal']['layout']['dashboard'] = array(
    'type' => 'columns',
    'components' => array(
        0 => array(
            'layout' => array(
                'type' => 'column',
                'components' => array(
                    array(
                        'view' => 'list',
                    ),
                    array(
                        'view' => 'list',
                        'context' => array(
                            'module' => 'Bugs',
                        ),
                    ),
                ),
            ),
            'layout' => array(
                'type' => 'fluid',
                'components' => array(
                    array(
                        'view' => 'list',
                    ),
                ),
            ),
        ),
    ),
);