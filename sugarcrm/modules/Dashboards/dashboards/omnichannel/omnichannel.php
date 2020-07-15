<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
return [
    'metadata' => [
        'tabs' => [
            // TAB 1
            [
                'icon' => [
                    'image' => '<i class="fa fa-search"></i>',
                ],
                'name' => 'LBL_SEARCH',
                'components' => [
                    //TODO: CS-810
                ],
            ],
            // TAB 2
            [
                'icon' => [
                    'module' => 'Contacts',
                ],
                'name' => 'LBL_CONTACT',
                'components' => [
                    //TODO: CS-770
                    [
                        'rows' => [
                            // row 1
                            [
                                [
                                    'view' => [
                                        'type' => 'dashablerecord',
                                        'module' => 'Contacts',
                                        'tabs' => [
                                            [
                                                'active' => true,
                                                'label' => 'LBL_MODULE_NAME_SINGULAR',
                                                'link' => '',
                                                'module' => 'Contacts',
                                            ],
                                            [
                                                'active' => false,
                                                'link' => 'tasks',
                                                'module' => 'Tasks',
                                                'order_by' => [
                                                    'field' => 'date_entered',
                                                    'direction' => 'desc',
                                                ],
                                                'limit' => 5,
                                                'fields' => [
                                                    'name',
                                                    'assigned_user_name',
                                                    'date_entered',
                                                ],
                                            ],
                                            [
                                                'active' => false,
                                                'link' => 'documents',
                                                'module' => 'Documents',
                                                'order_by' => [
                                                    'field' => 'active_date',
                                                    'direction' => 'desc',
                                                ],
                                                'limit' => 5,
                                                'fields' => [
                                                    'document_name',
                                                    'active_date',
                                                ],
                                            ],
                                        ],
                                        'tab_list' => [
                                            'Contacts',
                                            'tasks',
                                            'documents',
                                        ],
                                    ],
                                    'context' => [
                                        'module' => 'Contacts',
                                    ],
                                    'width' => 6,
                                ],
                                [
                                    'width' => 6,
                                ],
                            ],
                            // row 2
                            [
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
            // TAB 3
            [
                'icon' => [
                    'module' => 'Cases',
                ],
                'name' => 'LBL_CASE',
                'components' => [
                    //TODO: CS-821
                    [
                        'rows' => [
                            // row 1
                            [
                                [
                                    'view' => [
                                        'type' => 'dashablerecord',
                                        'module' => 'Cases',
                                        'tabs' => [
                                            [
                                                'active' => true,
                                                'label' => 'LBL_MODULE_NAME_SINGULAR',
                                                'link' => '',
                                                'module' => 'Cases',
                                            ],
                                            [
                                                'active' => false,
                                                'link' => 'tasks',
                                                'module' => 'Tasks',
                                                'order_by' => [
                                                    'field' => 'date_entered',
                                                    'direction' => 'desc',
                                                ],
                                                'limit' => 5,
                                                'fields' => [
                                                    'name',
                                                    'assigned_user_name',
                                                    'date_entered',
                                                ],
                                            ],
                                            [
                                                'active' => false,
                                                'link' => 'documents',
                                                'module' => 'Documents',
                                                'order_by' => [
                                                    'field' => 'active_date',
                                                    'direction' => 'desc',
                                                ],
                                                'limit' => 5,
                                                'fields' => [
                                                    'document_name',
                                                    'active_date',
                                                ],
                                            ],
                                        ],
                                        'tab_list' => [
                                            'Cases',
                                            'tasks',
                                            'documents',
                                        ],
                                    ],
                                    'context' => [
                                        'module' => 'Cases',
                                    ],
                                    'width' => 6,
                                ],
                                [
                                    'width' => 6,
                                ],
                            ],
                            // row 2
                            [
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
        ],
    ],
    'name' => 'LBL_OMNICHANNEL_DASHBOARD',
    'id' => '32bc5cd0-b1a0-11ea-ad16-f45c898a3ce7',
];
