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

$dictionary['BusinessCenter'] = array(
    'table' => 'business_centers',
    'audited' => true,
    'activity_enabled' => false,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => false,
    'comment' => 'Business operations center details',
    'fields' => array(
        'timezone' => array(
            'name' => 'timezone',
            'vname' => 'LBL_TIMEZONE',
            'type' => 'enum',
            'options' => 'timezone_dom',
            'comment' => 'Time Zone in which this Business Center operates',
            'required' => true,
            'audited' => true,
        ),
        'address_street' => array(
            'name' => 'address_street',
            'vname' => 'LBL_ADDRESS_STREET',
            'type' => 'text',
            'dbType' => 'varchar',
            'len' => '150',
            'comment' => 'Address of this Business Center',
            'group' => 'address',
            'group_label' => 'LBL_ADDRESS',
            'merge_filter' => 'enabled',
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'boost' => 0.26,
            ),
            'audited' => true,
        ),
        'address_city' => array(
            'name' => 'address_city',
            'vname' => 'LBL_ADDRESS_CITY',
            'type' => 'varchar',
            'len' => '100',
            'comment' => 'City of this Business Center',
            'group' => 'address',
            'merge_filter' => 'enabled',
            'audited' => true,
        ),
        'address_state' => array(
            'name' => 'address_state',
            'vname' => 'LBL_ADDRESS_STATE',
            'type' => 'varchar',
            'len' => '100',
            'group' => 'address',
            'comment' => 'State of this Business Center',
            'merge_filter' => 'enabled',
            'audited' => true,
        ),
        'address_postalcode' => array(
            'name' => 'address_postalcode',
            'vname' => 'LBL_ADDRESS_POSTALCODE',
            'type' => 'varchar',
            'len' => '20',
            'group' => 'address',
            'comment' => 'Postal Code of this Business Center',
            'merge_filter' => 'enabled',
            'audited' => true,
        ),
        'address_country' => array(
            'name' => 'address_country',
            'vname' => 'LBL_ADDRESS_COUNTRY',
            'type' => 'varchar',
            'group' => 'address',
            'comment' => 'Country of this Business Center',
            'merge_filter' => 'enabled',
            'audited' => true,
        ),

        // BUSINESS HOURS FIELDS - TO BE ADDED
    ),
// BEGIN SUGARCRM flav=ent ONLY
    'relationships' => array(
        'business_center_accounts' => array(
            'lhs_module' => 'BusinessCenters',
            'lhs_table' => 'business_centers',
            'lhs_key' => 'id',
            'rhs_module' => 'Accounts',
            'rhs_table' => 'accounts',
            'rhs_key' => 'business_center_id',
            'relationship_type' => 'one-to-many',
        ),
        'business_center_cases' => array(
            'lhs_module' => 'BusinessCenters',
            'lhs_table' => 'business_centers',
            'lhs_key' => 'id',
            'rhs_module' => 'Cases',
            'rhs_table' => 'cases',
            'rhs_key' => 'business_center_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
// END SUGARCRM flav=ent ONLY
    'acls' => array(
        'SugarACLAdminOnly' => true,
    ),
    'uses' => ['basic', 'assignable', 'team_security'],
);

VardefManager::createVardef(
    'BusinessCenters',
    'BusinessCenter'
);
