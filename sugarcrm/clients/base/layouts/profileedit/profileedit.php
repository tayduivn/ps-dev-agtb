<?php
$viewdefs['base']['layout']['profileedit'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnav',
            'meta' => 'edit'
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
                                    'view' => 'profile-edit',
                                ),
                                1 => array(
                                    'view' => 'passwordmodal',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
