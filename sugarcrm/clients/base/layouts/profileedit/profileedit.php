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
                                    'view' => 'profile-edit',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
