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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Backend;

use Sabre\DAV;
use Sabre\CalDAV;

use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\VObject;
use Sabre\CalDAV\Backend\SchedulingSupport;
use Sabre\CalDAV\Backend\SyncSupport;
use Sugarcrm\Sugarcrm\Dav\Base\Constants;
use Sugarcrm\Sugarcrm\Dav\Base\Helper;

/**
 * Class CalendarData
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Backend
 *
 */
class CalendarData extends AbstractBackend implements SchedulingSupport, SyncSupport
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

    protected function isUnsupported($calendarData)
    {
        $vObject = VObject\Reader::read($calendarData);
        $mainComponent = $vObject->getBaseComponent();
        if ($mainComponent->RRULE) {
            $rRule = $mainComponent->RRULE->getParts();
            return
                !empty($rRule['BYMONTH']) ||
                !empty($rRule['BYYEARDAY']) ||
                !empty($rRule['BYWEEKNO']) ||
                !empty($rRule['BYHOUR']) ||
                $rRule['FREQ'] == 'MINUTELY' ||
                $rRule['FREQ'] == 'HOURLY';
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
     * Get CalDavEventCollection bean object
     *
     * @return null|\CalDavEventCollection
     */
    public function getEventsBean()
    {
        $bean = \BeanFactory::getBean('CalDavEvents');
        $bean->doLocalDelivery = false;
        return $bean;
    }

    /**
     * Get CalDavChanges bean object
     * @return null|\SugarBean
     */
    public function getChangesBean()
    {
        return \BeanFactory::getBean('CalDavChanges');
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
        $user = $userHelper->getUserByPrincipalString($principalUri);

        if ($user) {
            $calendarBean = $this->getCalendarBean();
            $query = $this->getSugarQuery();
            $query->from($calendarBean);
            $query->where()->equals('assigned_user_id', $user->id);
            $calendars = $query->execute();

            if (empty($calendars)) {
                $calendars = array(
                    $calendarBean->createDefaultForUser($user)
                );
            }

            foreach ($calendars as $calendar) {
                $result[] = $this->calendarSQLRowToCalDavArray($calendar, $principalUri);
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
            if ($calendar->id) {

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
        if ($calendar->id) {
            $eventBean = $this->getEventsBean();
            $query = $this->getSugarQuery();
            $query->from($eventBean);
            $query->where()->equals('calendar_id', $calendar->id);

            $interval = $this->getCurrentUser()->getPreference('caldav_interval');
            if ($interval != 0) {
                $date = $this->getDateTime()->modify("-" . $interval)->format('U');
                $query->where()->gte('last_occurence', $date);
            }

            $result = $query->execute();
            foreach ($result as $event) {
                $events[] = $this->eventSQLRowToCalDavArray($event);
            }
        }

        return $events;
    }

    /**
     * Return current user.
     * Need to mock result of User in UTs.
     *
     * @return \User
     */
    protected function getCurrentUser()
    {
        global $current_user;
        return $current_user;
    }

    /**
     * Get DateTime object with current time
     * Need to mock result of DateTime in UTs.
     *
     * @return \DateTime
     */
    protected function getDateTime()
    {
        return new \DateTime('NOW', new \DateTimeZone('UTC'));
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $event = $this->getMultipleCalendarObjects($calendarId, array($objectUri));
        if (isset($event[0])) {
            return $event[0];
        }

        return array();
    }

    /**
     * @inheritdoc
     */
    public function getMultipleCalendarObjects($calendarId, array $uris)
    {
        $events = array();
        $calendar = $this->getCalendarBean($calendarId);
        if ($calendar->id) {
            $eventBean = $this->getEventsBean();
            $query = $this->getSugarQuery();
            $query->from($eventBean);
            $query->where()->equals('calendar_id', $calendarId);

            if (count($uris) == 1) {
                $query->where()->equals('caldav_events.uri', reset($uris));
                $query->limit(1);
            } else {
                $query->where()->in('caldav_events.uri', $uris);
            }

            $result = $query->execute();

            if (!empty($result)) {
                foreach ($result as $event) {
                    $events[] = $this->eventSQLRowToCalDavArray($event);
                }
            }
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

        if ($event && $event->setData($calendarData)) {

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
        $events = $eventBean->getByURI($calendarId, array($objectUri), 1);
        if (!$events) {
            return null;
        }
        $event = array_shift($events);

        if ($event->getSynchronizationObject()->getConflictCounter()) {
            throw new DAV\Exception\Conflict('Event in the middle of conflict solving');
        }

        $event->doLocalDelivery = false;
        if ($event && $event->id && $event->setData($calendarData)) {
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
        $events = $eventBean->getByURI($calendarId, array($objectUri), 1);
        if (!$events) {
            return null;
        }
        $event = array_shift($events);
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
        $eventQuery->where()->equals('calendar_id', $calendarId);

        if ($componentType) {
            $eventQuery->where()->equals('component_type', $componentType);
        }

        if ($timeRange) {

            if (isset($timeRange['start'])) {
                $eventQuery->where()->gte('last_occurence', $timeRange['start']->getTimeStamp());
            }

            if (isset($timeRange['end'])) {
                $eventQuery->where()->lte('first_occurence', $timeRange['end']->getTimeStamp());
            }

        }

        $result = $eventQuery->execute();

        foreach ($result as $key => $row) {

            $row = $this->eventSQLRowToCalDavArray($row);
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
                $calendarIDS[] = $calendar['id'];
                $calendarURIS[$calendar['id']] = $calendar['uri'];
            }

            $eventBean = $this->getEventsBean();
            $eventQuery = $this->getSugarQuery();

            $eventQuery->from($eventBean);
            $eventQuery->where()->in('calendar_id', $calendarIDS);
            $eventQuery->where()->equals('event_uid', $uid);

            $events = $eventBean->fetchFromQuery($eventQuery);

            if ($events) {
                $event = array_shift($events);

                return $calendarURIS[$event->calendar_id] . '/' . $event->uri;
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
        $user = $userHelper->getUserByPrincipalString($principalUri);

        if (!$user) {
            return array();
        }

        return $this->getSchedulingByUri($objectUri, $user->id);
    }

    /**
     * @inheritdoc
     */
    public function getSchedulingObjects($principalUri)
    {
        $userHelper = $this->getUserHelper();
        $user = $userHelper->getUserByPrincipalString($principalUri);
        $result = array();

        if (!$user) {
            return $result;
        }

        return $this->getSchedulingByAssigned($user->id);
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

    /**
     * @inheritdoc
     */
    public function getChangesForCalendar($calendarId, $syncToken = 0, $syncLevel = 1, $limit = null)
    {
        $calendar = $this->getCalendarBean($calendarId);

        if (!$calendar->synctoken || $calendar->synctoken < $syncToken) {
            return null;
        }

        $out = array(
            'syncToken' => $calendar->synctoken,
            'added' => array(),
            'modified' => array(),
            'deleted' => array(),
        );

        $changesBean = $this->getChangesBean();
        $query = $this->getSugarQuery();
        $query->from($changesBean);
        $query->select(array('uri', 'operation'));
        $query->where()->equals('calendarid', $calendarId);
        $query->where()->gte('synctoken', $syncToken);
        $query->where()->lt('synctoken', $calendar->synctoken);
        $query->orderBy('synctoken', 'DESC');
        if (!empty($limit)) {
            $query->limit($limit);
        }
        $result = $query->execute();

        $changes = array();
        foreach ($result as $row) {
            if (!empty($row['uri']) && !isset($changes[$row['uri']])) {
                $changes[$row['uri']] = $row['operation'];
            }
        }
        foreach ($changes as $uri => $operation) {

            switch ($operation) {
                case Constants::OPERATION_ADD:
                    $out['added'][] = $uri;
                    break;
                case Constants::OPERATION_MODIFY:
                    $out['modified'][] = $uri;
                    break;
                case Constants::OPERATION_DELETE:
                    $out['deleted'][] = $uri;
                    break;
            }
        }

        return $out;
    }

    /**
     * Convert sql row to array
     * @param array $event
     * @return array
     */
    protected function eventSQLRowToCalDavArray($event)
    {
        return array(
            'id' => $event['id'],
            'uri' => $event['uri'],
            'lastmodified' => strtotime($event['date_modified'] . ' UTC'),
            'etag' => '"' . $event['etag'] . '"',
            'calendarid' => $event['calendar_id'],
            'size' => $event['data_size'],
            'calendardata' => $event['calendar_data'],
            'component' => strtolower($event['component_type']),
        );
    }

    /**
     * Retrieve all scheduling objects by user
     * @param string $userId
     * @return array
     */
    public function getSchedulingByAssigned($userId)
    {
        $schedulingBean = $this->getSchedulingBean();

        $query = $this->getSugarQuery();

        $query->from($schedulingBean);
        $query->where()->equals('assigned_user_id', $userId);

        $schedulings = array();
        $result = $query->execute();
        if (!empty($result)) {
            foreach ($result as $scheduling) {
                $schedulings[] = $this->schedulingSQLRowToCalDavArray($scheduling);
            }
        }

        return $schedulings;
    }

    /**
     * @param $objectUri
     * @param string $userId
     * @return array
     */
    protected function getSchedulingByUri($objectUri, $userId)
    {
        $schedulingBean = $this->getSchedulingBean();

        $query = $this->getSugarQuery();

        $query->from($schedulingBean);
        $query->where()->equals('uri', $objectUri);
        $query->where()->equals('assigned_user_id', $userId);
        $query->limit(1);

        $result = $query->execute();

        if (empty($result)) {
            return null;
        }

        return $this->schedulingSQLRowToCalDavArray($result[0]);
    }

    /**
     * Convert sql row to array
     *
     * @param array $scheduling
     * @return array
     */
    protected function schedulingSQLRowToCalDavArray($scheduling)
    {
        return array(
            'uri' => $scheduling['uri'],
            'calendardata' => $scheduling['calendar_data'],
            'lastmodified' => strtotime($scheduling['date_modified'] . ' UTC'),
            'etag' => '"' . $scheduling['etag'] . '"',
            'size' => $scheduling['data_size'],
        );
    }

    /**
     * Convert bean to CalDav calendar array format
     *
     * @param array  $calendar
     * @param string $principalUri
     *
     * @return array
     */
    protected function calendarSQLRowToCalDavArray($calendar, $principalUri)
    {
        $result = array();

        $result['id'] = $calendar['id'];
        $result['uri'] = $calendar['uri'];

        foreach ($this->propertyMap as $davProperty => $calendarProperty) {
            $result[$davProperty] = $calendar[$calendarProperty];
        }

        $result['{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set'] =
            new CalDAV\Xml\Property\SupportedCalendarComponentSet(explode(',', $calendar['components']));

        $result['{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag'] = 'http://sabre.io/ns/sync/' . $calendar['synctoken'];
        $result['{http://sabredav.org/ns}sync-token'] = $calendar['synctoken'];

        $result['{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp'] =
            new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar['transparent'] ? 'transparent' : 'opaque');

        $result['principaluri'] = $principalUri;

        return $result;
    }
}
