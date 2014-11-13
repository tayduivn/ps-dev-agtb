<?php
$viewdefs['pmse_Inbox']['base']['layout']['casesList'] = array(
    'components' =>
        array(
            array(
                'layout' =>
                    array(
                        'components' =>
                            array(
                                array(
                                    'layout' =>
                                        array(
                                            'components' =>
                                                array(
                                                    array(
                                                        'view' => 'casesList-headerpane',
                                                    ),
//                                                    array(
//                                                        'view' => 'casesList-recipientscontainer',
//                                                    ),
                                                    array(
                                                        'view' => 'casesList-filter',
                                                    ),
                                                    array(
                                                        'layout' => 'casesList-list',
                                                    ),
                                                ),
                                            'type' => 'simple',
                                            'name' => 'main-pane',
                                            'span' => 8,
                                        ),
                                ),
                                array(
                                    'layout' =>
                                        array(
                                            'components' =>
                                                array(),
                                            'type' => 'simple',
                                            'name' => 'side-pane',
                                            'span' => 4,
                                        ),
                                ),
                                array(
                                    'layout' =>
                                        array(
                                            'components' =>
                                                array(),
                                            'type' => 'simple',
                                            'name' => 'dashboard-pane',
                                            'span' => 4,
                                        ),
                                ),
                                array(
                                    'layout' =>
                                        array(
                                            'components' =>
                                                array(
                                                    array(
                                                        'layout' => 'preview',
                                                    ),
                                                ),
                                            'type' => 'simple',
                                            'name' => 'preview-pane',
                                            'span' => 8,
                                        ),
                                ),
                            ),
                        'type' => 'default',
                        'name' => 'sidebar',
                        'span' => 12,
                    ),
            ),
        ),
    'type' => 'casesList',
    'name' => 'base',
    'span' => 12,
);