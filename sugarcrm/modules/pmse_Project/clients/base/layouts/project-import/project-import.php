<?php



$module_name = 'pmse_Project';
$viewdefs[$module_name]['base']['layout']['project-import'] = array(
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
                                    'view' => 'project-import-headerpane',
                                ),
                                array(
                                    'view' => 'project-import',
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
                            array(
                                array(
                                    'layout' => 'sidebar',
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
    'type' => 'simple',
    'name' => 'base',
    'span' => 12,
);
