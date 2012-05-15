<?php
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
                            'module' => 'Leads',
                        ),
                    ),
                ),
            ),
        ),
    ),
);