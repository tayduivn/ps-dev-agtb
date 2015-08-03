<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$dictionary['CalDavCalendar'] = array(
    'table' => 'caldav_calendars',
    'comment' => 'This table used for store calendars',
    'full_text_search' => false,
    'fields' =>
        array(
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Calendar URI',
                ),
            'synctoken' =>
                array(
                    'required' => true,
                    'name' => 'synctoken',
                    'vname' => 'LBL_SYNCTOKEN',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'false',
                    'comment' => 'Synchronization token for CalDav server purposes',
                ),
            'calendarorder' =>
                array(
                    'required' => true,
                    'name' => 'calendarorder',
                    'vname' => 'LBL_ORDER',
                    'type' => 'int',
                    'len' => '5',
                    'default' => '0',
                    'isnull' => 'false',
                    'comment' => 'Calendar order for iCal',
                ),
            'calendarcolor' =>
                array(
                    'name' => 'calendarcolor',
                    'vname' => 'LBL_COLOR',
                    'type' => 'varchar',
                    'len' => '10',
                    'isnull' => 'true',
                    'comment' => 'Calendar color for iCal',
                ),
            'timezone' =>
                array(
                    'name' => 'timezone',
                    'vname' => 'LBL_TIMEZONE',
                    'type' => 'text',
                    'isnull' => 'true',
                    'comment' => 'Specifies a time zone on a calendar collection',
                ),
            'components' =>
                array(
                    'name' => 'components',
                    'vname' => 'LBL_COMPONENTTYPE',
                    'type' => 'varchar',
                    'len' => '20',
                    'isnull' => 'true',
                    'comment' => 'Supported calendar components set',
                ),
            'transparent' =>
                array(
                    'required' => true,
                    'name' => 'transparent',
                    'vname' => 'LBL_TRANSPARENT',
                    'type' => 'tinyint',
                    'isnull' => 'false',
                    'comment' => 'Determines whether the calendar object resources in a calendar collection will affect the owner\'s busy time information.',
                ),
        ),
    'indices' => array(
        array(
            'name' => 'idx_assigned_user_id',
            'type' => 'index',
            'fields' => array('assigned_user_id'),
        )
    ),
    'uses' => array(
        'default',
        'assignable',
    ),
);

VardefManager::createVardef(
    'CalDavCalendars',
    'CalDavCalendar'
);
