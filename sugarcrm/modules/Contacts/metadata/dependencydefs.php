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
//BEGIN SUGARCRM flav=ent ONLY
$dependencies['Contacts']['uncheck_portal_active'] = [
    'hooks' => ['edit'],
    'triggerFields' => ['account_name'],
    'trigger' => 'equal(strlen($account_name),0)',
    'onload' => true,
    'actions' => [
        [
            'name' => 'SetValue',
            'params' => [
                'target' => 'portal_active',
                'value' => 'false',
            ],
        ],
    ],
];
$dependencies['Contacts']['readonly_portal_active'] = [
    'hooks' => ['edit'],
    'triggerFields' => ['account_name'],
    'trigger' => 'true',
    'onload' => true,
    'actions' => [
        [
            'name' => 'ReadOnly',
            'params' => [
                'target' => 'portal_active',
                'value' => 'equal(strlen($account_name),0)',
            ],
        ],
    ],
];
//END SUGARCRM flav=ent ONLY
