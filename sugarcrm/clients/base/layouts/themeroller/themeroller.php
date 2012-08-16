<?php
$viewdefs['base']['layout']['themeroller'] = array(
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
                            'span' => 7,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'themeroller',
                                ),
                            ),
                        ),
                    ),
                    1 => array(
                        'layout' =>
                        array(
                            'type' => 'simple',
                            'span' => 5,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'themerollerpreview',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
