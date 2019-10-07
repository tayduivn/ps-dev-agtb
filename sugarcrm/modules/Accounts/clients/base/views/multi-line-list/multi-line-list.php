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
$viewdefs['Accounts']['base']['view']['multi-line-list'] = [
    'panels' => [
        [
            'label' => 'LBL_PANEL_1',
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'LBL_RENEWALS_CONSOLE_ACCOUNT_NAME_INDUSTRY',
                    'width' =>  'xlarge',
                    'subfields' => [
                        [
                            'name' => 'name',
                            'link' => true,
                            'label' => 'LBL_LIST_ACCOUNT_NAME',
                            'width' =>  'large',
                        ],
                        [
                            'name' => 'industry',
                            'label' => 'LBL_INDUSTRY',
                            'default' => true,
                            'enabled' => true,
                            'readonly' => true,
                            'type' => 'enum',
                        ],
                    ],
                ],
                [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'subfields' => [
                        [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                            'default' => true,
                            'enabled' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'annual_revenue',
                    'label' => 'LBL_ANNUAL_REVENUE',
                    'subfields' => [
                        [
                            'name' => 'annual_revenue',
                            'label' => 'LBL_ANNUAL_REVENUE',
                            'default' => true,
                            'enabled' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'industry',
                    'label' => 'LBL_INDUSTRY',
                    'subfields' => [
                        [
                            'name' => 'industry',
                            'label' => 'LBL_INDUSTRY',
                            'default' => true,
                            'enabled' => true,
                            'type' => 'enum',
                        ],
                    ],
                ],
                [
                    'name' => 'account_type',
                    'label' => 'LBL_ACCOUNT_TYPE',
                    'subfields' => [
                        [
                            'name' => 'account_type',
                            'label' => 'LBL_ACCOUNT_TYPE',
                            'default' => true,
                            'enabled' => true,
                            'type' => 'enum',
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
            'order_by' => 'date_modified',
        ],
    ],
    'filterDef' => [
        [
            '$owner' => '',
        ],
    ],
];
