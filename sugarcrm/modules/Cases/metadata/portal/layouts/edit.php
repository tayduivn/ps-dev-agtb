<?php
$viewdefs['Cases']['portal']['layout']['edit'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnav',
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'columns',
                'components' =>
                array(
                    0 => array(
                        'layout' =>
                        array(
                            'type' => 'leftside',
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'edit',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);