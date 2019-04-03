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
// TODO: CS-80
$viewdefs['Cases']['base']['view']['multi-line-list'] = [
    'panels' => [
        [
            'label' => 'LBL_PANEL_1',
            'fields' => [
                [
                    'name' => 'case_number',
                    'label' => 'LBL_LIST_NUMBER',
                    'subfields' => [
                        [
                            'name' => 'case_number',
                            'label' => 'LBL_LIST_NUMBER',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                        [
                            'name' => 'case_number',
                            'label' => 'LBL_LIST_NUMBER',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'subfields' => [
                        [
                            'name' => 'priority',
                            'label' => 'LBL_LIST_PRIORITY',
                            'default' => true,
                            'enabled' => true,
                        ],
                        [
                            'name' => 'status',
                            'label' => 'LBL_STATUS',
                            'default' => true,
                            'enabled' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'follow_up_datetime',
                    'label' => 'LBL_FOLLOW_UP_DATETIME',
                    'subfields' => [
                        [
                            'name' => 'follow_up_datetime',
                            'label' => 'LBL_FOLLOW_UP_DATETIME',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                        [
                            'name' => 'follow_up_datetime',
                            'label' => 'LBL_FOLLOW_UP_DATETIME',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'name',
                    'label' => 'LBL_LIST_SUBJECT',
                    'subfields' => [
                        [
                            'name' => 'name',
                            'label' => 'LBL_LIST_SUBJECT',
                            'link' => true,
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'account_name',
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'subfields' => [
                        [
                            'name' => 'account_name',
                            'label' => 'LBL_LIST_ACCOUNT_NAME',
                            'module' => 'Accounts',
                            'id' => 'ACCOUNT_ID',
                            'ACLTag' => 'ACCOUNT',
                            'related_fields' => ['account_id'],
                            'default' => true,
                            'enabled' => true,
                        ],
                        [
                            'name' => 'service_level',
                            'label' => 'LBL_SERVICE_LEVEL',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'subfields' => [
                        [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                            'id' => 'ASSIGNED_USER_ID',
                            'default' => true,
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
