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
use Sugarcrm\Sugarcrm\Dav\Cal\Structures;
use Sabre\VObject\Recur\EventIterator;
use Sugarcrm\Sugarcrm\Dav\Base\Principal;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as CalDavHook;

/**
 * Class CalDavEventCollection
 * Represents implementation of Sugar Bean for CalDAV backend operations with calendar events
 */
class CalDavEventCollection extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavEvents';
    public $object_name = 'CalDavEventCollection';
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
    public $calendar_data = '';

    /**
     * Calendar URI
     * @var string
     */
    public $uri;

    /**
     * Calendar ID for event
     * @var string
     */
    public $calendar_id;

    /**
     * Event ETag. MD5 hash from $calendar_data
     * @var string
     */
    public $etag;

    /**
     * $calendar_data size in bytes
     * @var integer
     */
    public $data_size;

    /**
     * Event component type
     * @var string
     */
    public $component_type;

    /**
     * Recurring event first occurrence
     * @var string
     */
    public $first_occurence;

    /**
     * Recurring event last occurrence
     * @var string
     */
    public $last_occurence;

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
     * Json with participants link Dav to Sugar
     * @var string
     */
    public $participants_links;

    /**
     * Json with bean children ids
     * @var string
     */
    public $children_order_ids;

    /**
     * Make local delivery for participants or not
     * @var bool
     */
    public $doLocalDelivery = true;

    /**
     * Array of links email => [beanName, beanId]
     * @var string
     */
    protected $participantLinks = array();

    /**
     * Calendar event is stored here
     * @var Sabre\VObject\Component\VCalendar
     */
    protected $vCalendar = null;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\ServerHelper
     */
    protected $serverHelper;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event
     */
    protected $parentEvent = null;

    /**
     * Array of Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event[]
     */
    protected $childEvents = array();

    /**
     * Recurring rule
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule
     */
    protected $rRule = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
     */
    protected $dateTimeHelper;

    public function __construct()
    {
        $this->serverHelper = new DavHelper\ServerHelper();
        $this->dateTimeHelper = new DavHelper\DateTimeHelper();
        parent::__construct();
    }

    /**
     * Retrieve VCalendar
     * @return Sabre\VObject\Component\VCalendar
     */
    protected function getVCalendar()
    {
        if ($this->vCalendar) {
            return $this->vCalendar;
        }
        if (!$this->calendar_data) {
            $this->vCalendar = new SabreComponent\VCalendar();
            $timezone = $this->vCalendar->createComponent('VTIMEZONE');
            $currentTimezone = $this->getCurrentUser()->getPreference('timezone');
            if (!$currentTimezone) {
                $currentTimezone = date_default_timezone_get();
            }
            $timezone->TZID = $currentTimezone;
            $this->vCalendar->add($timezone);

            $event = $this->vCalendar->createComponent('VEVENT');
            if (!empty($event->UID)) {
                $event->UID->setValue(create_guid());
            } else {
                $uid = $event->createProperty('UID', create_guid());
                $event->add($uid);
            }
            $this->vCalendar->add($event);
        } else {
            $this->vCalendar = VObject\Reader::read($this->calendar_data);
        }

        return $this->vCalendar;
    }

    /**
     * Get class name for event
     * @return string
     */
    protected function getEventClass()
    {
        return \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Event');
    }

    /**
     * Get all recurring children
     * @return Structures\Event[]
     */
    protected function getAllChildren()
    {
        if ($this->childEvents) {
            return $this->childEvents;
        }

        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $eventClass = $this->getEventClass();
        $vCalendar = $this->getVCalendar();
        $parent = $vCalendar->getBaseComponent();

        if ($parent->DTSTART) {
            $it = new EventIterator($vCalendar, $parent->UID);
            $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;
            $endDate = clone $parent->DTSTART->getDateTime();
            $endDate->modify('+' . $maxRecur . ' day');
            $end = $it->getDtEnd();
            while ($it->valid() && $end < $endDate) {
                $state = $eventClass::STATE_VIRTUAL;
                $child = $it->getEventObject();
                if (!$child->{'RECURRENCE-ID'}) {
                    $recurrenceNode = clone $child->DTSTART;
                    $recurrenceNode->name = 'RECURRENCE-ID';
                    $child->add($recurrenceNode);
                }
                if ($child) {

                    if ($child == $it->currentOverriddenEvent) {
                        $state = $eventClass::STATE_CUSTOM;
                    }
                    $event = new $eventClass($child, $state, $this->getParticipantsLinks());
                    $this->childEvents[$event->getRecurrenceID()->getTimestamp()] = $event;
                }
                $end = $it->getDtEnd();
                $it->next();
            }
        }

        $deletedRecurring = $this->getDeleted();

        foreach ($deletedRecurring as $recurrenceID) {
            $event = new $eventClass(null, $eventClass::STATE_DELETED);
            $event->setRecurrenceID($recurrenceID);
            $this->childEvents[$recurrenceID->getTimestamp()] = $event;
        }

        ksort($this->childEvents);

        return $this->childEvents;
    }

    /**
     * Get sugar bean children ids
     * @return array
     */
    public function getSugarChildrenOrder()
    {
        if ($this->children_order_ids) {
            return json_decode($this->children_order_ids, true);
        }

        return array();
    }

    /**
     * Set sugar bean children ids
     * @param array $ids Array with bean ids
     * @return bool
     */
    public function setSugarChildrenOrder(array $ids)
    {
        $valid = array_filter($ids, function ($id) {
            return \is_guid($id);
        });
        if ($valid == $ids) {
            $this->children_order_ids = json_encode($ids);

            return true;
        }

        return false;
    }

    /**
     * Get all recurring children id
     * @return \SugarDateTime[]
     */
    public function getAllChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
            $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
        }

        return $result;
    }

    /**
     * Get deleted recurring children id
     * @return \SugarDateTime[]
     */
    public function getDeletedChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            if ($child->isDeleted()) {
                $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
                $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
            }
        }

        return $result;
    }

    /**
     * Get recurring children id that was customized
     * @return \SugarDateTime[]
     */
    public function getCustomizedChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            if ($child->isCustomized()) {
                $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
                $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
            }
        }

        return $result;
    }

    /**
     * Get parent event
     * @return Structures\Event
     */
    public function getParent()
    {
        if ($this->parentEvent) {
            return $this->parentEvent;
        }

        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $vCalendar = $this->getVCalendar();
        $parent = $vCalendar->getBaseComponent();
        $eventClass = $this->getEventClass();
        $this->parentEvent = new $eventClass($parent, $eventClass::STATE_PARENT, $this->getParticipantsLinks());

        return $this->parentEvent;
    }

    /**
     * Get deleted recurring children
     * @return \SugarDateTime[]
     */
    protected function getDeleted()
    {
        $result = array();
        $event = $this->getParent()->getObject();
        if ($event->EXDATE) {
            foreach ($event->EXDATE as $deleted) {
                $deletedDate = $this->dateTimeHelper->davDateToSugar($deleted);
                $result[$deletedDate->getTimestamp()] = $deletedDate;
            }
        }

        return $result;
    }

    /**
     * Remove child from EXDATE
     * @param \SugarDateTime $recurrenceId
     * @return array
     */
    protected function removeFromDeleted(\SugarDateTime $recurrenceId)
    {
        $event = $this->getParent()->getObject();

        if ($event->EXDATE) {
            foreach ($event->EXDATE as $deleted) {
                if ($recurrenceId == $deleted->getDateTime()) {
                    $event->remove($deleted);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get recurring event rules
     * @return Structures\RRule
     */
    public function getRRule()
    {
        if ($this->rRule) {
            return $this->rRule;
        }

        $event = $this->getParent()->getObject();

        if ($event->RRULE) {
            $recurringClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\RRule');
            $this->rRule = new $recurringClass($event->RRULE);
        }

        return $this->rRule;
    }

    /**
     * Set recurring rules of event
     * @param Structures\RRule $rRule
     * @return bool
     */
    public function setRRule(Structures\RRule $rRule = null)
    {
        $event = $this->getParent()->getObject();

        if (!$rRule) {
            $event->remove('RRULE');
            $this->childEvents = array();
            $this->rRule = null;

            return true;
        }

        $currentRule = $this->getRRule();

        if (!$currentRule) {
            $event->add($rRule->getObject());
            $this->rRule = $rRule;
            $this->childEvents = array();

            return true;
        }

        $needChange = $currentRule->getUntil() != $rRule->getUntil() ||
            $currentRule->getFrequency() != $rRule->getFrequency() ||
            $currentRule->getInterval() != $rRule->getInterval() ||
            $currentRule->getCount() != $rRule->getCount() ||
            $currentRule->getByDay() != $rRule->getByDay() ||
            $currentRule->getByMonthDay() != $rRule->getByMonthDay() ||
            $currentRule->getByYearDay() != $rRule->getByYearDay() ||
            $currentRule->getByWeekNo() != $rRule->getByWeekNo() ||
            $currentRule->getByMonth() != $rRule->getByMonth() ||
            $currentRule->getBySetPos() != $rRule->getBySetPos();

        if ($needChange) {
            $currentRule->getObject()->setParts($rRule->getObject()->getParts());
            $this->childEvents = array();

            return true;
        }

        return false;
    }

    /**
     * Delete all custom children from collection
     */
    protected function deleteCustomChildren()
    {
        $customChildren = $this->getCustomizedChildrenRecurrenceIds();
        foreach ($customChildren as $recurrenceId) {
            $child = $this->getChild($recurrenceId);
            if ($child) {
                $vCalendar = $this->getVCalendar();
                $vCalendar->remove($child->getObject());
            }
        }
    }

    /**
     * Cleaning all custom and deleted nodes from event
     */
    public function resetChildrenChanges()
    {
        $this->deleteCustomChildren();
        $event = $this->getParent()->getObject();
        if ($event->EXDATE) {
            foreach ($event->EXDATE as $deleted) {
                $event->remove($deleted);
            }
        }

        $this->childEvents = array();
    }

    /**
     * Get recurring children by RECURRENCE-ID
     * @param SugarDateTime $recurrenceId
     * @param bool $restoreDeleted Restore deleted child or not
     * @return Structures\Event | null
     */
    public function getChild(\SugarDateTime $recurrenceId, $restoreDeleted = false)
    {
        $children = $this->getAllChildren();
        $recurringTimeStamp = $recurrenceId->getTimestamp();
        $child = isset($children[$recurringTimeStamp]) ? $children[$recurringTimeStamp] : null;

        if ($child) {

            if (!$child->isDeleted()) {
                return $child;
            }

            if ($child->isDeleted() && $restoreDeleted) {
                return $this->addChild($recurrenceId, $restoreDeleted);
            }
        }

        return null;
    }

    /**
     * Add new child a custom recurring item
     * If  recurrence-id was deleted we can not add new child with this recurring id
     * @param SugarDateTime $recurrenceId
     * @param bool $restoreDeleted Restore deleted child or not
     * @return null | Structures\Event
     */
    protected function addChild(\SugarDateTime $recurrenceId, $restoreDeleted = false)
    {
        $vCalendar = $this->getVCalendar();
        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $eventClass = $this->getEventClass();

        $event = $vCalendar->createComponent('VEVENT');
        if (!empty($event->UID)) {
            $event->UID->setValue($this->getParent()->getUID());
        } else {
            $uid = $event->createProperty($this->getParent()->getUID());
            $event->add($uid);
        }
        $vCalendar->add($event);

        $child = new $eventClass($event, $eventClass::STATE_CUSTOM);
        $child->setRecurrenceID($recurrenceId);

        $recurringTimeStamp = $recurrenceId->getTimestamp();
        $this->childEvents[$recurringTimeStamp] = $child;

        if ($restoreDeleted) {
            $this->removeFromDeleted($recurrenceId);
        }

        return $child;
    }

    /**
     * Deletes event from series.
     *
     * @param SugarDateTime $recurrenceId
     * @return bool
     */
    public function deleteChild(\SugarDateTime $recurrenceId)
    {
        $children = $this->getAllChildren();
        $recurrenceTimeStamp = $recurrenceId->getTimestamp();
        $child = isset($children[$recurrenceTimeStamp]) ? $children[$recurrenceTimeStamp] : null;
        if (!$child) {
            return false;
        }
        if ($child->isDeleted()) {
            return false;
        }

        $object = $this->getParent()->getObject();
        $property = $object->parent->createProperty('EXDATE');
        $property->setDateTime($recurrenceId);
        $object->add($property);
        $this->childEvents = array();

        return true;
    }

    /**
     * Calculate and set the size of the event data in bytes
     */
    protected function calculateSize()
    {
        $this->data_size = strlen($this->calendar_data);
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
        $component = $vObject->getBaseComponent();
        if ($component) {
            $this->component_type = $component->name;
            if ($component->UID) {
                $this->event_uid = $component->UID->getValue();
            }

            return true;
        }

        return false;
    }

    /**
     * Calculate firstoccurence and lastoccurence of event
     * @param string $data Calendar event text data
     */
    protected function calculateTimeBoundaries()
    {
        if (!$this->calendar_data) {
            return;
        }
        $vObject = VObject\Reader::read($this->calendar_data);
        $component = $vObject->getBaseComponent();
        if ($component->name === 'VEVENT' && $component->DTSTART) {
            $this->first_occurence = $component->DTSTART->getDateTime()->getTimestamp();

            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $this->last_occurence = $component->DTEND->getDateTime()->getTimestamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $this->last_occurence = $endDate->getTimestamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $this->last_occurence = $endDate->getTimestamp();
                } else {
                    $this->last_occurence = $this->first_occurence;
                }
            } else {
                $it = new VObject\Recur\EventIterator($vObject, $component->UID);
                $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;

                $endDate = clone $component->DTSTART->getDateTime();
                $endDate->modify('+' . $maxRecur . ' day');
                if ($it->isInfinite()) {
                    $this->last_occurence = $endDate->getTimestamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $endDate) {
                        $end = $it->getDtEnd();
                        $it->next();
                    }
                    $this->last_occurence = $end->getTimestamp();
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
        $calendar = $this->getRelatedCalendar();
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
     * Parse text calendar event data to database fields
     * @param string $data Calendar event text data
     * @return bool True - if all data are correct and were set, false in otherwise
     */
    public function setData($data)
    {
        if (empty($data)) {
            return false;
        }

        if (!$this->calculateComponentType($data)) {
            return false;
        }

        $this->calendar_data = $data;

        $this->vCalendar = null;
        $this->parentEvent = null;
        $this->childEvents = array();
        $this->rRule = null;

        return true;
    }

    /**
     * Set calendar id
     * @param string $calendarId
     */
    public function setCalendarId($calendarId)
    {
        $this->calendar_id = $calendarId;
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
            'calendarid' => $this->calendar_id,
            'size' => $this->data_size,
            'calendardata' => $this->calendar_data,
            'component' => strtolower($this->component_type),
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
     * Get scheduling bean
     * @return \CalDavScheduling
     */
    protected function getSchedulingBean()
    {
        return \BeanFactory::getBean('CalDavSchedulings');
    }

    /**
     * Retrieve Calendar for event
     * @return null|SugarBean
     */
    protected function getRelatedCalendar()
    {
        if ($this->load_relationship('events_calendar')) {
            return array_shift($this->events_calendar->getBeans());
        }

        return null;
    }

    /**
     * Set the organizer's calendar as the related one, if organizer is present.
     */
    protected function setRelatedCalendar()
    {
        $calendar = $this->getRelatedCalendar();
        if (!$calendar && $GLOBALS['current_user'] instanceof User) {
            $userHelper = new UserHelper();
            $calendar = array_shift($userHelper->getCalendars($GLOBALS['current_user']->user_name));
            $this->setCalendarId($calendar['id']);
            if (isset($this->events_calendar)) {
                $this->events_calendar->resetLoaded();
            }
        }
    }

    /**
     * Find scheduling object by uri and set parent_type of event if object found
     * @return bool
     */
    protected function setCalDavParent()
    {
        if (!$this->uri) {
            return false;
        }

        $calendar = $this->getRelatedCalendar();
        if ($calendar) {
            $scheduling = $this->getSchedulingBean()->getByUri($this->uri, $calendar->assigned_user_id);
            if ($scheduling) {
                $this->parent_type = $this->module_name;
                return true;
            }
        }

        return false;
    }

    /**
     * Event can be imported to sugar module or not
     * @return bool
     */
    public function isImportable()
    {
        return $this->parent_type !== $this->module_name && $this->calendar_data;
    }

    /**
     * Retrieve set of events by calendarID and URI
     * @param string $calendarId
     * @param array $uri
     * @param int $limit
     * @return CalDavEventCollection[]
     * @throws SugarQueryException
     */
    public function getByURI($calendarId, array $uri, $limit = 0)
    {
        $result = array();
        if ($this->load_relationship('events_calendar')) {
            $query = new \SugarQuery();
            $query->from($this);
            $query->where()->equals('calendar_id', $calendarId);
            $query->where()->in('caldav_events.uri', $uri);
            $query->limit($limit);
            $result = $this->fetchFromQuery($query);
        }

        return $result;
    }

    /**
     * Get search manager
     * @return \Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager
     */
    protected function getPrincipalManager()
    {
        return new Principal\Manager();
    }

    /**
     * Returns mapping of emails to sugar's persons.
     * In case if it's first call then mapping will be received from participants_links property.
     *
     * @var bool $force
     * @return array
     */
    protected function getParticipantsLinks($force = false)
    {
        if ($force || (!$this->participantLinks && $this->participants_links)) {
            $this->participantLinks = array();
            $links = json_decode($this->participants_links, true);
            if (is_array($links)) {
                $this->participantLinks = $links;
            } else {
                $this->participantLinks = array();
            }
        }
        return $this->participantLinks;
    }

    /**
     * Links all dav participants to sugar beans and return array with links
     * @return array
     */
    protected function mapParticipantsToBeans()
    {
        $participantsList = $this->getParent()->getParticipants();
        $customChildrenId = $this->getCustomizedChildrenRecurrenceIds();
        foreach ($customChildrenId as $recurrenceId) {
            $participantsList = array_merge($participantsList, $this->getChild($recurrenceId)->getParticipants());
        }

        $this->getParticipantsLinks(true);
        foreach ($participantsList as $participant) {
            $email = $participant->getEmail();
            if (!isset($this->participantLinks[$email])) {
                if ($participant->getBeanName() && $participant->getBeanId()) {
                    $link = array('beanName' => $participant->getBeanName(), 'beanId' => $participant->getBeanId());
                } else {
                    $link = $this->getPrincipalManager()
                                 ->setOutputFormat(new Principal\Search\Format\ArrayStrategy())
                                 ->findSugarLinkByEmail($email);
                }

                global $locale;

                if (!$link) {
                    /** @var Addressee $focus */
                    $focus = \BeanFactory::getBean('Addressees');
                    $focus->last_name = $email;
                    $focus->email1 = $email;
                    $focus->save();

                    $link = array(
                        'beanName' => $focus->module_name,
                        'beanId' => $focus->id,
                    );
                }

                if ($link['beanName'] == 'Addressees') {
                    /** @var Addressee $focus */
                    $focus = \BeanFactory::getBean('Addressees', $link['beanId']);
                    if ($focus->last_name === $email) {
                        $parseName = $locale->getLocaleUnFormattedName($participant->getDisplayName());
                        $parseName = array_filter($parseName);
                        if ($parseName) {
                            $focus->first_name = isset($parseName['f']) ? $parseName['f'] : '';
                            $focus->last_name = isset($parseName['l']) ? $parseName['l'] : '';
                            $focus->salutation = isset($parseName['s']) ? $parseName['s'] : '';

                            $focus->save();
                        }
                    }
                }

                $participant->setBeanName($link['beanName']);
                $participant->setBeanId($link['beanId']);

                $this->participantLinks[$email] = $link;
            }
        }

        return $this->participantLinks;
    }

    /**
     * Populate bean fields according calendar_data or vCalendar
     */
    public function sync()
    {
        $isUpdate = $this->isUpdate();
        if ($this->vCalendar) {
            $this->calendar_data = $this->getVCalendar()->serialize();
            $this->calculateComponentType($this->calendar_data);
        }

        $this->calculateSize();

        if ($this->getParent()->getStartDate()) {
            $this->etag = md5($this->calendar_data);
            $this->calculateTimeBoundaries();
        } else {
            $this->etag = '';
        }

        if (empty($this->uri) && !empty($this->event_uid)) {
            $this->uri = $this->event_uid . '.ics';
        }

        $this->participants_links = json_encode($this->mapParticipantsToBeans());

        if (!$isUpdate) {
            $this->setCalDavParent();
        }

    }

    /**
     * @inheritdoc
     */
    public function save($check_notify = false)
    {
        $isUpdate = $this->isUpdate();
        $currentETag = isset($this->fetched_row['etag']) ? $this->fetched_row['etag'] : null;

        $this->setRelatedCalendar();
        if (!$this->parent_type) {
            $this->parent_type = $GLOBALS['current_user']->getPreference('caldav_module');
        }

        $this->sync();
        if ($this->isImportable() && $this->doLocalDelivery && $this->scheduleLocalDelivery()) {
            $this->sync();
        }

        $originalCalendarData = '';
        if (isset($this->fetched_row['calendar_data'])) {
            $originalCalendarData = $this->fetched_row['calendar_data'];
        }

        $result = parent::save($check_notify);

        if ($result && $currentETag != $this->etag) {
            $operation = $isUpdate ? DavConstants::OPERATION_MODIFY : DavConstants::OPERATION_ADD;
            $this->addChange($operation);
        }

        $this->getCalDavHook()->import($this, array('update', $originalCalendarData));

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function mark_deleted($id)
    {
        if (!$id) {
            return null;
        }
        if ($this->id != $id) {
            BeanFactory::getBean($this->module_name, $id)->mark_deleted($id);
            return null;
        }
        if (!$this->deleted) {
            $this->addChange(DavConstants::OPERATION_DELETE);
        }
        $deletedStatus = $this->deleted;
        parent::mark_deleted($id);
        if (!$deletedStatus && $this->deleted) {
            $this->getCalDavHook()->import($this, array('delete'));
        }
    }

    /**
     * @inheritdoc
     */
    public function mark_undeleted($id)
    {
        if (!$id) {
            return null;
        }
        if ($this->id != $id) {
            BeanFactory::getBean($this->module_name, $id)->mark_undeleted($id);
            return null;
        }
        if ($this->deleted) {
            $this->addChange(DavConstants::OPERATION_ADD);
        }
        $deletedStatus = $this->deleted;
        parent::mark_undeleted($id);
        if ($deletedStatus && !$this->deleted) {
            $this->getCalDavHook()->import($this);
        }
    }

    /**
     * Get Event VTIMEZONE section and return timezone in string representation
     * @return string
     */
    public function getTimeZone()
    {
        $event = $this->getVCalendar();
        if ($event->VTIMEZONE) {
            return $event->VTIMEZONE->TZID->getValue();
        }

        return 'UTC';
    }

    /**
     * Handler for the 'schedule' event.
     * This method should be called before saving caldav event
     *
     * This handler attempts to look at local accounts to deliver the
     * scheduling object from sugar to caldav.
     *
     * @return bool is event object was changed or not
     */
    protected function scheduleLocalDelivery()
    {
        if ($this->etag === '') {
            return false;
        }

        $schedulingUser = $this->getCurrentUser();

        if ($this->created_by && $this->created_by != $schedulingUser->id) {
            $schedulingUser = \BeanFactory::getBean('Users', $this->created_by);
        }
        $server = $this->serverHelper->setUp();

        if (!$server || !$schedulingUser) {
            return false;
        }

        $schedulePlugin = $server->getPlugin('caldav-schedule');
        $oldCalendarData = isset($this->fetched_row['calendar_data']) ? $this->fetched_row['calendar_data'] : null;

        return $schedulePlugin->calendarObjectSugarChange(
            $this->getVCalendar(),
            $oldCalendarData,
            $schedulingUser->user_name
        );
    }

    /**
     * Returns related bean
     * @return null|SugarBean
     */
    public function getBean()
    {
        if ($this->parent_type && $this->parent_id) {
            return BeanFactory::getBean($this->parent_type, $this->parent_id, array(
                'strict_retrieve' => true,
                'deleted' => false,
            ));
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
        if ($bean->id) {
            return $this->setParentModuleAndId($bean->module_name, $bean->id);
        }

        return false;
    }

    /**
     * Set related bean by module name and id
     * @param $beanModule
     * @param $beanId
     * @return bool
     */
    public function setParentModuleAndId($beanModule, $beanId)
    {
        $this->parent_type = $beanModule;
        $this->parent_id = $beanId;

        return true;
    }

    /**
     * Retrieve CalDavEventCollection by parent bean.
     *
     * @param SugarBean $bean
     * @return CalDavEventCollection|null
     */
    public function findByBean(\SugarBean $bean)
    {
        return $this->findByParentModuleAndId($bean->module_name, $bean->id);
    }

    /**
     * Retrieve CalDavEventCollection by parent module name and id.

     * @param string $beanModule
     * @param string $beanId
     * @return CalDavEventCollection|null
     */
    public function findByParentModuleAndId($beanModule, $beanId)
    {
        $query = new \SugarQuery();
        $query->from($this, array(
            'add_deleted' => false,
        ));
        $query->where()->equals('parent_type', $beanModule);
        $query->where()->equals('parent_id', $beanId);
        $query->limit(1);
        $result = $this->fetchFromQuery($query);
        if ($result) {
            return array_shift($result);
        }

        return null;
    }

    /**
     * Check if invite was canceled for user
     * @param SugarBean $bean
     * @param null $emailInvitee
     *
     * @return bool
     */
    public static function isInviteCanceled(SugarBean $bean, $emailInvitee = null)
    {
        $event = static::prepareForInvite($bean, $emailInvitee);
        if ($event) {
            $collection = new static();
            $collection->setData($event);
            $vCalendarEvent = $collection->getVCalendar();
            return $vCalendarEvent->METHOD == 'CANCEL';
        }
        return false;
    }

    /**
     * Create text representation of event for email
     * @param SugarBean $bean
     * @param string $inviteeEmail
     * @param string|null $organizerEmail
     * @return string
     */
    public static function prepareForInvite(SugarBean $bean, $inviteeEmail = null, $organizerEmail = null)
    {
        $collection = new static();
        $adapterFactory = $collection->getAdapterFactory();
        $adapter = $adapterFactory->getAdapter($bean->module_name);

        if ($adapter) {
            $exportDataSet = $adapter->prepareForExport($bean);
            if ($exportDataSet) {
                foreach ($exportDataSet as $exportData) {
                    $adapter->export($exportData, $collection);
                }
                $vCalendarEvent = $collection->getVCalendar();
                if (!empty($bean->send_invites_uid)) {
                    $vCalendarEvent->getBaseComponent()->UID->setValue($bean->send_invites_uid);
                }

                if ($bean->deleted) {
                    $vCalendarEvent->add($vCalendarEvent->createProperty('METHOD', 'CANCEL'));
                    return $vCalendarEvent->serialize();
                }

                /** @var Structures\Event $event */
                $event = $collection->getParent();
                $event->getObject()->add($vCalendarEvent->createProperty('X-SUGAR-ID', $bean->id));
                $event->getObject()->add($vCalendarEvent->createProperty('X-SUGAR-NAME', $bean->module_name));

                $organizer = $event->getOrganizer();
                if (!$organizer) {
                    $tempOrganizer = \BeanFactory::getBean('Users', $bean->created_by);
                    $email = $tempOrganizer->emailAddress->getPrimaryAddress($tempOrganizer);
                    $organizer = new Structures\Participant();
                    $organizer->setEmail($email);
                    $event->setOrganizer($organizer);
                }

                if ($inviteeEmail) {
                    $participants = $event->getParticipants();
                    $participants[] = $organizer;
                    $found = false;
                    foreach ($participants as $participant) {
                        if ($participant->getEmail() === $inviteeEmail) {
                            $participant->setRSVP('TRUE');
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        $vCalendarEvent->add($vCalendarEvent->createProperty('METHOD', 'REQUEST'));
                    } else {
                        $vCalendarEvent->add($vCalendarEvent->createProperty('METHOD', 'CANCEL'));
                    }
                }

                if ($organizerEmail) {
                    $organizer->setEmail($organizerEmail);
                }

                return $vCalendarEvent->serialize();
            }
        }

        return '';
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
        /** @var CalDavSynchronization $synchronizationObject */
        $synchronizationObject = BeanFactory::getBean('CalDavSynchronizations');
        $query = new SugarQuery();
        $query->from($synchronizationObject);
        $query->where()->equals('event_id', $this->id);
        $query->limit(1);
        $result = $synchronizationObject->fetchFromQuery($query);
        if ($result) {
            $result = array_shift($result);
            return $result;
        } else {
            if (!$this->id) {
                $this->new_with_id = true;
                $this->id = create_guid();
            }
            $synchronizationObject->event_id = $this->id;
            $synchronizationObject->save();
            return $synchronizationObject;
        }
    }

    /**
     * Get queue object for operation queue
     *
     * @return null|CalDavQueue
     */
    public function getQueueObject()
    {
        /** @var CalDavQueue $queueObject */
        $queueObject = BeanFactory::getBean('CalDavQueues');
        $queueObject->event_id = $this->id;
        return $queueObject;
    }

    /**
     * Create main fields diff
     * @param Structures\Event $currentEvent
     * @param Structures\Event|null $oldEvent
     * @return array
     */
    protected function getEventDiff(Structures\Event $currentEvent, Structures\Event $oldEvent = null)
    {
        $changedFields = array();
        if ($oldEvent) {
            if ($currentEvent->getTitle() != $oldEvent->getTitle()) {
                $changedFields['title'] = array($currentEvent->getTitle(), $oldEvent->getTitle());
            }
            if ($currentEvent->getDescription() != $oldEvent->getDescription()) {
                $changedFields['description'] = array($currentEvent->getDescription(), $oldEvent->getDescription());
            }
            if ($currentEvent->getLocation() != $oldEvent->getLocation()) {
                $changedFields['location'] = array($currentEvent->getLocation(), $oldEvent->getLocation());
            }
            if ($currentEvent->getStatus() != $oldEvent->getStatus()) {
                $changedFields['status'] = array($currentEvent->getStatus(), $oldEvent->getStatus());
            }
            if ($currentEvent->getStartDate() != $oldEvent->getStartDate()) {
                $changedFields['date_start'] =
                    array($currentEvent->getStartDate()->asDb(), $oldEvent->getStartDate()->asDb());
            }
            if ($currentEvent->getEndDate() != $oldEvent->getEndDate()) {
                $changedFields['date_end'] =
                    array($currentEvent->getEndDate()->asDb(), $oldEvent->getEndDate()->asDb());
            }
        } else {
            $changedFields['title'] = array($currentEvent->getTitle());
            $changedFields['description'] = array($currentEvent->getDescription());
            $changedFields['location'] = array($currentEvent->getLocation());
            $changedFields['status'] = array($currentEvent->getStatus());
            if ($currentEvent->getStartDate()) {
                $changedFields['date_start'] = array($currentEvent->getStartDate()->asDb());
            }
            if ($currentEvent->getEndDate()) {
                $changedFields['date_end'] = array($currentEvent->getEndDate()->asDb());
            }
        }
        return $changedFields;
    }

    /**
     * Create participants diff
     * @param Structures\Event $currentEvent
     * @param Structures\Event|null $oldEvent
     * @return array
     */
    protected function getParticipantsDiff(Structures\Event $currentEvent, Structures\Event $oldEvent = null)
    {
        $participantHelper = new ParticipantsHelper();

        $participantsBefore = array();
        if ($oldEvent) {
            foreach ($oldEvent->getParticipants() as $participant) {
                $participantsBefore[] = $participantHelper->participantToArray($participant);
            }
        }
        $participantsAfter = array();
        foreach ($currentEvent->getParticipants() as $participant) {
            $participantsAfter[] = $participantHelper->participantToArray($participant);
        }
        return $participantHelper->getInviteesDiff($participantsBefore, $participantsAfter);
    }

    /**
     * Create RRULE diff
     * @param CalDavEventCollection $currentCollection
     * @param CalDavEventCollection|null $oldCollection
     * @return array
     */
    protected function getRRuleDiff(\CalDavEventCollection $currentCollection, \CalDavEventCollection $oldCollection = null)
    {
        $changedFields = array();
        $oldRRule = $oldCollection ? $oldCollection->getRRule() : null;
        $currentRRule = $currentCollection->getRRule();

        if ($oldRRule) {
            if (!$currentRRule) {
                $changedFields['action'] = 'deleted';
                $changedFields['frequency'] = array(null, $oldRRule->getFrequency());
                $changedFields['interval'] = array(null, $oldRRule->getInterval());
                $changedFields['count'] = array(null, $oldRRule->getCount());
                $oldUntil = $oldRRule->getUntil() ? $oldRRule->getUntil()->asDbDate() : null;
                $changedFields['until'] = array(null, $oldUntil);
                $changedFields['byday'] = array(array(), $oldRRule->getByDay());
                $changedFields['bymonthday'] = array(array(), $oldRRule->getByDay());
                $changedFields['bysetpos'] = array(array(), $oldRRule->getBySetPos());
                return $changedFields;
            }

            if ($oldRRule->getObject()->getParts() != $currentRRule->getObject()->getParts()) {
                $changedFields['action'] = 'updated';
                if ($oldRRule->getFrequency() != $currentRRule->getFrequency()) {
                    $changedFields['frequency'] = array($currentRRule->getFrequency(), $oldRRule->getFrequency());
                }

                if ($oldRRule->getInterval() != $currentRRule->getInterval()) {
                    $changedFields['interval'] = array($currentRRule->getInterval(), $oldRRule->getInterval());
                }

                if ($oldRRule->getCount() != $currentRRule->getCount()) {
                    $changedFields['count'] = array($currentRRule->getCount(), $oldRRule->getCount());
                }

                if ($oldRRule->getUntil() != $currentRRule->getUntil()) {
                    $oldUntil = $oldRRule->getUntil() ? $oldRRule->getUntil()->asDbDate() : null;
                    $currentUntil = $currentRRule->getUntil() ? $currentRRule->getUntil()->asDbDate() : null;
                    $changedFields['until'] = array($currentUntil, $oldUntil);
                }

                if ($oldRRule->getByDay() != $currentRRule->getByDay()) {
                    $changedFields['byday'] = array($currentRRule->getByDay(), $oldRRule->getByDay());
                }

                if ($oldRRule->getByMonthDay() != $currentRRule->getByMonthDay()) {
                    $changedFields['bymonthday'] = array($currentRRule->getByMonthDay(), $oldRRule->getByMonthDay());
                }

                if ($oldRRule->getBySetPos() != $currentRRule->getBySetPos()) {
                    $changedFields['bysetpos'] = array($currentRRule->getBySetPos(), $oldRRule->getBySetPos());
                }
            }
        } elseif ($currentRRule) {
            $changedFields['action'] = 'added';
            $changedFields['frequency'] = array($currentRRule->getFrequency());
            $changedFields['interval'] = array($currentRRule->getInterval());
            $changedFields['count'] = array($currentRRule->getCount());
            $until = $currentRRule->getUntil();
            $changedFields['until'] = $until ? array($until->asDbDate()) : array(null);
            $changedFields['byday'] = array($currentRRule->getByDay());
            $changedFields['bymonthday'] = array($currentRRule->getByMonthDay());
            $changedFields['bysetpos'] = array($currentRRule->getBySetPos());
        }

        return $changedFields;
    }

    /**
     * Create event diff
     * @param string $data
     * @return mixed returns array with difference or false if data is the same as current object
     */
    public function getDiffStructure($data)
    {
        $this->childEvents = array();
        $this->parentEvent = null;
        $currentParent = $this->getParent();
        $result = array();
        if ($data) {
            /** @var CalDavEventCollection $oldCollection */
            $oldCollection = new static();
            $oldCollection->setData($data);
            $oldCollection->participants_links = $this->participants_links;
            $oldCollection->mapParticipantsToBeans();
            $oldParent = $oldCollection->getParent();
        } else {
            $oldParent = null;
            $oldCollection = null;
        }

        $rRuleParams = $this->getRRuleDiff($this, $oldCollection);

        $mainParentChanged = $updateAllChildren = false;
        if (isset($rRuleParams['action'])) {
            if ($oldParent) {
                $oldCustomizedParent = $oldCollection->getChild($oldParent->getStartDate());
                if ($oldCustomizedParent && $oldCustomizedParent->isCustomized()) {
                    $oldParent = $oldCustomizedParent;
                }
            }
            $updateAllChildren = true;
        }

        $changedFields = $this->getEventDiff($currentParent, $oldParent);
        $invites = $this->getParticipantsDiff($currentParent, $oldParent);
        if (isset($rRuleParams['action'])) {
            $changedFields['rrule'] = $rRuleParams;
        }

        $filter = true;
        if (empty($data)) {
            $filter = array_filter($changedFields, function ($set) {
                return !empty($set[0]);
            });
        }

        if ($filter && ($invites || $changedFields)) {
            if (!$updateAllChildren) {
                $mainParentChanged = true;
            }
            $result[] = array(
                array(
                    $data ? 'update' : 'override',
                    $this->id,
                    null,
                    null,
                    null,
                ),
                $changedFields,
                $invites,
            );
        }

        $childrenRecurrenceIds = array_values($this->getAllChildrenRecurrenceIds());
        foreach ($childrenRecurrenceIds as $recurrenceId) {

            $oldChild = $oldCollection && !$updateAllChildren ? $oldCollection->getChild($recurrenceId) : null;
            $currentChild = $this->getChild($recurrenceId);

            if (!$currentChild) {
                continue;
            }

            if ($currentParent->getStartDate() == $currentChild->getRecurrenceID()) {
                if (!$currentChild->isCustomized()) {
                    continue;
                } elseif ($mainParentChanged) {
                    continue;
                }
            }

            $changedFields = $this->getEventDiff($currentChild, $oldChild);
            $invites = $this->getParticipantsDiff($currentChild, $oldChild);

            $filter = true;
            if (!$oldChild) {
                $filter = array_filter($changedFields, function ($set) {
                    return $set[0];
                });
            }

            if ($filter && ($invites || $changedFields)) {
                $result[] = array(
                    array(
                        $oldChild ? 'update' : 'restore',
                        $this->id,
                        $this->getSugarChildrenOrder(),
                        $recurrenceId->asDb(),
                        array_search($recurrenceId, $childrenRecurrenceIds),
                    ),
                    $changedFields,
                    $invites,
                );
            }
        }
        // looking for events which should be deleted
        $deletedChildrenRecurrenceIds = $this->getDeletedChildrenRecurrenceIds();
        foreach ($deletedChildrenRecurrenceIds as $recurrenceId) {
            $oldChild = $oldCollection ? $oldCollection->getChild($recurrenceId) : null;
            if ($oldChild && !$oldChild->isDeleted()) {
                $result[] = array(
                    array(
                        'delete',
                        $this->id,
                        $this->getSugarChildrenOrder(),
                        $recurrenceId->asDb(),
                        array_search($recurrenceId, $childrenRecurrenceIds),
                    ),
                    array(),
                    array(),
                );
            }
        }

        if ($oldCollection) {
            $recurrenceIds = array_merge(
                $oldCollection->getCustomizedChildrenRecurrenceIds(), // looking for custom events which should become base
                $oldCollection->getDeletedChildrenRecurrenceIds() // looking for deleted events which should become base
            );
            foreach ($recurrenceIds as $recurrenceId) {
                $currentChild = $this->getChild($recurrenceId);
                if ($currentChild && !$currentChild->isCustomized()) {
                    $result[] = array(
                        array(
                            'restore',
                            $this->id,
                            $this->getSugarChildrenOrder(),
                            $recurrenceId->asDb(),
                            array_search($recurrenceId, $childrenRecurrenceIds),
                        ),
                        $this->getEventDiff($currentChild, null),
                        $this->getParticipantsDiff($currentChild, null),
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get CalDav Handler.
     * @return CalDavHook
     */
    public function getCalDavHook()
    {
        return new CalDavHook();
    }
}
