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
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' =>
        array(
            'calendardata' =>
                array(
                    'name' => 'calendardata',
                    'vname' => 'LBL_EVENT_DATA',
                    'type' => 'longblob',
                    'isnull' => 'true',
                    'comment' => 'Event data in VOBJECT format',
                ),
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_EVENT_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Event URI',
                ),
            'calendarid' =>
                array(
                    'required' => true,
                    'name' => 'calendarid',
                    'vname' => 'LBL_EVENT_CALENDAR_ID',
                    'type' => 'id',
                    'comment' => 'Calendar ID',
                ),
            'etag' =>
                array(
                    'name' => 'etag',
                    'vname' => 'LBL_EVENT_ETAG',
                    'type' => 'varchar',
                    'len' => '32',
                    'isnull' => 'true',
                    'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
                ),
            'size' =>
                array(
                    'required' => true,
                    'name' => 'size',
                    'vname' => 'LBL_EVENT_SIZE',
                    'type' => 'int',
                    'len' => '11',
                    'comment' => 'Event size in bytes',
                ),
            'componenttype' =>
                array(
                    'name' => 'componenttype',
                    'vname' => 'LBL_EVENT_COMPONENTTYPE',
                    'type' => 'varchar',
                    'len' => '8',
                    'isnull' => 'true',
                    'comment' => 'Event component type',
                ),
            'firstoccurence' =>
                array(
                    'name' => 'firstoccurence',
                    'vname' => 'LBL_EVENT_FIRSTOCCURENCE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Recurring event first occurrence',
                ),
            'lastoccurence' =>
                array(
                    'name' => 'lastoccurence',
                    'vname' => 'LBL_EVENT_LASTOCCURENCE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Recurring event last occurrence',
                ),
            'uid' =>
                array(
                    'name' => 'uid',
                    'vname' => 'LBL_EVENT_UID',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'The event\'s UID',
                ),
            'parent_type' =>
                array(
                    'name' => 'parent_type',
                    'vname' => 'LBL_PARENT_TYPE',
                    'type' => 'parent_type',
                    'dbType' => 'varchar',
                    'options' => 'parent_type_display',
                    'len' => 100,
                    'comment' => 'Module CalDav is associated with',
                    'studio' => array('searchview' => false, 'wirelesslistview' => false),
                ),
            'parent_id' =>
                array(
                    'name' => 'parent_id',
                    'vname' => 'LBL_PARENT_ID',
                    'type' => 'id',
                    'reportable' => false,
                    'comment' => 'ID of item indicated by parent_type',
                    'studio' => array('searchview' => false),
                ),
            'sync_counter' =>
                array(
                    'name' => 'sync_counter',
                    'vname' => 'LBL_EVENT_SYNC_COUNTER',
                    'type' => 'int',
                    'len' => '11',
                    'default' => '0',
                    'comment' => 'Event sync counter',
                ),
            'module_sync_counter' =>
                array(
                    'name' => 'module_sync_counter',
                    'vname' => 'LBL_EVENT_MODULE_SYNC_COUNTER',
                    'type' => 'int',
                    'len' => '11',
                    'default' => '0',
                    'comment' => 'Related module object sync counter',
                ),
            'events_calendar' =>
                array(
                    'name' => 'events_calendar',
                    'type' => 'link',
                    'relationship' => 'events_calendar',
                    'source' => 'non-db',
                    'link_type' => 'one',
                    'vname' => 'LBL_CALENDAR_EVENTS',
                ),
        ),
    'relationships' => array(
        'events_calendar' => array(
            'rhs_module' => 'CalDavEvents',
            'rhs_table' => 'caldav_events',
            'rhs_key' => 'calendarid',
            'lhs_module' => 'CalDavCalendars',
            'lhs_table' => 'caldav_calendars',
            'lhs_key' => 'id',
            'relationship_type' => 'one-to-many',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_calendarid',
            'type' => 'index',
            'fields' => array('calendarid', 'uri'),
        ),
        array(
            'name' => 'idx_timerange',
            'type' => 'index',
            'fields' => array('firstoccurence', 'lastoccurence'),
        ),
    ),
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

$dictionary['CalDavCalendar'] = array(
    'table' => 'caldav_calendars',
    'comment' => 'This table used for store calendars',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' =>
        array(
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_CALENDAR_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Calendar URI',
                ),
            'synctoken' =>
                array(
                    'required' => true,
                    'name' => 'synctoken',
                    'vname' => 'LBL_CALENDAR_SYNCTOKEN',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'false',
                    'comment' => 'Synchronization token for CalDav server purposes',
                ),
            'calendarorder' =>
                array(
                    'required' => true,
                    'name' => 'calendarorder',
                    'vname' => 'LBL_CALENDAR_ORDER',
                    'type' => 'int',
                    'len' => '5',
                    'default' => '0',
                    'isnull' => 'false',
                    'comment' => 'Calendar order for iCal',
                ),
            'calendarcolor' =>
                array(
                    'name' => 'calendarcolor',
                    'vname' => 'LBL_CALENDAR_COLOR',
                    'type' => 'varchar',
                    'len' => '10',
                    'isnull' => 'true',
                    'comment' => 'Calendar color for iCal',
                ),
            'timezone' =>
                array(
                    'name' => 'timezone',
                    'vname' => 'LBL_CALENDAR_TIMEZONE',
                    'type' => 'text',
                    'isnull' => 'true',
                    'comment' => 'Specifies a time zone on a calendar collection',
                ),
            'components' =>
                array(
                    'name' => 'components',
                    'vname' => 'LBL_CALENDAR_COMPONENTS',
                    'type' => 'varchar',
                    'len' => '20',
                    'isnull' => 'true',
                    'comment' => 'Supported calendar components',
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
            'calendar_events' =>
                array(
                    'name' => 'calendar_events',
                    'type' => 'link',
                    'relationship' => 'calendar_events',
                    'source' => 'non-db',
                    'link_type' => 'one',
                    'vname' => 'LBL_CALENDAR_EVENTS',
                ),
        ),
    'relationships' => array(
        'calendar_events' => array(
            'rhs_module' => 'CalDavEvents',
            'rhs_table' => 'caldav_events',
            'rhs_key' => 'calendarid',
            'lhs_module' => 'CalDavCalendars',
            'lhs_table' => 'caldav_calendars',
            'lhs_key' => 'id',
            'relationship_type' => 'one-to-many',
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
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

$dictionary['CalDavChange'] = array(
    'table' => 'caldav_changes',
    'comment' => 'Calendar changes history',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' =>
        array(
            'uri' =>
                array(
                    'required' => true,
                    'name' => 'uri',
                    'vname' => 'LBL_CHANGE_EVENT_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'false',
                    'comment' => 'Event URI',
                ),
            'synctoken' =>
                array(
                    'required' => true,
                    'name' => 'synctoken',
                    'vname' => 'LBL_CHANGE_CALENDAR_SYNCTOKEN',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'false',
                    'comment' => 'Calendar events link',
                ),
            'calendarid' =>
                array(
                    'required' => true,
                    'name' => 'calendarid',
                    'vname' => 'LBL_CHANGE_CALENDAR_ID',
                    'type' => 'id',
                    'comment' => 'Calendar ID',
                    'isnull' => 'false',
                ),
            'operation' =>
                array(
                    'required' => true,
                    'name' => 'operation',
                    'vname' => 'LBL_CHANGE_OPERATION',
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
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

$dictionary['CalDavScheduling'] = array(
    'table' => 'caldav_scheduling',
    'comment' => 'Scheduling objects for caldav',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' =>
        array(
            'principaluri' =>
                array(
                    'name' => 'principaluri',
                    'vname' => 'LBL_SCHEDULING_PRINCIPALURI',
                    'type' => 'varchar',
                    'len' => '255',
                    'isnull' => 'true',
                    'comment' => 'Principal uri',
                ),
            'calendardata' =>
                array(
                    'name' => 'calendardata',
                    'vname' => 'LBL_EVENT_DATA',
                    'type' => 'longblob',
                    'isnull' => 'true',
                    'comment' => 'Event data in VOBJECT format',
                ),
            'uri' =>
                array(
                    'name' => 'uri',
                    'vname' => 'LBL_EVENT_URI',
                    'type' => 'varchar',
                    'len' => '200',
                    'isnull' => 'true',
                    'comment' => 'Event URI',
                ),
            'lastmodified' =>
                array(
                    'name' => 'lastmodified',
                    'vname' => 'LBL_SCHEDULING_LASTMODIFIED',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'true',
                    'comment' => 'Modified time',
                ),
            'etag' =>
                array(
                    'name' => 'etag',
                    'vname' => 'LBL_SCHEDULING_ETAG',
                    'type' => 'varchar',
                    'len' => '32',
                    'isnull' => 'true',
                    'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
                ),
            'size' =>
                array(
                    'required' => true,
                    'name' => 'size',
                    'vname' => 'LBL_SCHEDULING_SIZE',
                    'type' => 'int',
                    'len' => '11',
                    'isnull' => 'false',
                    'comment' => 'Object size in bytes',
                ),
        ),
    'indices' => array(),
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

VardefManager::createVardef(
    'CalDavEvents',
    'CalDavEvent'
);

VardefManager::createVardef(
    'CalDavCalendars',
    'CalDavCalendar'
);

VardefManager::createVardef(
    'CalDavChanges',
    'CalDavChange'
);

VardefManager::createVardef(
    'CalDavSchedulings',
    'CalDavScheduling'
);
