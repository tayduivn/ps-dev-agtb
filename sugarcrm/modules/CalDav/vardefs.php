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

$dictionary['CalDavEvent'] = array(
    'table' => 'caldav_events',
    'comment' => 'This table used for store calendar objects',
    'full_text_search' => false,
    'fields' =>
        array(
            'calendardata' =>
                array(
                    'name' => 'calendardata',
                    'vname' => 'LBL_DATA',
                    'type' => 'longblob',
                    'isnull' => 'true',
                    'comment' => 'Object in VOBJECT format',
                ),
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Object URI',
                ),
            'calendarid' =>
                array(
                    'required' => true,
                    'name' => 'calendarid',
                    'vname' => 'LBL_CALENDAR_ID',
                    'type' => 'id',
                    'comment' => 'Calendar ID',
                ),
            'lastmodified' =>
                array(
                    'name' => 'lastmodified',
                    'vname' => 'LBL_LASTMODIFIED',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Modified time',
                ),
            'etag' =>
                array(
                    'name' => 'etag',
                    'vname' => 'LBL_ETAG',
                    'type' => 'varchar',
                    'len' => '32',
                    'isnull' => 'true',
                    'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
                ),
            'size' =>
                array(
                    'required' => true,
                    'name' => 'size',
                    'vname' => 'LBL_SIZE',
                    'type' => 'int',
                    'len' => '11',
                    'comment' => 'Object size in bytes',
                ),
            'componenttype' =>
                array(
                    'name' => 'componenttype',
                    'vname' => 'LBL_COMPONENTTYPE',
                    'type' => 'varchar',
                    'len' => '8',
                    'isnull' => 'true',
                    'comment' => 'Object component type',
                ),
            'firstoccurence' =>
                array(
                    'name' => 'firstoccurence',
                    'vname' => 'LBL_FIRSTOCCURENCE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Recurring event first occurrence',
                ),
            'lastoccurence' =>
                array(
                    'name' => 'lastoccurence',
                    'vname' => 'LBL_LASTOCCURENCE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Recurring event last occurrence',
                ),
            'uid' =>
                array(
                    'name' => 'uid',
                    'vname' => 'LBL_UID',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'The object\'s UID',
                ),
            'related_module' =>
                array(
                    'name' => 'related_module',
                    'type' => 'varchar',
                    'len' => '255',
                    'vname' => 'LBL_RELATED_MODULE',
                    'isnull' => 'true',
                    'comment' => 'Related Module',
                ),
            'related_module_id' =>
                array(
                    'name' => 'related_module_id',
                    'type' => 'id',
                    'vname' => 'LBL_RELATED_MODULE_ID',
                    'isnull' => 'true',
                    'comment' => 'Related Module ID',
                ),
            'sync_counter' =>
                array(
                    'name' => 'sync_counter',
                    'vname' => 'LBL_SYNC_COUNTER',
                    'type' => 'int',
                    'len' => '11',
                    'default' => '0',
                    'comment' => 'Object sync counter',
                ),
            'module_sync_counter' =>
                array(
                    'name' => 'module_sync_counter',
                    'vname' => 'LBL_MODULE_SYNC_COUNTER',
                    'type' => 'int',
                    'len' => '11',
                    'default' => '0',
                    'comment' => 'Related module object sync counter',
                ),
        ),
    'indices' => array(
        array(
            'name' => 'idx_calendarid',
            'type' => 'index',
            'fields' => array('calendarid', 'uri'),
        ),
    ),
);

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

$dictionary['CalDavChange'] = array(
    'table' => 'caldav_changes',
    'comment' => 'Calendar changes history',
    'full_text_search' => false,
    'fields' =>
        array(
            'uri' =>
                array(
                    'required' => true,
                    'name' => 'uri',
                    'vname' => 'LBL_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'false',
                    'comment' => 'Object URI',
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
            'calendarid' =>
                array(
                    'required' => true,
                    'name' => 'calendarid',
                    'vname' => 'LBL_CALENDAR_ID',
                    'type' => 'id',
                    'comment' => 'Calendar ID',
                    'isnull' => 'false',
                ),
            'operation' =>
                array(
                    'required' => true,
                    'name' => 'operation',
                    'vname' => 'LBL_OPERATION',
                    'type' => 'tinyint',
                    'isnull' => 'false',
                    'comment' => 'Operation with object',
                ),
        ),
    'indices' => array(
        array(
            'name' => 'calendarid_synctoken',
            'type' => 'index',
            'fields' => array('calendarid', 'synctoken'),
        ),
    ),
);

$dictionary['CalDavScheduling'] = array(
    'table' => 'caldav_scheduling',
    'comment' => 'Scheduling objects for caldav',
    'full_text_search' => false,
    'fields' =>
        array(
            'principaluri' =>
                array(
                    'name' => 'principaluri',
                    'vname' => 'LBL_PRINCIPALURI',
                    'type' => 'varchar',
                    'len' => '255',
                    'isnull' => 'true',
                    'comment' => 'Principal uri',
                ),
            'calendardata' =>
                array(
                    'name' => 'calendardata',
                    'vname' => 'LBL_DATA',
                    'type' => 'longblob',
                    'isnull' => 'true',
                    'comment' => 'Object in VOBJECT format',
                ),
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Object URI',
                ),
            'lastmodified' =>
                array(
                    'name' => 'lastmodified',
                    'vname' => 'LBL_LASTMODIFIED',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Modified time',
                ),
            'etag' =>
                array(
                    'name' => 'etag',
                    'vname' => 'LBL_ETAG',
                    'type' => 'varchar',
                    'len' => '32',
                    'isnull' => 'true',
                    'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
                ),
            'size' =>
                array(
                    'required' => true,
                    'name' => 'size',
                    'vname' => 'LBL_SIZE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'false',
                    'comment' => 'Object size in bytes',
                ),
        ),
    'indices' => array(),
);

VardefManager::createVardef(
    'CalDavCalendars',
    'CalDavCalendar'
);

VardefManager::createVardef(
    'CalDavEvents',
    'CalDavEvent'
);

VardefManager::createVardef(
    'CalDavChanges',
    'CalDavChange'
);

VardefManager::createVardef(
    'CalDavSchedulings',
    'CalDavScheduling'
);
