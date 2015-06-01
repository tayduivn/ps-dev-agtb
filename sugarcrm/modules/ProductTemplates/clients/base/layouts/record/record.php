<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$viewdefs['ProductTemplates']['base']['layout']['record'] = array(
    'components' => array(
        array(
            'layout' => array(
                'type' => 'default',
                'name' => 'sidebar',
                'components' => array(
                    array(
                        'layout' => array(
                            'type' => 'base',
                            'name' => 'main-pane',
                            'targetEl' => '.main-pane',
                            'components' => array(
                                array(
                                    'view' => 'record',
                                    'primary' => true,
                                ),
                                array(
                                    'layout' => 'extra-info',
                                ),
                                array(
                                    'layout' => array(
                                        'type' => 'filterpanel',
                                        'last_state' => array(
                                            'id' => 'record-filterpanel',
                                            'defaults' => array(
                                                'toggle-view' => 'subpanels',
                                            ),
                                        ),
                                        'availableToggles' => array(
                                            array(
                                                'type' => 'subpanels',
                                                'icon' => 'fa-table',
                                                'label' => 'LBL_DATA_VIEW',
                                            ),
                                            array(
                                                'type' => 'list',
                                                'icon' => 'fa-table',
                                                'label' => 'LBL_LISTVIEW',
                                            ),
                                            array(
                                                'type' => 'activitystream',
                                                'icon' => 'fa-clock-o',
                                                'label' => 'LBL_ACTIVITY_STREAM',
                                            ),
                                        ),
                                        'components' => array(
                                            array(
                                                'layout' => 'filter',
                                                'targetEl' => '.filter',
                                                'position' => 'prepend'
                                            ),
                                            array(
                                                'view' => 'filter-rows',
                                                "targetEl" => '.filter-options'
                                            ),
                                            array(
                                                'view' => 'filter-actions',
                                                "targetEl" => '.filter-options'
                                            ),
                                            array(
                                                'layout' => 'activitystream',
                                                'context' =>
                                                array(
                                                    'module' => 'Activities',
                                                ),
                                            ),
                                            array(
                                                'layout' => 'subpanels',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'layout' => array(
                            'type' => 'base',
                            'name' => 'dashboard-pane',
                            'targetEl' => '.dashboard-pane',
                            'components' => array(
                                array(
                                    'layout' => array(
                                        'type' => 'dashboard',
                                        'last_state' => array(
                                            'id' => 'last-visit',
                                        )
                                    ),
                                    'context' => array(
                                        'forceNew' => true,
                                        'module' => 'Home',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'layout' => array(
                            'type' => 'base',
                            'targetEl' => '.preview-pane',
                            'components' => array(
                                array(
                                    'layout' => 'preview',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
