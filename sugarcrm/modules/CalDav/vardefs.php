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

$dictionary['CalDavEventCollection'] = array(
    'table' => 'caldav_events',
    'comment' => 'This table used for store calendar objects',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' => array(
        'calendar_data' => array(
            'name' => 'calendar_data',
            'vname' => 'LBL_EVENT_DATA',
            'type' => 'longblob',
            'isnull' => 'true',
            'comment' => 'Event data in VOBJECT format',
        ),
        'uri' => array(
            'name' => 'uri',
            'vname' => 'LBL_EVENT_URI',
            'type' => 'varchar',
            'len' => '200',
            'isnull' => 'true',
            'comment' => 'Event URI',
        ),
        'calendar_id' => array(
            'required' => true,
            'name' => 'calendar_id',
            'vname' => 'LBL_EVENT_CALENDAR_ID',
            'type' => 'id',
            'comment' => 'Calendar ID',
        ),
        'etag' => array(
            'name' => 'etag',
            'vname' => 'LBL_EVENT_ETAG',
            'type' => 'varchar',
            'len' => '32',
            'isnull' => 'true',
            'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
        ),
        'data_size' => array(
            'required' => true,
            'name' => 'data_size',
            'vname' => 'LBL_EVENT_SIZE',
            'type' => 'int',
            'len' => '11',
            'comment' => 'Event size in bytes',
        ),
        'component_type' => array(
            'name' => 'component_type',
            'vname' => 'LBL_EVENT_COMPONENTTYPE',
            'type' => 'varchar',
            'len' => '8',
            'isnull' => 'true',
            'comment' => 'Event component type',
        ),
        'first_occurence' => array(
            'name' => 'first_occurence',
            'vname' => 'LBL_EVENT_FIRSTOCCURENCE',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'true',
            'comment' => 'Recurring event first occurrence',
        ),
        'last_occurence' => array(
            'name' => 'last_occurence',
            'vname' => 'LBL_EVENT_LASTOCCURENCE',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'true',
            'comment' => 'Recurring event last occurrence',
        ),
        'event_uid' => array(
            'name' => 'event_uid',
            'vname' => 'LBL_EVENT_UID',
            'type' => 'varchar',
            'len' => '200',
            'isnull' => 'true',
            'comment' => 'The event\'s UID',
        ),
        'parent_type' => array(
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'options' => 'parent_type_display',
            'len' => 100,
            'comment' => 'Module CalDav is associated with',
            'studio' => array(
                'searchview' => false,
                'wirelesslistview' => false,
            ),
        ),
        'parent_id' => array(
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'reportable' => false,
            'comment' => 'ID of item indicated by parent_type',
            'studio' => array(
                'searchview' => false,
            ),
        ),
        'participants_links' => array(
            'name' => 'participants_links',
            'vname' => 'LBL_PARTICIPANTS_LINKS',
            'type' => 'json',
            'dbType' => 'longtext',
            'reportable' => false,
            'comment' => 'Email to bean links',
            'studio' => array(
                'searchview' => false,
            ),
        ),
        'children_order_ids' => array(
            'name' => 'children_order_ids',
            'vname' => 'LBL_CHILDREN_ORDER_IDS',
            'type' => 'json',
            'dbType' => 'longtext',
            'reportable' => false,
            'comment' => 'Ids of sugar bean children in a given order',
            'studio' => array(
                'searchview' => false,
            ),
        ),
        'events_calendar' => array(
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
            'rhs_key' => 'calendar_id',
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
            'fields' => array('calendar_id', 'uri'),
        ),
        array(
            'name' => 'idx_timerange',
            'type' => 'index',
            'fields' => array('first_occurence', 'last_occurence'),
        ),
        array(
            'name' => 'idx_parent',
            'type' => 'index',
            'fields' => array('parent_type', 'parent_id'),
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
    'fields' => array(
        'uri' => array(
            'name' => 'uri',
            'vname' => 'LBL_CALENDAR_URI',
            'type' => 'varchar',
            'len' => '200',
            'isnull' => 'true',
            'comment' => 'Calendar URI',
        ),
        'synctoken' => array(
            'required' => true,
            'name' => 'synctoken',
            'vname' => 'LBL_CALENDAR_SYNCTOKEN',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'false',
            'comment' => 'Synchronization token for CalDav server purposes',
        ),
        'calendarorder' => array(
            'required' => true,
            'name' => 'calendarorder',
            'vname' => 'LBL_CALENDAR_ORDER',
            'type' => 'int',
            'len' => '5',
            'default' => '0',
            'isnull' => 'false',
            'comment' => 'Calendar order for iCal',
        ),
        'calendarcolor' => array(
            'name' => 'calendarcolor',
            'vname' => 'LBL_CALENDAR_COLOR',
            'type' => 'varchar',
            'len' => '10',
            'isnull' => 'true',
            'comment' => 'Calendar color for iCal',
        ),
        'timezone' => array(
            'name' => 'timezone',
            'vname' => 'LBL_CALENDAR_TIMEZONE',
            'type' => 'text',
            'isnull' => 'true',
            'comment' => 'Specifies a time zone on a calendar collection',
        ),
        'components' => array(
            'name' => 'components',
            'vname' => 'LBL_CALENDAR_COMPONENTS',
            'type' => 'varchar',
            'len' => '20',
            'isnull' => 'true',
            'comment' => 'Supported calendar components',
        ),
        'transparent' => array(
            'required' => true,
            'name' => 'transparent',
            'vname' => 'LBL_TRANSPARENT',
            'type' => 'tinyint',
            'isnull' => 'false',
            'comment' => 'Determines whether the calendar object resources in a calendar collection will affect the owner\'s busy time information.',
        ),
        'calendar_events' => array(
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
            'rhs_key' => 'calendar_id',
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
    'fields' => array(
        'uri' => array(
            'required' => false,
            'name' => 'uri',
            'vname' => 'LBL_CHANGE_EVENT_URI',
            'type' => 'varchar',
            'len' => '200',
            'isnull' => 'false',
            'comment' => 'Event URI',
        ),
        'synctoken' => array(
            'required' => true,
            'name' => 'synctoken',
            'vname' => 'LBL_CHANGE_CALENDAR_SYNCTOKEN',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'false',
            'comment' => 'Calendar events link',
        ),
        'calendarid' => array(
            'required' => true,
            'name' => 'calendarid',
            'vname' => 'LBL_CHANGE_CALENDAR_ID',
            'type' => 'id',
            'comment' => 'Calendar ID',
            'isnull' => 'false',
        ),
        'operation' => array(
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
    'fields' => array(
        'calendar_data' => array(
            'name' => 'calendar_data',
            'vname' => 'LBL_EVENT_DATA',
            'type' => 'longblob',
            'isnull' => 'true',
            'comment' => 'Event data in VOBJECT format',
        ),
        'uri' => array(
            'name' => 'uri',
            'vname' => 'LBL_EVENT_URI',
            'type' => 'varchar',
            'len' => '200',
            'isnull' => 'true',
            'comment' => 'Event URI',
        ),
        'etag' => array(
            'name' => 'etag',
            'vname' => 'LBL_SCHEDULING_ETAG',
            'type' => 'varchar',
            'len' => '32',
            'isnull' => 'true',
            'comment' => 'HTTP ETag. If the resource content at that URI ever changes, a new and different ETag is assigned',
        ),
        'data_size' => array(
            'required' => true,
            'name' => 'data_size',
            'vname' => 'LBL_SCHEDULING_SIZE',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'false',
            'comment' => 'Object size in bytes',
        ),
    ),
    'uses' => array(
        'default',
        'assignable',
    ),
    'indices' => array(
        array(
            'name' => 'uri_assigned',
            'type' => 'index',
            'fields' => array('uri', 'assigned_user_id'),
        ),
    ),
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

$dictionary['CalDavSynchronization'] = array(
    'table' => 'caldav_synchronization',
    'comment' => 'Synchronization counters for caldav event and sugar bean',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' => array(
        'event_id' => array(
            'name' => 'event_id',
            'vname' => 'LBL_EVENT_ID',
            'type' => 'id',
            'isnull' => 'false',
            'comment' => 'Calendar event id',
        ),
        'save_counter' => array(
            'name' => 'save_counter',
            'vname' => 'LBL_SYNC_SAVE_COUNTER',
            'type' => 'int',
            'len' => '11',
            'default' => '0',
            'comment' => 'Save counter',
        ),
        'job_counter' => array(
            'name' => 'job_counter',
            'vname' => 'LBL_SYNC_JOB_COUNTER',
            'type' => 'int',
            'len' => '11',
            'default' => '0',
            'comment' => 'Ended jobs counter',
        ),
        'conflict_counter' => array(
            'name' => 'conflict_counter',
            'vname' => 'LBL_CONFLICT',
            'type' => 'int',
            'len' => '11',
            'default' => '0',
            'comment' => 'Number of save_counter which solves conflict',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_event_id',
            'type' => 'unique',
            'fields' => array('event_id'),
        ),
    ),
    'ignore_templates' => array(
        'following',
        'favorite',
        'taggable',
    ),
);

$dictionary['CalDavQueue'] = array(
    'table' => 'caldav_queue',
    'comment' => 'Queues counters for caldav event and sugar bean',
    'full_text_search' => false,
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'fields' => array(
        'event_id' => array(
            'name' => 'event_id',
            'vname' => 'LBL_EVENT_ID',
            'type' => 'id',
            'isnull' => 'false',
            'comment' => 'Calendar event id',
        ),
        'action' => array(
            'name' => 'action',
            'vname' => 'LBL_ACTION',
            'type' => 'enum',
            'options' => 'caldav_queue_action',
            'len' => 100,
            'required' => false,
            'reportable' => false,
            'importable' => 'required',
        ),
        'save_counter' => array(
            'name' => 'save_counter',
            'vname' => 'LBL_SYNC_SAVE_COUNTER',
            'type' => 'int',
            'len' => '11',
            'default' => '0',
            'comment' => 'Save counter',
        ),
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'caldav_queue_status',
            'len' => 100,
            'required' => false,
            'reportable' => false,
            'importable' => 'required',
        ),
        'data' => array(
            'name' => 'data',
            'vname' => 'LBL_DATA',
            'type' => 'longtext',
            'required' => false,
            'reportable' => true,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'event_status_counter',
            'type' => 'index',
            'fields' => array('event_id', 'status', 'save_counter'),
        ),
    ),
);

VardefManager::createVardef(
    'CalDavEvents',
    'CalDavEventCollection'
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

VardefManager::createVardef(
    'CalDavSynchronizations',
    'CalDavSynchronization'
);

VardefManager::createVardef(
    'CalDavQueues',
    'CalDavQueue'
);
