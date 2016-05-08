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

use Sabre\DAV;
use Sabre\CalDAV;
use Sugarcrm\Sugarcrm\Dav\Base;
use Sabre\VObject\Component as SabreComponent;

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
    public $disable_custom_fields = true;

    /**
     * Calendar ID
     * @var string
     */
    public $id;

    /**
     * Calendar display name
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
     * Calendar URI
     * @var string
     */
    public $uri;

    /**
     * Synchronization token for CalDav server purposes
     * @var integer
     */
    public $synctoken = 0;

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
    public $components = 'VEVENT,VTODO';

    /**
     * Determines whether the calendar object resources in a calendar collection will affect the owner's busy time information.
     * @var integer
     */
    public $transparent = 0;

    /**
     * Owner of the calendar
     * @var string
     */
    public $assigned_user_id;

    /**
     * Fields that are allowed to change
     * @var array
     */
    protected $allowedFieldsToChange = array(
        'name',
        'description',
        'timezone',
        'calendarorder',
        'calendarcolor',
        'transparent',
    );

    /**
     * Get calendar componets array
     * @return array | null
     */
    public function getComponents()
    {
        if ($this->components) {
            return explode(',', $this->components);
        }

        return null;
    }

    /**
     * Get transparent info
     * @return string
     */
    public function getTransparent()
    {
        return $this->transparent ? 'transparent' : 'opaque';
    }

    /**
     * Create default calendar for selected user
     * @param User $user
     * @return array Fetched row of a created Calendar Bean.
     */
    public function createDefaultForUser(User $user)
    {
        $this->uri = Base\Constants::DEFAULT_CALENDAR_URI;
        $this->name = translate('LBL_DAFAULT_CALDAV_NAME');
        $this->assigned_user_id = $user->id;

        $vCalendarEvent = new SabreComponent\VCalendar();
        $timezone = $vCalendarEvent->createComponent('VTIMEZONE');
        $currentTimezone = $user->getPreference('timezone');
        if (!$currentTimezone) {
            $currentTimezone = date_default_timezone_get();
        }
        $timezone->TZID = $currentTimezone;
        $vCalendarEvent->add($timezone);

        $this->timezone = $vCalendarEvent->serialize();
        $this->save();
        // TODO: Remove when after insert fetched_row will be present
        $this->retrieve();

        $GLOBALS['log']->info("CalDav: Created default Calendar($this->id) for User($user->id)");

        return $this->fetched_row;
    }

    /**
     * Checks if calendar was changed
     * @return bool
     */
    public function isChanged()
    {
        if (empty($this->fetched_row)) {
            return true;
        }

        foreach ($this->allowedFieldsToChange as $field) {
            if (!array_key_exists($field, $this->fetched_row) || $this->$field != $this->fetched_row[$field]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add change to database
     */
    protected function addChange()
    {
        if ($this->isUpdate() && $this->isChanged()) {
            $changes = \BeanFactory::getBean('CalDavChanges');
            $changes->add($this, "", Base\Constants::OPERATION_MODIFY);
            $this->synctoken ++;
        }
    }

    public function save($check_notify = false)
    {
        $this->addChange();

        return parent::save($check_notify);
    }
}
