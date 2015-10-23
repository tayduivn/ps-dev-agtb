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
use Sabre\VObject\Component as SabreComponent;
use Sugarcrm\Sugarcrm\Dav\Base\Helper as DavHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as DavStatusMapper;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;

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
    public $data_size;

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
    public $event_uid;

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
     * Calendar event is stored here
     * @var Sabre\VObject\Component\VCalendar
     */
    protected $vCalendarEvent = null;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
     */
    protected $dateTimeHelper;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
     */
    protected $participantsHelper;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap
     */
    protected $statusMapper;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\ServerHelper
     */
    protected $serverHelper;

    /**
     * Is mail template generating or not
     * @var bool
     */
    protected $inMailGeneration;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper
     */
    protected $recurringHelper;

    /**
     * Calculate and set the size of the event data in bytes
     * @param string $data Calendar event text data
     */
    protected function calculateSize($data)
    {
        $this->data_size = strlen($data);
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
     * Retrieve component from vObject
     * @param Sabre\VObject\Component\VCalendar $vObject
     * @return Sabre\VObject\Component\VEvent | null
     */
    public function getComponent(SabreComponent\VCalendar $vObject)
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
            $this->event_uid = $component->UID;

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
                $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;

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
     * Retrieve current_user
     * @return \User
     */
    protected function getCurrentUser()
    {
        return $GLOBALS['current_user'];
    }

    /**
     * Retrieve VCalendar Event
     * @return Sabre\VObject\Component\VCalendar
     */
    public function getVCalendarEvent()
    {
        if (!empty($this->vCalendarEvent)) {
            return $this->vCalendarEvent;
        }
        if (empty($this->calendardata)) {
            $this->vCalendarEvent = new SabreComponent\VCalendar();
            $timezone = $this->vCalendarEvent->createComponent('VTIMEZONE');
            $timezone->TZID = $this->getCurrentUser()->getPreference('timezone');
            $this->vCalendarEvent->add($timezone);
        } else {
            $this->vCalendarEvent = VObject\Reader::read($this->calendardata);
        }

        return $this->vCalendarEvent;
    }

    /**
     * Clearing current vCalendarEvent
     */
    public function clearVCalendarEvent()
    {
        $this->vCalendarEvent = null;
    }

    /**
     * Set calendar event object
     * @param SabreComponent\VCalendar $vEvent
     */
    public function setVCalendarEvent(SabreComponent\VCalendar $vEvent)
    {
        $this->vCalendarEvent = $vEvent;
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
     * Delete VObject property
     * @param SabreComponent $parent
     * @param string $propertyName
     * @return bool
     */
    protected function deleteVObjectProperty(SabreComponent $parent, $propertyName)
    {
        if ($parent->$propertyName) {
            $parent->remove($parent->$propertyName);
            return true;
        }

        return false;
    }

    /**
     * Set string property of event
     * Return true if property was changed or false otherwise
     * @param string $propertyName
     * @param string $value
     * @param Sabre\VObject\Component $parent Parent component for property
     * @return bool
     */
    protected function setVObjectStringProperty($propertyName, $value, SabreComponent $parent)
    {
        if (!$value) {
            return $this->deleteVObjectProperty($parent, $propertyName);
        }

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
     * Modify existing participants to event
     * @param array $participants Participants to modify
     * @param Sabre\VObject\Component $parent
     * @param string $componentName
     */
    protected function modifyParticipants(array $participants, SabreComponent $parent, $componentName = 'ATTENDEE')
    {
        $nodes = $parent->select($componentName);

        foreach ($nodes as $node) {
            $part = $participants;
            $participant = array_filter($part, function ($arr) use ($node) {
                return $arr['davLink'] == strtolower($node->getValue());
            });

            if ($participant) {
                $key = key($participant);
                $node->setValue($key);

                if (isset($node['PARTSTAT'])) {
                    $node['PARTSTAT'] = $participant[$key]['PARTSTAT'];
                }
            }
        }
    }

    /**
     * Add participants to event
     * @param array $participants Participants to add
     * @param Sabre\VObject\Component $parent
     */
    protected function addParticipants(array $participants, SabreComponent $parent, $componentName = 'ATTENDEE')
    {
        foreach ($participants as $email => $parcipiant) {
            if (array_key_exists('davLink', $parcipiant)) {
                unset($parcipiant['davLink']);
            }
            $parent->add($componentName, $email, $parcipiant);
        }
    }

    /**
     * Delete participants from event
     * @param array $participants Participants to delete
     * @param Sabre\VObject\Component $parent
     * @param string $componentName
     */
    protected function deleteParticipants(array $participants, SabreComponent $parent, $componentName = 'ATTENDEE')
    {
        $nodes = $parent->select($componentName);

        foreach ($nodes as $node) {
            if (isset($participants[strtolower($node->getValue())])) {
                $parent->remove($node);
            }
        }
    }

    /**
     * Set DateTime property of event
     * @param string $propertyName
     * @param string $value
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    protected function setVObjectDateTimeProperty($propertyName, $value, SabreComponent $parent)
    {
        if (!$value) {
            return $this->deleteVObjectProperty($parent, $propertyName);
        }

        $value = $this->dateTimeHelper->sugarDateToUTC($value)->format(\TimeDate::DB_DATETIME_FORMAT);
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
     * @param Sabre\VObject\Component $parent
     * @param string $action
     */
    protected function createReminderComponent($duration, SabreComponent $parent, $action)
    {
        $alarm = $parent->parent->createComponent('VALARM');
        $alarm->add($parent->parent->createProperty('ACTION', $action));
        $alarm->add($parent->parent->createProperty('TRIGGER', $duration));
        $parent->add($alarm);
    }

    public function __construct()
    {
        $this->dateTimeHelper = new DavHelper\DateTimeHelper();
        $this->recurringHelper = new DavHelper\RecurringHelper();
        $this->participantsHelper = new DavHelper\ParticipantsHelper();
        $this->statusMapper = new DavStatusMapper\EventMap();
        $this->serverHelper = new DavHelper\ServerHelper();
        parent::__construct();
    }

    /**
     * Get component name for event
     * @return string
     */
    public function getComponentTypeName()
    {
        return 'VEVENT';
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

        $vObject = VObject\Reader::read($data);

        if ($vObject->{'X-PARENT-UID'}) {
            $this->parent_type = $this->module_name;
        }

        $this->calendardata = $data;

        $this->calculateTimeBoundaries($data);
        $this->calculateSize($data);
        $this->calculateETag($data);

        if (empty($this->uri) && !empty($this->event_uid)) {
            $this->uri = $this->event_uid . '.ics';
        }

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
            'size' => $this->data_size,
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
        if (!$calendarId) {
            return null;
        }

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
        $operation = $this->isUpdate() ? DavConstants::OPERATION_MODIFY : DavConstants::OPERATION_ADD;
        $this->addChange($operation);

        return parent::save($check_notify);
    }

    /**
     * @inheritdoc
     */
    public function mark_deleted($id)
    {
        if (!$this->deleted) {
            $this->addChange(DavConstants::OPERATION_DELETE);
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
     * @param Sabre\VObject\Component $parent Parent component for title
     * @return bool
     */
    public function setTitle($value, SabreComponent $parent)
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
     * @param Sabre\VObject\Component $parent Parent component from CalDavEvent::setComponent
     * @return bool
     */
    public function setDescription($value, SabreComponent $parent)
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
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setStartDate($value, SabreComponent $parent)
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
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setEndDate($value, SabreComponent $parent)
    {
        if ($parent instanceof SabreComponent\VEvent) {
            return $this->setVObjectDateTimeProperty('DTEND', $value, $parent);
        }

        return false;
    }

    /**
     * Set due date of vtodo
     * @param string $value
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setDueDate($value, SabreComponent $parent)
    {
        if ($parent instanceof SabreComponent\VTodo) {
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
     * @param Sabre\VObject\Component $parent Parent component from CalDavEvent::setComponent
     * @return bool
     */
    public function setLocation($value, SabreComponent $parent)
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
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setDuration($hours, $minutes, SabreComponent $parent)
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
     * @see Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::prepareForSugar
     * @return array
     */
    public function getOrganizer()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        $result = array();
        if ($component && $component->ORGANIZER) {
            $result = $this->participantsHelper->prepareForSugar($this, $component->ORGANIZER);
        }

        return $result;
    }

    /**
     * Set organizer of event
     * @param \SugarBean $bean
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setOrganizer(\SugarBean $bean, SabreComponent $parent)
    {
        $attendees = $this->participantsHelper->prepareForDav($bean, $this, 'ORGANIZER');

        if (!$attendees) {
            return false;
        }

        $nodes = $parent->select('ORGANIZER');
        foreach ($nodes as $node) {
            $parent->remove($node);
        }

        foreach ($attendees as $operation => $attendees) {
            switch ($operation) {
                case DavConstants::PARTICIPIANT_ADDED:
                    $this->addParticipants($attendees, $parent, 'ORGANIZER');
                    break;
                case DavConstants::PARTICIPIANT_MODIFIED:
                    $this->modifyParticipants($attendees, $parent, 'ORGANIZER');
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    /**
     * Get participants of event
     * @see Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::prepareForSugar
     * @return array[]
     */
    public function getParticipants()
    {
        $event = $this->getVCalendarEvent();
        $component = $this->getComponent($event);
        $result = array();
        if ($component && $component->ATTENDEE) {
            $result = $this->participantsHelper->prepareForSugar($this, $component->ATTENDEE);
        }

        return $result;
    }

    /**
     * Set participants of event
     * @param \SugarBean $bean
     * @param Sabre\VObject\Component $parent
     * @return bool
     */
    public function setParticipants(\SugarBean $bean, SabreComponent $parent)
    {
        $attendees = $this->participantsHelper->prepareForDav($bean, $this, 'ATTENDEE');

        if (!$attendees) {
            return false;
        }

        foreach ($attendees as $operation => $attendees) {
            switch ($operation) {
                case DavConstants::PARTICIPIANT_ADDED:
                    $this->addParticipants($attendees, $parent, 'ATTENDEE');
                    break;
                case DavConstants::PARTICIPIANT_DELETED:
                    $this->deleteParticipants($attendees, $parent, 'ATTENDEE');
                    break;
                case DavConstants::PARTICIPIANT_MODIFIED:
                    $this->modifyParticipants($attendees, $parent, 'ATTENDEE');
                    break;
                default:
                    return false;
            }
        }

        return true;
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
                        $attendees = $this->participantsHelper->prepareForSugar($this, $alarm->ATTENDEE);
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
     * @param int $value     Duration in seconds
     * @param Sabre\VObject\Component $parent
     * @param string $action :
     *                       DISPLAY - popup window
     *                       EMAIL - email message
     * @return bool
     */
    public function setReminder($value, SabreComponent $parent, $action = 'DISPLAY')
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
     * @see Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::getRecurringInfo for array format
     * @return null | array
     */
    public function getRRule()
    {
        return $this->recurringHelper->getRecurringInfo($this);
    }

    /**
     * Set recurring rules of event
     * @param array $recuringInfo
     * @see Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::setRecurringInfo for array format
     * @return bool
     *
     */
    public function setRRule(array $recuringInfo)
    {
        return $this->recurringHelper->setRecurringInfo($this, $recuringInfo);
    }

    /**
     * Get status of event in SugarCRM format
     * @see $statusMap for avaliable statuses
     * @return null|string
     */
    public function getStatus()
    {
        $status = $this->getVObjectStringProperty('STATUS');
        $statusMap = $this->statusMapper->getMapping($this);
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
     * @param Sabre\VObject\Component $parent Parent component from CalDavEvent::setComponent
     * @return bool
     */
    public function setStatus($value, SabreComponent $parent)
    {
        $statusMap = array_flip($this->statusMapper->getMapping($this));

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
     * @return Sabre\VObject\Component
     *
     * @throws \InvalidArgumentException
     */
    public function setComponent($componentType)
    {
        $event = $this->getVCalendarEvent();
        $currentComponent = $this->getComponent($event);
        if ($currentComponent && $currentComponent->name == $componentType) {
            return $currentComponent;
        } else {
            $component = $event->createComponent($componentType);
            if (empty($component->UID)) {
                $uid = $event->createProperty('UID', create_guid());
                $component->add($uid);
            }
            return $event->add($component);
        }
    }

    /**
     * Handler for the 'schedule' event.
     * This method should be called from adapter if any participant was changed.
     * This method should be called before saving caldav event
     *
     * This handler attempts to look at local accounts to deliver the
     * scheduling object from sugar to caldav.
     * @return void
     */
    public function scheduleLocalDelivery()
    {
        if ($this->inMailGeneration) {
            return;
        }

        $currentUser = $this->getCurrentUser();
        $server = $this->serverHelper->setUp();

        if (!$server || !$currentUser) {
            return;
        }

        $calendarUri = DavConstants::DEFAULT_CALENDAR_URI;

        $schedulePlugin = $server->getPlugin('caldav-schedule');
        $caldavPlugin = $server->getPlugin('caldav');

        $calendarPath =
            $caldavPlugin::CALENDAR_ROOT . '/users/' . $currentUser->user_name . '/' . $calendarUri;

        $schedulePlugin->calendarObjectSugarChange($this->getVCalendarEvent(), $calendarPath, $this->calendardata);
    }

    /**
     * Returns related bean
     * @return null|SugarBean
     */
    public function getBean()
    {
        if ($this->parent_type) {
            return BeanFactory::getBean($this->parent_type, $this->parent_id, array('use_cache' => false));
        }

        return null;
    }

    /**
     * Set related bean
     * Return false if any errors occurred
     * @param SugarBean $bean
     * @return bool
     */
    public function setBean(\SugarBean $bean)
    {
        $id = $bean->repeat_parent_id ?: $bean->id;
        if ($id) {
            $this->parent_type = $bean->module_name;
            $this->parent_id = $id;
            return true;
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
        $id = $bean->repeat_parent_id ?: $bean->id;
        $query = new \SugarQuery();
        $query->from($this);
        $query->where()->equals('parent_type', $bean->module_name);
        $query->where()->equals('parent_id', $id);
        $query->limit(1);
        $result = $this->fetchFromQuery($query);
        if ($result) {
            return array_shift($result);
        }

        return null;
    }

    /**
     * Create text representation of event for email
     * @param SugarBean $bean
     * @return string
     */
    public function prepareForInvite(\SugarBean $bean)
    {
        $this->inMailGeneration = true;
        $adapterFactory = $this->getAdapterFactory();
        $adapter = $adapterFactory->getAdapter($bean->module_name);

        $result = '';

        if ($adapter) {
            $this->calendarid = \create_guid();
            $this->setBean($bean);

            if ($adapter->export($bean, $this)) {
                $vCalendarEvent = $this->getVCalendarEvent();
                $method = $vCalendarEvent->createProperty('METHOD', 'REQUEST');
                $vCalendarEvent->add($method);
                $result = $this->getVCalendarEvent()->serialize();
            }
        }
        $this->inMailGeneration = false;

        return $result;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }

    /**
     * Gets synchronization object for operation with counters
     * @return null|CalDavSynchronization
     */
    public function getSynchronizationObject()
    {
        $this->load_relationship('synchronization');

        BeanFactory::clearCache();
        $this->synchronization->resetLoaded();
        $result = $this->synchronization->getBeans();
        if ($result) {
            return array_shift($result);
        } else {
            if (!$this->id) {
                $this->new_with_id = true;
                $this->id = create_guid();
            }

            $syncBean = BeanFactory::getBean('CalDavSynchronizations');
            $syncBean->event_id = $this->id;
            $syncBean->save();

            return $syncBean;
        }
    }
}
