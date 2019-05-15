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
                'components' => [[
                    'rows' => [
                        [
                            [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                ],
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_135',
                                    'type' => 'saved-reports-chart',
                                    'module' => 'Cases',
                                    'saved_report_id' => 'c290a6da-7606-11e9-a76d-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_135',
                                ],
                            ], [
                                'width' => 4,
                                'view' => [
                                    'limit' => '10',
                                    'date' => 'today',
                                    'visibility' => 'user',
                                    'label' => 'LBL_PLANNED_ACTIVITIES_DASHLET',
                                    'type' => 'planned-activities',
                                    'module' => null,
                                    'template' => 'tabbed-dashlet',
                                ],
                            ], [
                                'width' => 4,
                                'view' => [
                                    'limit' => 10,
                                    'visibility' => 'user',
                                    'label' => 'LBL_ACTIVE_TASKS_DASHLET',
                                    'type' => 'active-tasks',
                                    'module' => null,
                                    'template' => 'tabbed-dashlet',
                                ],
                            ],
                        ], [
                            [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                ],
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_137',
                                    'type' => 'saved-reports-chart',
                                    'module' => 'Cases',
                                    'saved_report_id' => 'c290abda-7606-11e9-9f3e-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_137',
                                    'chart_type' => 'pie chart',
                                ],
                            ], [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                ],
                                'view' => [
                                    'label' => 'LBL_RECENTLY_VIEWED_CASES_DASHLET',
                                    'type' => 'dashablelist',
                                    'module' => 'Cases',
                                    'last_state' => [
                                        'id' => 'dashable-list',
                                    ],
                                    'intelligent' => '0',
                                    'limit' => 5,
                                    'filter_id' => 'recently_viewed',
                                    'display_columns' => ['case_number', 'name', 'account_name', 'priority', 'status', 'assigned_user_name', 'date_modified', 'date_entered', 'team_name', 'time_to_resolution', 'business_center_name', 'service_level', 'follow_up_datetime'],
                                ],
                            ], [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                ],
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_138',
                                    'type' => 'saved-reports-chart',
                                    'module' => 'Cases',
                                    'saved_report_id' => 'c290ae50-7606-11e9-9cb2-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_138',
                                    'chart_type' => 'horizontal group by chart',
                                ],
                            ],
                        ], [
                            [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                    'link' => null,
                                ],
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_12',
                                    'type' => 'saved-reports-chart',
                                    'module' => 'Cases',
                                    'saved_report_id' => 'c2910814-7606-11e9-841e-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_12',
                                    'chart_type' => 'horizontal group by chart',
                                ],
                            ], [
                                'width' => 4,
                                'context' => [
                                    'module' => 'Cases',
                                ],
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_132',
                                    'type' => 'saved-reports-chart',
                                    'module' => 'Cases',
                                    'saved_report_id' => 'c2909f50-7606-11e9-b00e-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_132',
                                ],
                            ], [
                                'width' => 4,
                                'view' => [
                                    'label' => 'LBL_REPORT_DASHLET_TITLE_139',
                                    'type' => 'saved-reports-chart',
                                    'module' => null,
                                    'saved_report_id' => 'c290b0da-7606-11e9-81f9-f218983a1c3e',
                                    'saved_report' => 'LBL_REPORT_DASHLET_TITLE_139',
                                ],
                            ],
                        ],
                    ],
                    'width' => 12,
                ]],
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
