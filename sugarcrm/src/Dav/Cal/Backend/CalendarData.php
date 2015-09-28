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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Backend;

use Sabre\DAV;
use Sabre\CalDAV;

use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\VObject;
use Sabre\CalDAV\Backend\SchedulingSupport;
use Sugarcrm\Sugarcrm\Dav\Base\Helper;

class CalendarData extends AbstractBackend implements SchedulingSupport
{
    /**
     * Instance of UserHelper
     * @var Helper\UserHelper
     */
    protected static $userHelperInstance = null;

    /**
     * Mapping CalDav fields to CalDavCalendar bean fields
     * @var array
     */
    public $propertyMap = array(
        '{DAV:}displayname' => 'name',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
    );

    /**
     * Get SugarQuery Instance
     * @return \SugarQuery
     */
    public function getSugarQuery()
    {
        return new \SugarQuery();
    }

    /**
     * Get UserHelper
     * @return Helper\UserHelper
     */
    public function getUserHelper()
    {
        if (is_null(self::$userHelperInstance)) {
            self::$userHelperInstance = new Helper\UserHelper();
        }

        return self::$userHelperInstance;
    }

    /**
     * Get recurring helper
     * @return Helper\RecurringHelper
     */
    public function getRecurringHelper()
    {
        return new Helper\RecurringHelper();
    }

    protected function isUnsupported($calendarData)
    {
        $recurringHelper = $this->getRecurringHelper();
        $vObject = VObject\Reader::read($calendarData);
        $mainComponent = $vObject->getBaseComponent();
        if ($mainComponent->RRULE) {
            return $recurringHelper->isUnsupported($mainComponent->RRULE->getParts());
        }

        return false;
    }

    /**
     * Get CalDavCalendar bean object
     * @param string $calendarID
     * @return null|\CalDavCalendar
     */
    public function getCalendarBean($calendarID = null)
    {
        return \BeanFactory::getBean('CalDavCalendars', $calendarID);
    }

    /**
     * Get CalDavEvent bean object
     *
     * @return null|\CalDavEvent
     */
    public function getEventsBean()
    {
        return \BeanFactory::getBean('CalDavEvents');
    }

    /**
     * Get CalDavScheduling bean object
     * @return null|\SugarBean
     */
    public function getSchedulingBean()
    {
        return \BeanFactory::getBean('CalDavSchedulings');
    }

    /**
     * @inheritdoc
     */
    public function getCalendarsForUser($principalUri)
    {
        $result = array();
        $userHelper = $this->getUserHelper();
        $calendars = $userHelper->getCalendars($principalUri);
        if ($calendars) {
            foreach ($calendars as $calendar) {
                $result[] = $calendar->toCalDavArray($this->propertyMap, $userHelper);
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @throws DAV\Exception\Forbidden
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        throw new DAV\Exception\Forbidden('createCalendar is not allowed for calendar');
    }

    /**
     * @inheritdoc
     */
    public function updateCalendar($calendarId, DAV\PropPatch $propPatch)
    {
        $supportedProperties = array_keys($this->propertyMap);
        $supportedProperties[] = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';

        $propPatch->handle($supportedProperties, function ($mutations) use ($calendarId) {
            $calendar = $this->getCalendarBean($calendarId);
            if ($calendar) {

                foreach ($mutations as $propertyName => $propertyValue) {

                    switch ($propertyName) {
                        case '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' :
                            $calendar->transparent = $propertyValue->getValue() === 'transparent';
                            break;
                        default :
                            $fieldName = $this->propertyMap[$propertyName];
                            $calendar->$fieldName = $propertyValue;
                            break;
                    }
                }

                $calendar->save();

                return true;
            }

        });
    }

    /**
     * @inheritdoc
     */
    public function deleteCalendar($calendarId)
    {
        throw new DAV\Exception\Forbidden('Delete operation is not allowed for calendar');
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObjects($calendarId)
    {
        $events = array();
        $calendar = $this->getCalendarBean($calendarId);
        if ($calendar->load_relationship('calendar_events')) {
            $result = $calendar->calendar_events->getBeans();

            foreach ($result as $bean) {
                $events[] = $bean->toCalDavArray();
            }
        }

        return $events;
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $eventBean = $this->getEventsBean();
        $event = $eventBean->getByURI($calendarId, array($objectUri), true);

        if ($event && $event->id) {
            return $event->toCalDavArray();
        }

        return array();
    }

    /**
     * @inheritdoc
     */
    public function getMultipleCalendarObjects($calendarId, array $uris)
    {
        $events = array();

        $eventBean = $this->getEventsBean();
        $result = $eventBean->getByURI($calendarId, $uris);
        foreach ($result as $bean) {
            $events[] = $bean->toCalDavArray();
        }

        return $events;
    }

    /**
     * @inheritdoc
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        if ($this->isUnsupported($calendarData)) {
            throw new DAV\Exception\NotImplemented('RRULE format not supported');
        }
        $event = $this->getEventsBean();

        if ($event && $event->setCalendarEventData($calendarData)) {

            $event->setCalendarEventURI($objectUri);
            $event->setCalendarId($calendarId);
            $event->save();

            return '"' . $event->etag . '"';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        if ($this->isUnsupported($calendarData)) {
            throw new DAV\Exception\NotImplemented('RRULE format not supported');
        }
        $eventBean = $this->getEventsBean();
        $event = $eventBean->getByURI($calendarId, array($objectUri), true);
        if ($event && $event->id && $event->setCalendarEventData($calendarData)) {
            $event->save();

            return '"' . $event->etag . '"';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $eventBean = $this->getEventsBean();
        $event = $eventBean->getByURI($calendarId, array($objectUri), true);
        if ($event && $event->id) {
            $event->mark_deleted($event->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function calendarQuery($calendarId, array $filters)
    {
        $componentType = null;
        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (empty($filters['prop-filters']) && empty($filters['comp-filters'])) {
            $requirePostFilter = false;
        }

        if (!empty($filters['comp-filters'])) {
            // Figuring out if there's a component filter
            if (empty($filters['comp-filters'][0]['is-not-defined'])) {

                $componentType = $filters['comp-filters'][0]['name'];

                // Checking if we need post-filters
                if (empty($filters['prop-filters']) &&
                    empty($filters['comp-filters'][0]['comp-filters']) &&
                    empty($filters['comp-filters'][0]['time-range']) &&
                    empty($filters['comp-filters'][0]['prop-filters'])
                ) {
                    $requirePostFilter = false;
                }

                // There was a time-range filter
                if (($componentType == 'VEVENT' || $componentType == 'VTODO') &&
                    isset($filters['comp-filters'][0]['time-range'])
                ) {

                    $timeRange = $filters['comp-filters'][0]['time-range'];

                    // If start time OR the end time is not specified, we can do a
                    // 100% accurate mysql query.
                    if (empty($filters['prop-filters']) &&
                        empty($filters['comp-filters'][0]['comp-filters']) &&
                        empty($filters['comp-filters'][0]['prop-filters']) &&
                        (empty($timeRange['start']) || empty($timeRange['end']))
                    ) {
                        $requirePostFilter = false;
                    }
                }
            }
        }

        $events = array();
        $eventBean = $this->getEventsBean();
        $eventQuery = $this->getSugarQuery();

        $eventQuery->from($eventBean);
        $eventQuery->where()->equals('calendarid', $calendarId);

        if ($componentType) {
            $eventQuery->where()->equals('componenttype', $componentType);
        }

        if ($timeRange) {

            if (isset($timeRange['start'])) {
                $eventQuery->where()->gte('lastoccurence', $timeRange['start']->getTimeStamp());
            }

            if (isset($timeRange['end'])) {
                $eventQuery->where()->lte('firstoccurence', $timeRange['end']->getTimeStamp());
            }

        }

        $result = $eventQuery->execute();

        foreach ($result as $key => $row) {

            if ($requirePostFilter && !$this->validateFilterForObject($row, $filters)) {
                continue;
            }
            $events[] = $row['uri'];
        }

        return $events;
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObjectByUID($principalUri, $uid)
    {
        $userHelper = $this->getUserHelper();
        $calendars = $userHelper->getCalendars($principalUri);

        if ($calendars) {

            $calendarURIS = $calendarIDS = array();
            foreach ($calendars as $calendar) {
                $calendarIDS[] = $calendar->id;
                $calendarURIS[$calendar->id] = $calendar->uri;
            }

            $eventBean = $this->getEventsBean();
            $eventQuery = $this->getSugarQuery();

            $eventQuery->from($eventBean);
            $eventQuery->where()->in('calendarid', $calendarIDS);
            $eventQuery->where()->equals('event_uid', $uid);

            $events = $eventBean->fetchFromQuery($eventQuery);

            if ($events) {
                $event = array_shift($events);

                return $calendarURIS[$event->calendarid] . '/' . $event->uri;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSchedulingObject($principalUri, $objectUri)
    {
        $userHelper = $this->getUserHelper();
        $schedulingBean = $this->getSchedulingBean();
        $user = $userHelper->getUserByPrincipalString($principalUri);
        $result = array();

        if (!$user) {
            return $result;
        }

        $scheduling = $schedulingBean->getByUri($objectUri, $user->id);
        if (!$scheduling) {
            return null;
        }

        return $scheduling->toCalDavArray();
    }

    /**
     * @inheritdoc
     */
    public function getSchedulingObjects($principalUri)
    {
        $userHelper = $this->getUserHelper();
        $schedulingBean = $this->getSchedulingBean();
        $user = $userHelper->getUserByPrincipalString($principalUri);
        $result = array();

        if (!$user) {
            return $result;
        }

        $schedulings = $schedulingBean->getByAssigned($user->id);
        foreach ($schedulings as $scheduling) {
            $result[] = $scheduling->toCalDavArray();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function deleteSchedulingObject($principalUri, $objectUri)
    {
        $userHelper = $this->getUserHelper();
        $schedulingBean = $this->getSchedulingBean();
        $user = $userHelper->getUserByPrincipalString($principalUri);
        $result = array();

        if (!$user) {
            return $result;
        }
        $scheduling = $schedulingBean->getByUri($objectUri, $user->id);

        if ($scheduling) {
            $scheduling->mark_deleted($scheduling->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function createSchedulingObject($principalUri, $objectUri, $objectData)
    {
        $userHelper = $this->getUserHelper();
        $schedulingBean = $this->getSchedulingBean();

        $user = $userHelper->getUserByPrincipalString($principalUri);
        if ($user && $schedulingBean->setSchedulingEventData($user, $objectUri, $objectData)) {
            $schedulingBean->save();
        }
    }
}
