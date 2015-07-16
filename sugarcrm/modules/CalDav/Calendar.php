<?php
if(!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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
 * Class CalDavCalendar
 * Represents implementation of Sugar Bean for CalDAV backend operations with user calendar
 */
class CalDavCalendar extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavCalendars';
    public $object_name = 'CalDavCalendar';
    public $table_name = 'caldav_calendars';

    /**
     * Calendar ID
     * @var string
     */
    public $id;

    /**
     * Calendar name
     * @var string
     */
    public $name;

    /**
     * Calendar creation date
     * @var string
     */
    public $date_entered;

    /**
     * Calendar modification date
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
     * Calendar description
     * @var string
     */
    public $description;

    /**
     * Is calendar deleted or not
     * @var string
     */
    public $deleted;

    /**
     * Calendar title
     * @var string
     */
    public $displayname;

    /**
     * Calendar URI
     * @var string
     */
    public $uri;

    /**
     * Synchronization token for CalDav server purposes
     * @var integer
     */
    public $synctoken;

    /**
     * Calendar order for iCal
     * @var integer
     */
    public $calendarorder;

    /**
     * Calendar color for iCal
     * @var string
     */
    public $calendarcolor;

    /**
     * Specifies a time zone on a calendar collection
     * @var string
     */
    public $timezone;

    /**
     * Supported calendar components set
     * @var string
     */
    public $components;

    /**
     * Determines whether the calendar object resources in a calendar collection will affect the owner's busy time information.
     * @var integer
     */
    public $transparent;

    /**
     * Owner of the calendar
     * @var string
     */
    public $assigned_user_id;
}
