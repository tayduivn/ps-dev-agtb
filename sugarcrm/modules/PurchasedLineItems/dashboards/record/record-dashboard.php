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
        'dashlets' => [
            [
                'view' => [
                    'type' => 'dashablerecord',
                    'module' => 'PurchasedLineItems',
                    'label' => 'LBL_PARENT_PURCHASE',
                    'tabs' => [
                        [
                            'module' => 'Purchases',
                            'link' => 'purchase',
                        ],
                    ],
                    'tab_list' => [
                        'purchase',
                    ],
                ],
                'context' => [
                    'module' => 'PurchasedLineItems',
                ],
                'width' => 12,
                'height' => 4,
                'x' => 0,
                'y' => 0,
            ],
        ],
    ],
    'name' => 'LBL_PURCHASEDLINEITEMS_RECORD_DASHBOARD',
];
