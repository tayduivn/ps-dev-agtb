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
                'components' => [
                    [
                        'context' => [
                            'module' => 'Cases',
                        ],
                        'view' => 'multi-line-list',
                    ],
                ],
            ],
        ],
    ],
    'name' => 'LBL_AGENT_WORKBENCH',
];
