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
     * Maximum date count for INFINITE RECCURENCE
     */
    CONST MAX_INFINITE_RECCURENCE_COUNT = 1000;

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
    public $related_module;

    /**
     * Related module id
     * @var string
     */
    public $related_module_id;

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
                $maxRecur = self::MAX_INFINITE_RECCURENCE_COUNT;

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
     * @param string $calendarID
     */
    public function setCalendarId($calendarID)
    {
        $this->calendarid = $calendarID;
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

    public function getRelatedCalendar($calendarID)
    {
        if ($this->load_relationship('events_calendar')) {
            return \BeanFactory::getBean($this->events_calendar->getRelatedModuleName(), $calendarID);
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
            if($result && $fetchOne) {
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
}
