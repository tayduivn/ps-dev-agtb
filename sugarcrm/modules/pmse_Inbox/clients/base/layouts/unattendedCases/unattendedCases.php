<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['layout']['unattendedCases'] = array(
    'components' => array(
        array(
            'layout' => array(
                'components' => array(
                    array(
                        'layout' => array(
                            'components' => array(
                                array(
                                    'view' => 'unattendedCases-list-headerpane',
                                ),
                                array(
                                    'layout' => array(
                                        'type' => 'filterpanel',
                                        'span' => 12,
                                        'last_state' => array(
                                            'id' => 'list-filterpanel',
                                            'defaults' => array(
                                                'toggle-view' => 'list',
                                            ),
                                        ),
                                        'availableToggles' => array(
//                                            array(
//                                                'name' => 'list',
//                                                'icon' => 'fa-table',
//                                                'label' => 'LBL_LISTVIEW',
//                                            ),
//                                            array(
//                                                'name' => 'activitystream',
//                                                'icon' => 'fa-clock-o',
//                                                'label' => 'LBL_ACTIVITY_STREAM',
//                                            ),
                                        ),
                                        'components' => array(
                                            array(
                                                'view' => 'casesList-filter',
                                                'targetEl' => '.filter',
                                                'position' => 'prepend'
                                            ),
//                                            array(
//                                                'layout' => 'filter',
//                                                'targetEl' => '.filter',
//                                                'position' => 'prepend'
//                                            ),
                                            /*array(
                                                'view' => 'filter-rows',
                                                "targetEl" => '.filter-options'
                                            ),
                                            array(
                                                'view' => 'filter-actions',
                                                "targetEl" => '.filter-options'
                                            ),*/
                                            /*array(
                                                'layout' => 'activitystream',
                                                'context' => array(
                                                    'module' => 'Activities',
                                                ),
                                            ),*/
                                            array(
                                                'layout' => 'unattendedCases-list',
                                            ),
                                        ),
                                    ),
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
//                    array(
//                        'layout' => array(
//                            'components' => array(
//                                array(
//                                    'layout' => array(
//                                        'type' => 'dashboard',
//                                        'last_state' => array(
//                                            'id' => 'last-visit',
//                                        )
//                                    ),
//                                    'context' => array(
//                                        'forceNew' => true,
//                                        'module' => 'Home',
//                                    ),
//                                ),
//                            ),
//                            'type' => 'simple',
//                            'name' => 'dashboard-pane',
//                            'span' => 4,
//                        ),
//                    ),
                    array(
                        'layout' => array(
                            'components' => array(
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
    'type' => 'unattendedCases',
    'name' => 'base',
    'span' => 12,
);
