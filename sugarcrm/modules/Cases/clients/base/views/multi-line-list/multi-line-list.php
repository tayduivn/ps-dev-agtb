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
$viewdefs['Cases']['base']['view']['multi-line-list'] = [
    'panels' => [
        [
            'label' => 'LBL_PANEL_1',
            'fields' => [
                [
                    'name' => 'case_number',
                    'label' => 'LBL_AGENT_WORKBENCH_NUMBER',
                    'widthClass' => 'cell-xxsmall',
                    'subfields' => [
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
                    'label' => 'LBL_AGENT_WORKBENCH_PRIORITY_STATUS',
                    'widthClass' => 'cell-xsmall',
                    'subfields' => [
                        [
                            'name' => 'priority',
                            'label' => 'LBL_LIST_PRIORITY',
                            'default' => true,
                            'enabled' => true,
                            'type' => 'enum-colorcoded',
                            'color_code_classes' => [
                                'P1' => 'orange white-text',
                                'P2' => 'blue white-text',
                                'P3' => 'green white-text',
                            ],
                        ],
                        [
                            'name' => 'status',
                            'label' => 'LBL_STATUS',
                            'default' => true,
                            'enabled' => true,
                            'type' => 'enum-colorcoded',
                            'color_code_classes' => [
                                'New' => 'green white-text',
                                'Assigned' => 'purple white-text',
                                'Closed' => 'white black-text',
                                'Pending Input' => 'blue white-text',
                                'Rejected' => ' white-text',
                                'Duplicate' => 'white black-text',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'follow_up_datetime',
                    'label' => 'LBL_AGENT_WORKBENCH_FOLLOW_UP',
                    'widthClass' => 'cell-xsmall',
                    'subfields' => [
                        [
                            'name' => 'follow_up_datetime',
                            'label' => 'LBL_FOLLOW_UP_DATETIME',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                            'type' => 'follow-up-datetime-colorcoded',
                            'color_code_classes' => [
                                'overdue' => 'red white-text',
                                'in_a_day' => 'orange white-text',
                                'more_than_a_day' => '',
                            ],
                        ],
                        [
                            'name' => 'follow_up_datetime',
                            'label' => 'LBL_FOLLOW_UP_DATETIME',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                            'type' => 'datetimecombo',
                        ],
                    ],
                ],
                [
                    'name' => 'name',
                    'label' => 'LBL_LIST_SUBJECT',
                    'widthClass' => 'cell-xlarge',
                    'subfields' => [
                        [
                            'name' => 'name',
                            'label' => 'LBL_LIST_SUBJECT',
                            'link' => false,
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'business_center',
                    'label' => 'LBL_BUSINESS_CENTER',
                    'widthClass' => 'cell-xsmall',
                    'subfields' => [
                        [
                            'name' => 'business_center_name',
                            'label' => 'LBL_BUSINESS_CENTER',
                            'link' => false,
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT',
                    'widthClass' => 'cell-small',
                    'subfields' => [
                        [
                            'name' => 'account_name',
                            'label' => 'LBL_LIST_ACCOUNT_NAME',
                            'module' => 'Accounts',
                            'id' => 'ACCOUNT_ID',
                            'ACLTag' => 'ACCOUNT',
                            'related_fields' => ['account_id'],
                            'link' => false,
                            'default' => true,
                            'enabled' => true,
                        ],
                        [
                            'name' => 'service_level',
                            'label' => 'LBL_SERVICE_LEVEL',
                            'type' => 'enum',
                            'enum_module' => 'Accounts',
                            'link' => false,
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'widthClass' => 'cell-xsmall',
                    'subfields' => [
                        [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                            'id' => 'ASSIGNED_USER_ID',
                            'link' => false,
                            'default' => true,
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'collectionOptions' => [
        'limit' => 100,
        'params' => [
            'max_num' => 100,
            'order_by' => 'follow_up_datetime',
        ],
    ],
    'filterDef' => [
        [
            '$or' => [
                ['status' => 'New'],
                ['status' => 'Assigned'],
                ['status' => 'Pending Input'],
            ],
            '$owner' => '',
        ],
    ],
];
