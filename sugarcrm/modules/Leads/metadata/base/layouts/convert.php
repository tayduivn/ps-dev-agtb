<?php
$viewdefs['Leads']['base']['layout']['convert'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
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
                                    'view' => 'convert',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);