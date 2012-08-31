<?php
$viewdefs['base']['layout']['detail'] = array(
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
                        'layout' =>
                        array(
                            'type' => 'simple',
                            'span' => 5,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'subdetail',
                                    'context' => array(
                                        'link' => 'notes',
                                    ),
                                ),
                                1 => array(
                                    'view' => 'subdetail',
                                    'context' => array(
                                        'link' => 'contacts',
                                    ),
                                ),
                                2 => array(
                                    'view' => 'subdetail',
                                    'context' => array(
                                        'link' => 'accounts',
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
