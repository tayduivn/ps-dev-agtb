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
        'components' => [
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
                                        'link' => 'contacts',
                                        'module' => 'Contacts',
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
                                    'contacts',
                                    'documents',
                                ],
                            ],
                            'context' => [
                                'module' => 'Cases',
                             ],
                            'width' => 6,
                        ],
                        [
                            'view' => [
                                'type' => 'commentlog-dashlet',
                                'label' => 'LBL_DASHLET_COMMENTLOG_NAME',
                            ],
                            'width' => 6,
                        ],
                    ],
                    // row 2
                    [
                        [
                            'view' => [
                                'type' => 'dashablerecord',
                                'module' => 'Cases',
                                'tabs' => [
                                    [
                                        'module' => 'Accounts',
                                        'link' => 'accounts',
                                    ],
                                ],
                                'tab_list' => [
                                    'accounts',
                                ],
                            ],
                            'context' => [
                                'module' => 'Cases',
                            ],
                            'width' => 6,
                        ],
                    ],
                ],
                'width' => 12,
            ],
        ],
    ],
    'name' => 'LBL_CASES_MULTI_LINE_DASHBOARD',
];
