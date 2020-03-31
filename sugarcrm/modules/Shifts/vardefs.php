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

$dictionary['Shift'] = [
    'table' => 'shifts',
    'audited' => true,
    'activity_enabled' => false,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => false,
    'fields' => [
        'timezone' => [
            'name' => 'timezone',
            'vname' => 'LBL_TIMEZONE',
            'type' => 'enum',
            'options' => 'timezone_dom',
            'comment' => 'Time Zone in which this Shift operates',
            'required' => true,
            'audited' => true,
        ],
        'shifts_users' => [
            'name' => 'shifts_users',
            'type' => 'link',
            'relationship' => 'shifts_users',
            'source' => 'non-db',
            'vname' => 'LBL_SHIFT_USERS_TITLE',
            'module' => 'Users',
            'bean_name' => 'User',
        ],
        'users' => [
            'name' => 'users',
            'type' => 'link',
            'relationship' => 'shifts_users',
            'source' => 'non-db',
            'module' => 'Users',
            'bean_name' => 'Users',
            'rel_fields' => [],
            'vname' => 'LBL_USERS',
            'populate_list' => []
        ],
    ],
    'relationships' => [],
    'acls' => [
        'SugarACLAdminOnly' => [
            'allowUserRead' => true,
        ],
    ],
    'uses' => ['basic', 'assignable', 'team_security', 'business_hours'],
];

VardefManager::createVardef('Shifts','Shift');
