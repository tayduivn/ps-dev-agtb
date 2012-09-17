<?php
$viewdefs['base']['layout']['edit'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnavedit',
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
                            'span' => 11,
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
