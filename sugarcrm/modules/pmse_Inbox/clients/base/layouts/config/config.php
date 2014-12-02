<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['layout']['config'] = array(
    'components' => array(
        array(
            'layout' => array(
                'components' => array(
                    array(
                        'layout' => array(
                            'components' => array(
                                array(
                                    'view' => 'config-headerpane',
                                ),
                                array(
                                    'view' => 'config'
                                ),
                            ),
                            'type' => 'simple',
                            'name' => 'main-pane',
                            'span' => 8,
                        ),
                    ),
                    array(
                        'layout' => array(
                            'components' => array(
                                array(
                                    'layout' => 'list-sidebar',
                                ),
                            ),
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
                        'layout' => array(
                            'components' =>
                            array(),
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
    'type' => 'settings',
    'name' => 'base',
    'span' => 12,
);