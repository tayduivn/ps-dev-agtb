<?php
$viewdefs['Cases']['portal']['layout']['detail'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnav'
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'columns',
                'components' => array(
                    0 => array(
                        'layout' => array(
                            'type' => 'leftside',
                            'components' => array(
                                0 => array(
                                    'view' => 'detail',
                                ),
                                1 => array(
                                    'view' => 'activity',
                                    'context' => array(
                                        'link' => 'notes',
                                    ),
                                ),
                                2 => array(
                                    'view' => 'editmodal',
                                    'context' => array(
                                        'link' => 'notes',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    1 => array(
                        'layout' => array(
                            'type' => 'rightside',
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'subdetail',
                                    'context' => array(
                                        'link' => 'notes',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);