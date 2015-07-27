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
 * Class CalDavScheduling
 * Represents implementation of Sugar Bean for CalDAV backend operations with scheduling objects
 */
class CalDavScheduling extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavSchedulings';
    public $object_name = 'CalDavScheduling';
    public $table_name = 'caldav_scheduling';

    /**
     * Scheduling object ID
     * @var string
     */
    public $id;

    /**
     * Scheduling object name
     * @var string
     */
    public $name;

    /**
     * Scheduling object creation date
     * @var string
     */
    public $date_entered;

    /**
     * Scheduling object modified date
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
     * Scheduling object description
     * @var string
     */
    public $description;

    /**
     * Is object deleted or not
     * @var string
     */
    public $deleted;

    /**
     * Principal uri
     * @var string
     */
    public $principaluri;

    /**
     * Calendar event data in VOBJECT format
     * @var string
     */
    public $calendardata;

    /**
     * Scheduling object uri
     * @var string
     */
    public $uri;

    /**
     * Object modification date. Used for CalDAV server purposes only
     * @var string
     */
    public $lastmodified;

    /**
     * Object ETag. MD5 hash from $calendardata
     * @var string
     */
    public $etag;

    /**
     * $calendardata size in bytes
     * @var string
     */
    public $size;
}
