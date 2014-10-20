<?php

$viewdefs['pmse_Inbox']['base']['layout']['logView'] = array(
    'components' => array(
//        array(
//            'layout' => array(
//                'components' => array(
                    array(
                        'layout' => array(
                            'components' => array(
                                array(
                                    'view' => 'logView-headerpane',
                                    'primary' => true,
                                ),
                                array(
                                    'layout' => array(
                                        'components' => array(
                                            array(
                                                'view' => 'logView-pane',
                                            ),
                                        ),
                                        'type' => 'simple',
                                        'name' => 'main-pane',
                                        'span' => 12,
                                    ),
                                ),
                            ),

                        ),
                    ),
                    array(
                        'layout' => array(
                            'components' => array(
                                array(
                                    'layout' => 'sidebar',
                                ),
                            ),
//                            'type' => 'simple',
//                            'name' => 'side-pane',
//                            'span' => 4,
                        ),
                    ),
//                ),
                'type' => 'default',
                'name' => 'sidebar',
                'span' => 12,
//            ),
//        ),
    ),
//    'type' => 'simple',
//    'name' => 'base',
//    'span' => 12,
);