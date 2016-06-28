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

$mod_strings = array(
    'LBL_EVENT_DATA' => 'Event data in VOBJECT format',
    'LBL_EVENT_URI' => 'Event URI',
    'LBL_EVENT_COMPONENTTYPE' => 'Event component type',
    'LBL_EVENT_FIRSTOCCURENCE' => 'Recurring event first occurrence',
    'LBL_EVENT_LASTOCCURENCE' => 'Recurring event last occurrence',
    'LBL_EVENT_UID' => 'The event\'s UID',
    'LBL_EVENT_RELATED_MODULE'=> 'Related Module',
    'LBL_EVENT_RELATED_MODULE_ID' => 'Related Module ID',
    'LBL_EVENT_SYNC_COUNTER' => 'Event sync counter',
    'LBL_EVENT_MODULE_SYNC_COUNTER' => 'Related module object sync counter',
    'LBL_EVENT_ETAG' => 'Etag for caching' ,
    'LBL_EVENT_SIZE' => 'Size in bytes',
    'LBL_EVENT_CALENDAR_ID' => 'Calendar ID',

    'LBL_CALENDAR_URI' => 'Calendar uri',
    'LBL_CALENDAR_ORDER' => 'Calendar order',
    'LBL_CALENDAR_COLOR' => 'Calendar color',
    'LBL_CALENDAR_TIMEZONE' => 'Timezone',
    'LBL_CALENDAR_SYNCTOKEN' => 'Calendar Sync token',
    'LBL_CALENDAR_COMPONENTS' => 'Supported calendar components',
    'LBL_CALENDAR_EVENTS' => 'Calendar events link',
    'LBL_EVENT_SYNC' => 'Events synchronization link',

    'LBL_CHANGE_OPERATION' => 'Operation type with object',
    'LBL_CHANGE_EVENT_URI' => 'Event URI',
    'LBL_CHANGE_CALENDAR_SYNCTOKEN' => 'Calendar Sync token',
    'LBL_CHANGE_CALENDAR_ID' => 'Calendar id',

    'LBL_SCHEDULING_PRINCIPALURI' => 'Principal uri',
    'LBL_SCHEDULING_LASTMODIFIED' => 'Modified time',
    'LBL_SCHEDULING_ETAG' => 'Etag for caching' ,
    'LBL_SCHEDULING_SIZE' => 'Size in bytes',

    'LBL_TITLE' => 'CalDav',
    'LBL_MODULE_NAME' => 'CalDav',

    'LBL_CALDAV_ENABLE_SYNC' => 'Enable calendar sync',
    'LBL_CALDAV_ENABLE_SYNC_DESC_ADMIN' => 'Enable calendar sync will allow your users to sync their '
        . 'Sugar Calendar to supported iCal calendar application.',
    'LBL_CALDAV_ENABLE_SYNC_WARNING' => 'Disabling calendar sync after it is already in use will stop syncing '
        . 'any current and future events to external calendar applications.',

    'LBL_CALDAV_TAB_TEXT' => 'Email & iCal Sync',
    'LBL_CALDAV_TITLE' => 'Calendar Sync Settings',

    'LBL_CALDAV_SETUP_INFO_TITLE' => 'Calendar setup information',
    'LBL_CALDAV_SETUP_INFO_TITLE_DESC' => 'Use this information to set up your editable Sugar Calendar ' .
        'in any iCal-supported applications.',
    'LBL_CALDAV_SETUP_INFO_SERVER_ADDRESS' => 'Server Address',
    'LBL_CALDAV_SETUP_INFO_SERVER_PATH' => 'Server Path',
    'LBL_CALDAV_SETUP_INFO_SERVER_PATH_DESC' => 'Please include the slash ("/") at the end of the server information.',
    'LBL_CALDAV_SETUP_INFO_SERVER_PORT' => 'Port',
    'LBL_CALDAV_SETUP_INFO_SERVER_PORT_DESC' => 'This is the default port. This port may change based on ' .
        'the configuration set by your admin.',

    'LBL_CONFIG_TITLE_MODULE_SETTINGS_ADMIN' => 'Calendar Sync System Settings',
    'LBL_CONFIG_TITLE_MODULE_SETTINGS' => 'Calendar Sync Settings',
    'LBL_CONFIG_ERROR_SELECT_MODULE' => 'Module should be selected',
    'LBL_CALDAV_DEFAULT_MODULE' => 'Default event mapping',
    'LBL_CALDAV_DEFAULT_MODULE_DESC_ADMIN' => 'Set the default type of record to be used when syncing events ' .
                                        'created through an external calendar application.',
    'LBL_CALDAV_DEFAULT_MODULE_DESC' => 'Set the default type of record to be used when syncing events ' .
        'created through an external calendar application.',
    'LBL_CALDAV_DEFAULT_INTERVAL' => 'Default sync setting',
    'LBL_CALDAV_DEFAULT_INTERVAL_DESC_ADMIN' => 'Set the default initial sync setting for your users (this will '.
        'determine how far back the initial sync will go). After the initial sync, all new or updated events will '.
        'sync between Sugar and external calendars, regardless of the event’s date or the options selected below.',
    'LBL_CALDAV_DEFAULT_INTERVAL_DESC' => 'Set the default initial sync setting (this will determine how far ' .
        'back the initial sync will go). After the initial sync, all new or updated events will sync between Sugar ' .
        'and external calendars, regardless of the event’s date or the options selected below.',
    'LBL_CALDAV_NO_MODULES_ACCESS' => 'You don\'t have access to any of modules that can be synchronized with '.
        'external calendar application.',

    'LBL_SYNC_SAVE_COUNTER' => 'Save event or module counter',
    'LBL_SYNC_JOB_COUNTER' => 'Counts of ending jobs',
);
