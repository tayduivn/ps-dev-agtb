<?php
//FILE SUGARCRM flav=ent ONLY

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
                'name' => 'LBL_AGENT_WORKBENCH_OVERVIEW',
                'components' => [
                    [
                        'rows' => [
                            [
                                [
                                    // TODO: add dashlet here
                                ],
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
            // TAB 2
            [
                'name' => 'LBL_CASES',
                'badges' => [
                    [
                        'type' => 'record-count',
                        'module' => 'Cases',
                        // TODO: use new filter operators in CS-86
                        'filter' => [
                            [
                                'follow_up_datetime' => [
                                    '$lt' => '$nowTime',
                                ],
                            ],
                            [
                                'status' => [
                                    '$not_in' => ['Closed', 'Rejected', 'Duplicate'],
                                ],
                            ],
                        ],
                        'cssClass' => 'case-expired',
                    ],
                    [
                        'type' => 'record-count',
                        'module' => 'Cases',
                        'filter' => [
                            [
                                'follow_up_datetime' => [
                                    '$between' => ['$nowTime', '$tomorrowTime'],
                                ],
                            ],
                            [
                                'status' => [
                                    '$not_in' => ['Closed', 'Rejected', 'Duplicate'],
                                ],
                            ],
                        ],
                        'cssClass' => 'case-soon',
                    ],
                    [
                        'type' => 'record-count',
                        'module' => 'Cases',
                        'filter' => [
                            [
                                'follow_up_datetime' => [
                                    '$gt' => '$tomorrowTime',
                                ],
                            ],
                            [
                                'status' => [
                                    '$not_in' => ['Closed', 'Rejected', 'Duplicate'],
                                ],
                            ],
                        ],
                        'cssClass' => 'case-future',
                    ],
                ],
                'components' => [
                    [
                        'context' => [
                            'module' => 'Cases',
                        ],
                        'view' => 'multi-line-list',
                    ],
                    [
                        'layout' => [
                            'name' => 'side-drawer',
                            'type' => 'side-drawer',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'name' => 'LBL_AGENT_WORKBENCH',
    'id' => 'c108bb4a-775a-11e9-b570-f218983a1c3e',
];
