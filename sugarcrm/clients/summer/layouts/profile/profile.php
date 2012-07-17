<?php
$viewdefs['summmer']['layout']['profile'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnav',
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'fluid',
                'components' =>
                array(
                    0 => array(
                        'layout' =>
                        array(
                            'type' => 'simple',
                            'span' => 7,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'profile',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
