<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['Quotes']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'quote_num' => array(),
        'quote_type' => array(),
        'account_name' => array(
            'dbFields' => array(
                'accounts.name',
            ),
            'type' => 'text',
            'vname' => 'LBL_ACCOUNT_NAME',
        ),
        'total' => array(),
        'date_quote_expected_closed' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'assigned_user_name' => array(),
        'team_name' => array(),
        '$owner' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        '$owner' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
    ),
);
