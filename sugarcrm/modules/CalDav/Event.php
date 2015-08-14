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

use Sabre\VObject;
use Sugarcrm\Sugarcrm\Dav\Base;

/**
 * Class CalDav
 * Represents implementation of Sugar Bean for CalDAV backend operations with calendar events
 */
class CalDavEvent extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavEvents';
    public $object_name = 'CalDavEvent';
    public $table_name = 'caldav_events';

    /**
     * Event ID
     * @var string
     */
    public $id;

    /**
     * Event name
     * @var string
     */
    public $name;

    /**
     * Event creation date
     * @var string
     */
    public $date_entered;

    /**
     * Event modification date
     * @var string
     */
    public $date_modified;

    /**
     * User who modified the event
     * @var string
     */
    public $modified_user_id;

    /**
     * User who created the event
     * @var string
     */
    public $created_by;

    /**
     * Event description
     * @var string
     */
    public $description;

    /**
     * Is Event deleted or not
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
     * Calendar ID for event
     * @var string
     */
    public $calendarid;

    /**
     * Event ETag. MD5 hash from $calendardata
     * @var string
     */
    public $etag;

    /**
     * $calendardata size in bytes
     * @var integer
     */
    public $size;

    /**
     * Event component type
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
     * Event's UID
     * @var string
     */
    public $uid;

    /**
     * Related module name
     * @var string
     */
    public $parent_type;

    /**
     * Related module id
     * @var string
     */
    public $parent_id;

    /**
     * CalDAV server event synchronization counter
     * @var integer
     */
    public $sync_counter;

    /**
     * Related module record synchronization counter
     * @var integer
     */
    public $module_sync_counter;


    /**
     * Calendar event is stored here
     * @var Sabre\VObject\Component\VCalendar
     */
    protected $vCalendarEvent = null;

    /**
     * Map for CalDav event status => SugarCRM meetings/calls status
     * @var array
     */
    protected $statusMap = array(
        'CANCELLED' => 'Not Held',
        'CONFIRMED' => 'Planned',
    );

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
     */
    protected $dateTimeHelper;

    /**
     * Calculate and set the size of the event data in bytes
     * @param string $data Calendar event text data
     */
    protected function calculateSize($data)
    {
        $this->size = strlen($data);
    }

    /**
     * Calculate and set calendar event ETag hash
     * @param string $data Calendar event text data
     */
    protected function calculateETag($data)
    {
        $this->etag = md5($data);
    }

    /**
     * Retrieve logger instance
     * @return \LoggerManager
     */
    protected function getLogger()
    {
        return $GLOBALS['log'];
    }

    /**
     * Get global application strings
     * @return array
     */
    protected function getAppListStrings()
    {
        global $app_list_strings;

        return $app_list_strings;
    }

    /**
     * Filter $statusMap for valid mappings key and return it
     * If mapping not found empty array should be returned to allow CalDav or SugarCRM module to select the default value
     * @return array
     */
    protected function getStatusMap()
    {
        $appStrings = $this->getAppListStrings();
        $relatedModule = $this->getBean();
        if (!isset($relatedModule->field_defs['status']['options'])) {
            $this->getLogger()->error('CalDavEvent can\'t retrieve status options for module '.$relatedModule->module_name);
            return array();
        }

        $optionsKey = $relatedModule->field_defs['status']['options'];

        if (!isset($appStrings[$optionsKey])) {
            $this->getLogger()->error('CalDavEvent can\'t retrieve status options for module '.$relatedModule->module_name);
            return array();
        }

        $result = array();
        foreach ($this->statusMap as $davKey => $sugarKey) {
            if (isset($appStrings[$optionsKey][$sugarKey])) {
                $result[$davKey] = $sugarKey;
            }
        }

        return $result;
    }

    /**
     * Retrieve component from vObject
     * @param Sabre\VObject\Component\VCalendar $vObject
     * @return \Sabre\VObject\Component\VEvent | null
     */
    protected function getComponent(Sabre\VObject\Component\VCalendar $vObject)
    {
        $components = $vObject->getComponents();
        foreach ($components as $component) {
            if ($component->name !== 'VTIMEZONE') {
                return $component;
            }
        }

        return null;
    }

    /**
     * Retrieve component type from vobject
     * Component type can be VEVENT, VTODO or VJOURNAL
     * @param string $data Calendar event text data
     * @return bool True if component type found and valid
     */
    protected function calculateComponentType($data)
    {
        $vObject = VObject\Reader::read($data);
        $component = $this->getComponent($vObject);
        if ($component) {
            $this->componenttype = $component->name;
            $this->uid = $component->UID;

            return true;
        }

        return false;
    }

    /**
     * Calculate firstoccurence and lastoccurence of event
     * @param string $data Calendar event text data
     */
    protected function calculateTimeBoundaries($data)
    {
        $vObject = VObject\Reader::read($data);
        $component = $this->getComponent($vObject);
        if ($component->name === 'VEVENT') {
            $this->firstoccurence = $component->DTSTART->getDateTime()->getTimestamp();

            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $this->lastoccurence = $component->DTEND->getDateTime()->getTimestamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $this->lastoccurence = $endDate->getTimestamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $this->lastoccurence = $endDate->getTimestamp();
                } else {
                    $this->lastoccurence = $this->firstoccurence;
                }
            } else {
                $it = new VObject\Recur\EventIterator($vObject, $component->UID);
                $maxRecur = Base\Constants::MAX_INFINITE_RECCURENCE_COUNT;

                $endDate = clone $component->DTSTART->getDateTime();
                $endDate->modify('+' . $maxRecur . ' day');
                if ($it->isInfinite()) {
                    $this->lastoccurence = $endDate->getTimestamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $endDate) {
                        $end = $it->getDtEnd();
                        $it->next();
                    }
                    $this->lastoccurence = $end->getTimestamp();
                }
            }
        }
    }

    /**
     * Add Change to CalDav changes table
     * @param $operation
     */
    protected function addChange($operation)
    {
        $calendar = $this->getRelatedCalendar($this->calendarid);
        if ($calendar) {
            $changes = $this->getChangesBean();
            $changes->add($calendar, $this->uri, $operation);

            $calendar->synctoken ++;
            $calendar->save();
        }
    }

    /**
     * Retrieve VCalendar Event
     * @return VObject\Component\VCalendar|VObject\Document
     */
    protected function getVCalendarEvent()
    {
        if (!empty($this->vCalendarEvent)) {
            return $this->vCalendarEvent;
        }
        if (empty($this->calendardata)) {
            $this->vCalendarEvent = new Sabre\VObject\Component\VCalendar();
            $timezone = $this->vCalendarEvent->createComponent('VTIMEZONE');
            $timezone->TZID = $GLOBALS['current_user']->getPreference('timezone');
            $this->vCalendarEvent->add($timezone);
        } else {
            $this->vCalendarEvent = VObject\Reader::read($this->calendardata);
        }

        return $this->vCalendarEvent;
    }

    /**
     * Gets string property from event
     * @param string $propertyName
     * @return null|string
     */
    protected function getVObjectStringProperty($propertyName)
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        if ($component && $component->$propertyName) {
            return $component->$propertyName->getValue();
        }

        return null;
    }

    /**
     * Set string property of event
     * Return true if property was changed or false otherwise
     * @param string $propertyName
     * @param string $value
     * @param VObject\Component $parent Parent component for property
     * @return bool
     */
    protected function setVObjectStringProperty($propertyName, $value, Sabre\VObject\Component $parent)
    {
        if (!$parent->$propertyName) {
            $prop = $parent->parent->createProperty($propertyName, $value);
            $parent->add($prop);

            return true;
        }
        if ($parent->$propertyName->getValue() !== $value) {
            $parent->$propertyName->setValue($value);

            return true;
        }

        return false;
    }

    /**
     * Set DateTime property of event
     * @param string $propertyName
     * @param string $value
     * @param VObject\Component $parent
     * @return bool
     */
    protected function setVObjectDateTimeProperty($propertyName, $value, Sabre\VObject\Component $parent)
    {
        if (!$parent->$propertyName) {
            $dateTimeElement = $parent->parent->createProperty($propertyName);
            $dateTimeElement->setDateTime($this->dateTimeHelper->sugarDateToDav($value));
            $parent->add($dateTimeElement);

            return true;
        }

        $utcTZ = new DateTimeZone('UTC');
        $checkDateTime = new DateTime($this->dateTimeHelper->davDateToSugar($parent->$propertyName), $utcTZ);
        $currentDateTime = new DateTime($value, $utcTZ);

        if ($currentDateTime != $checkDateTime) {
            $parent->$propertyName->setDateTime($this->dateTimeHelper->sugarDateToDav($value));

            return true;
        }

        return false;
    }

    /**
     * Create VALARM component
     * @param string $duration Duration in DURATION format
     * @param VObject\Component $parent
     * @param string $action
     * @todo For full reminders functionality  participant helper should be used.
     */
    protected function createReminderComponent($duration, Sabre\VObject\Component $parent, $action)
    {
        $alarm = $parent->parent->createComponent('VALARM');
        $alarm->add($parent->parent->createProperty('ACTION', $action));
        $alarm->add($parent->parent->createProperty('TRIGGER', $duration));
        $parent->add($alarm);
    }

    public function __construct()
    {
        $this->dateTimeHelper = new Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper();
        parent::__construct();
    }

    /**
     * Parse text calendar event data to database fields
     * @param string $data Calendar event text data
     * @return bool True - if all data are correct and were set, false in otherwise
     */
    public function setCalendarEventData($data)
    {
        if (empty($data)) {
            return false;
        }

        if (!$this->calculateComponentType($data)) {
            return false;
        }

        $this->calendardata = $data;

        $this->calculateTimeBoundaries($data);
        $this->calculateSize($data);
        $this->calculateETag($data);

        return true;
    }

    /**
     * Set calendar id
     * @param string $calendarId
     */
    public function setCalendarId($calendarId)
    {
        $this->calendarid = $calendarId;
    }

    /**
     * Set event URI
     * @param string $eventURI
     */
    public function setCalendarEventURI($eventURI)
    {
        $this->uri = $eventURI;
    }

    /**
     * Convert bean to array which used by CalDav backend
     * @return array
     */
    public function toCalDavArray()
    {
        return array(
            'id' => $this->id,
            'uri' => $this->uri,
            'lastmodified' => strtotime($this->date_modified),
            'etag' => '"' . $this->etag . '"',
            'calendarid' => $this->calendarid,
            'size' => $this->size,
            'calendardata' => $this->calendardata,
            'component' => strtolower($this->componenttype),
        );
    }

    /**
     * Get instance of CalDavChange
     * @return null|CalDavChange
     */
    public function getChangesBean()
    {
        return \BeanFactory::getBean('CalDavChanges');
    }

    /**
     * Retrieve Calendar for event
     * @param $calendarId
     * @return null|SugarBean
     */
    public function getRelatedCalendar($calendarId)
    {
        if ($this->load_relationship('events_calendar')) {
            return \BeanFactory::getBean($this->events_calendar->getRelatedModuleName(), $calendarId);
        }

        return null;
    }

    /**
     * Retrieve set of events by calendarID and URI
     * @param string $calendarId
     * @param array $uri
     * @param bool $fetchOne Fetch only one event or not
     * @return CalDavEvent[]|CalDavEvent
     * @throws SugarQueryException
     */
    public function getByURI($calendarId, array $uri, $fetchOne = false)
    {
        $result = array();
        if ($this->load_relationship('events_calendar')) {
            $query = new \SugarQuery();
            $query->from($this);
            $query->where()->equals('calendarid', $calendarId);
            $query->where()->in('caldav_events.uri', $uri);
            if ($fetchOne) {
                $query->limit(1);
            }

            $result = $this->fetchFromQuery($query);
            if ($result && $fetchOne) {
                return array_shift($result);
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function save($check_notify = false)
    {
        $operation = $this->isUpdate() ? Base\Constants::OPERATION_MODIFY : Base\Constants::OPERATION_ADD;
        $this->addChange($operation);

        return parent::save($check_notify);
    }

    /**
     * @inheritdoc
     */
    public function mark_deleted($id)
    {
        if (!$this->deleted) {
            $this->addChange(Base\Constants::OPERATION_DELETE);
        }
        parent::mark_deleted($id);
    }

    /**
     * Get title (SUMMARY) of event
     * @return null|string
     */
    public function getTitle()
    {
        return $this->getVObjectStringProperty('SUMMARY');
    }

    /**
     * Set the title (SUMMARY) of event
     * Return true if title was changed or false otherwise
     * @param string $value
     * @param VObject\Component $parent Parent component for title
     * @return bool
     */
    public function setTitle($value, Sabre\VObject\Component $parent)
    {
        return $this->setVObjectStringProperty('SUMMARY', $value, $parent);
    }

    /**
     * Get description of event
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getVObjectStringProperty('DESCRIPTION');
    }

    /**
     * Set the description of event
     * Return true if description was changed or false otherwise
     * @param string $value
     * @param VObject\Component $parent Parent component from CalDavEvent::setType
     * @return bool
     */
    public function setDescription($value, Sabre\VObject\Component $parent)
    {
        return $this->setVObjectStringProperty('DESCRIPTION', $value, $parent);
    }

    /**
     * Get start datetime of event.
     * Format of CalDav datetime 20150806T110000
     * @return null|string
     */
    public function getStartDate()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        if ($component && $component->DTSTART) {
            return $this->dateTimeHelper->davDateToSugar($component->DTSTART);
        }

        return null;
    }

    /**
     * Set start date of event
     * @param string $value
     * @param VObject\Component $parent
     * @return bool
     */
    public function setStartDate($value, Sabre\VObject\Component $parent)
    {
        return $this->setVObjectDateTimeProperty('DTSTART', $value, $parent);
    }

    /**
     * Get end datetime of event.
     * DTEND is present only for VEVENT
     * For VTODO DUE should be used
     * Format of CalDav datetime 20150806T110000
     * @return null|string
     */
    public function getEndDate()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        if ($component) {
            if ($component->DTEND) {
                return $this->dateTimeHelper->davDateToSugar($component->DTEND);
            } elseif ($component->DUE) {
                return $this->dateTimeHelper->davDateToSugar($component->DUE);
            }
        }

        return null;
    }

    /**
     * Set end date of event
     * @param string $value
     * @param VObject\Component $parent
     * @return bool
     */
    public function setEndDate($value, Sabre\VObject\Component $parent)
    {
        if ($parent instanceof Sabre\VObject\Component\VEvent) {
            return $this->setVObjectDateTimeProperty('DTEND', $value, $parent);
        }

        return false;
    }

    /**
     * Set due date of vtodo
     * @param string $value
     * @param VObject\Component $parent
     * @return bool
     */
    public function setDueDate($value, Sabre\VObject\Component $parent)
    {
        if ($parent instanceof Sabre\VObject\Component\VTodo) {
            return $this->setVObjectDateTimeProperty('DUE', $value, $parent);
        }

        return false;
    }

    /**
     * Get Event VTIMEZONE section and return timezone in string representation
     * @return string
     */
    public function getTimeZone()
    {
        $event = $this->getVCalendarEvent();
        if ($event->VTIMEZONE) {
            return $event->VTIMEZONE->TZID->getValue();
        }

        return 'UTC';
    }

    /**
     * Get event location
     * @return null|string
     */
    public function getLocation()
    {
        return $this->getVObjectStringProperty('LOCATION');
    }

    /**
     * Set the location of event
     * Return true if location was changed or false otherwise
     * @param string $value
     * @param VObject\Component $parent Parent component from CalDavEvent::setType
     * @return bool
     */
    public function setLocation($value, Sabre\VObject\Component $parent)
    {
        return $this->setVObjectStringProperty('LOCATION', $value, $parent);
    }

    /**
     * Get DURATION or calculate duration by begin and end time
     * @see http://tools.ietf.org/html/rfc5545#page-34
     * @return integer
     */
    public function getDuration()
    {
        $duration = 0;
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        if (!empty($component->DURATION)) {
            $duration = intval($this->dateTimeHelper->durationToSeconds($component->DURATION->getValue()) / 60);
        } else {
            $begin = $this->getStartDate();
            $end = $this->getEndDate();
            if ($begin && $end) {
                return intval((strtotime($end) - strtotime($begin)) / 60);
            }
        }

        return $duration;
    }

    /**
     * Set event duration by hours and minutes
     * @param int $hours
     * @param int $minutes
     * @param VObject\Component $parent
     * @return bool
     */
    public function setDuration($hours, $minutes, Sabre\VObject\Component $parent)
    {
        $duration = $this->dateTimeHelper->secondsToDuration($hours * 3600 + $minutes * 60);

        return $this->setVObjectStringProperty('DURATION', $duration, $parent);
    }

    /**
     * Get duration full hours
     * @return int
     */
    public function getDurationHours()
    {
        return intval($this->getDuration() / 60);
    }

    /**
     * Get duration full minutes
     * @return int
     */
    public function getDurationMinutes()
    {
        return intval($this->getDuration() % 60);
    }

    /**
     * Get event visibility (PUBLIC, PRIVATE, CONFIDENTIAL)
     * @return null|string
     */
    public function getVisibility()
    {
        return $this->getVObjectStringProperty('CLASS');
    }

    /**
     * Get organizer of event.  At now returns array with organizer's email, status and role only
     * @return null|array
     *
     * @todo For full functionality participant helper should be used.
     */
    public function getOrganizer()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);

        if ($component && $component->ORGANIZER) {
            $organizer = $component->ORGANIZER->getValue();
            $params = $component->ORGANIZER->parameters();

            return array(
                'user' => $organizer,
                'status' => $params['PARTSTAT']->getValue(),
                'role' => $params['ROLE']->getValue(),
            );
        }

        return null;
    }

    /**
     * Set organizer of event
     * @param mixed $value
     * @param VObject\Component $parent
     * @return bool
     *
     * @todo It will be implemented when "participant helper" becomes available
     */
    public function setOrganizer($value, Sabre\VObject\Component $parent)
    {
        return false;
    }

    /**
     * Get participants of event
     * At now returns array with email, status and role only
     * @return array[]
     *
     * @todo For full functionality participant helper should be used.
     */
    public function getParticipants()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        $result = array();
        if ($component && $component->ATTENDEE) {
            foreach ($component->ATTENDEE as $participant) {
                $params = $participant->parameters();
                $result[] = array(
                    'user' => $participant->getValue(),
                    'status' => $params['PARTSTAT']->getValue(),
                    'role' => $params['ROLE']->getValue(),
                );
            }
        }

        return $result;
    }

    /**
     * Set participants of event
     * @param mixed $value
     * @param VObject\Component $parent
     * @return bool
     *
     * @todo It will be implemented when "participant helper" becomes available
     */
    public function setParticipants($value, Sabre\VObject\Component $parent)
    {
        return false;
    }

    /**
     * Get all reminders info
     *
     * Returns array of defined reminders
     * array keys:
     *      DISPLAY - popup window
     *      EMAIL - email message
     * array values:
     *      duration - duration value in seconds
     *      description - reminder message
     *      attendees - reminder partipiants
     *
     * @return array[] See above
     *
     * @todo Need helper to convert duration to SugarCRM allowed reminder duration
     * @todo For full reminders functionality  participant helper should be used.
     */
    public function getReminders()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        $result = array();
        if ($component && $component->VALARM) {
            foreach ($component->VALARM as $alarm) {
                if ($alarm->TRIGGER instanceof Sabre\VObject\Property\ICalendar\Duration) {
                    $attendees = array();
                    $duration = $this->dateTimeHelper->durationToSeconds($alarm->TRIGGER->getValue());

                    //SugarCRM not support notifications after beginning of event
                    if ($duration >= 0) {
                        continue;
                    }

                    $description = $alarm->DESCRIPTION ? $alarm->DESCRIPTION->getValue() : '';
                    if ($alarm->ATTENDEE) {
                        foreach ($alarm->ATTENDEE as $attendee) {
                            $email = $attendee->getValue();
                            $params = $attendee->parameters();
                            $attendees[] = array(
                                'user' => $email,
                                'status' => $params['PARTSTAT']->getValue(),
                                'role' => $params['ROLE']->getValue(),
                            );
                        }
                    }

                    $result[$alarm->ACTION->getValue()] = array(
                        'duration' => abs($duration),
                        'description' => $description,
                        'attendees' => $attendees,
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Set reminder params.
     * It is possible to set several reminders such as "popup window" and "email message"
     * @param int $value Duration in seconds
     * @param VObject\Component $parent
     * @param string $action :
     *      DISPLAY - popup window
     *      EMAIL - email message
     * @return bool
     */
    public function setReminder($value, Sabre\VObject\Component $parent, $action = 'DISPLAY')
    {
        if (!$value) {
            return false;
        }

        $duration = $this->dateTimeHelper->secondsToDuration(0 - $value);

        if (!$parent->VALARM) {
            $this->createReminderComponent($duration, $parent, $action);

            return true;
        }

        $selectedAlarm = null;
        foreach ($parent->VALARM as $alarm) {
            if ($alarm->ACTION->getValue() === $action) {
                $selectedAlarm = $alarm;
                break;
            }
        }

        if (!$selectedAlarm) {
            $this->createReminderComponent($duration, $parent, $action);

            return true;
        }

        if ($selectedAlarm->TRIGGER instanceof Sabre\VObject\Property\ICalendar\Duration &&
            $selectedAlarm->TRIGGER->getValue() !== $duration
        ) {
            $selectedAlarm->TRIGGER->setValue($duration);

            return true;
        }

        return false;
    }

    /**
     * Get recurring event info
     * @return null | array
     *
     * @todo For full functionality recurring helper should be used.
     */
    public function getRRule()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        if ($component && $component->RRULE) {
            $aRule = $component->RRULE->getParts();
            if ($aRule) {
                $result = array();

                if (isset($aRule['FREQ'])) {
                    $result['type'] = ucfirst(strtolower($aRule['FREQ']));
                }

                if (isset($aRule['INTERVAL'])) {
                    $result['interval'] = $aRule['INTERVAL'];
                }

                if (isset($aRule['UNTIL'])) {
                    $dateTime = SugarDateTime::createFromFormat('Ymd\THis\Z', $aRule['UNTIL'], new DateTimeZone('UTC'));
                    $result['until'] = $dateTime->asDb();
                }

                if (isset($aRule['COUNT'])) {
                    $result['count'] = $aRule['COUNT'];
                }

                if (isset($aRule['BYDAY'])) {
                    $result['byday'] = $aRule['BYDAY'];
                }

                return $result;
            }
        }

        return null;
    }

    /**
     * Set recurring rules of event
     * @param mixed $value
     * @param VObject\Component $parent
     * @return bool
     *
     * @todo It will be implemented when "recurring helper" becomes available
     */
    public function setRRule($value, Sabre\VObject\Component $parent)
    {
        return false;
    }

    /**
     * Get status of event in SugarCRM format
     * @see $statusMap for avaliable statuses
     * @return null|string
     */
    public function getStatus()
    {
        $status = $this->getVObjectStringProperty('STATUS');
        $statusMap = $this->getStatusMap();
        if (isset($statusMap[$status])) {
            return $statusMap[$status];
        }

        return null;
    }

    /**
     * Set the status of event
     * @see $statusMap for avaliable statuses
     * Return true if status was changed or false otherwise
     * @param string $value
     * @param VObject\Component $parent Parent component from CalDavEvent::setType
     * @return bool
     */
    public function setStatus($value, Sabre\VObject\Component $parent)
    {
        $statusMap = array_flip($this->getStatusMap());

        if (!isset($statusMap[$value])) {
            return false;
        }

        return $this->setVObjectStringProperty('STATUS', $statusMap[$value], $parent);
    }

    public function vObjectToString()
    {
        $event = $this->getVCalendarEvent();

        return $event->serialize();
    }

    /**
     * Create component and return it
     * If component already exists it should be returned
     * @param string $componentType Component type VEVENT or VTODO
     * @return VObject\Component
     *
     * @throws \InvalidArgumentException
     */
    public function setType($componentType)
    {
        $event = $this->getVCalendarEvent();
        $currentComponent = $this->getComponent($event);
        if ($currentComponent && $currentComponent->name == $componentType) {
            return $currentComponent;
        } else {
            $component = $event->createComponent($componentType);

            return $event->add($component);
        }
    }

    /**
     * Retrive related bean
     * @return null|SugarBean
     *
     * @todo Default module name should be retrieved from config
     */
    public function getBean()
    {
        if ($this->parent_type) {
            return BeanFactory::getBean($this->parent_type, $this->parent_id);
        }

        return BeanFactory::getBean('Meetings');
    }

    /**
     * Set related bean
     * Return false if any errors occurred
     * @param SugarBean $bean
     * @return bool|String
     */
    public function setBean(\SugarBean $bean)
    {
        if ($bean->id) {
            $this->parent_type = $bean->module_name;
            $this->parent_id = $bean->id;
        }

        return false;
    }

    /**
     * Retrive CalDavEvent by parent bean
     * @param SugarBean $bean
     * @return mixed|null
     * @throws SugarQueryException
     */
    public function findByBean(\SugarBean $bean)
    {
        $query = new \SugarQuery();
        $query->from($this);
        $query->where()->equals('parent_type', $bean->module_name);
        $query->where()->equals('parent_id', $bean->id);
        $query->limit(1);
        $result = $this->fetchFromQuery($query);
        if ($result) {
            return array_shift($result);
        }

        return null;
    }
}
