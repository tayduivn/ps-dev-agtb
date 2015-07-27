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

/**
 * Class CalDav
 * Represents implementation of Sugar Bean for CalDAV backend operations with calendar events
 */
class CalDavEvent extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDav';
    public $object_name = 'CalDavEvent';
    public $table_name = 'caldav_events';

    /**
     * Object ID
     * @var string
     */
    public $id;

    /**
     * Object name
     * @var string
     */
    public $name;

    /**
     * Object creation date
     * @var string
     */
    public $date_entered;

    /**
     * Object modification date
     * @var string
     */
    public $date_modified;

    /**
     * User who modified the object
     * @var string
     */
    public $modified_user_id;

    /**
     * User who created the object
     * @var string
     */
    public $created_by;

    /**
     * Object description
     * @var string
     */
    public $description;

    /**
     * Is object deleted or not
     * @var integer
     */
    public $deleted;

    /**
     * Calendar event data in VOBJECT format
     * @var string
     */
    public $calendardata;

    /**
     * Calendar URI
     * @var string
     */
    public $uri;

    /**
     * Calendar ID for object
     * @var string
     */
    public $calendarid;

    /**
     * Object modification date. Used for CalDAV server purposes only
     * @var integer
     */
    public $lastmodified;

    /**
     * Object ETag. MD5 hash from $calendardata
     * @var string
     */
    public $etag;

    /**
     * $calendardata size in bytes
     * @var integer
     */
    public $size;

    /**
     * Object component type
     * @var string
     */
    public $componenttype;

    /**
     * Recurring event first occurrence
     * @var string
     */
    public $firstoccurence;

    /**
     * Recurring event last occurrence
     * @var string
     */
    public $lastoccurence;

    /**
     * Object's UID
     * @var string
     */
    public $uid;

    /**
     * Related module name
     * @var string
     */
    public $related_module;

    /**
     * Related module id
     * @var string
     */
    public $related_module_id;

    /**
     * CalDAV server object synchronization counter
     * @var integer
     */
    public $sync_counter;

    /**
     * Related module record synchronization counter
     * @var integer
     */
    public $module_sync_counter;
}
