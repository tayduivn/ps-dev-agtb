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

use Sugarcrm\Sugarcrm\Dav\Base;

class SugarTestCalDavUtilities
{
    private static $_createdCalendars = array();
    private static $createdEvents = array();
    private static $createdSchedulingObjects = array();

    /**
     * Create scheduling object
     * @param User $user
     * @param string $objectUri
     * @param string $eventData
     */
    public static function createSchedulingObject(\User $user, $objectUri, $eventData)
    {
        $schedulingBean = BeanFactory::getBean('CalDavSchedulings');
        $schedulingBean->assigned_user_id = $user->id;

        $schedulingBean->setSchedulingEventData($user, $objectUri, $eventData);
        $schedulingBean->save();
        $schedulingBean->retrieve($schedulingBean->id);

        self::$createdSchedulingObjects[] = $schedulingBean->id;

        return $schedulingBean;
    }

    /**
     * Delete all created schedulign objects
     */
    public static function deleteSchedulingObjects()
    {
        if (self::$createdSchedulingObjects) {
            $GLOBALS['db']->query('DELETE FROM caldav_scheduling WHERE id IN (\'' .
                implode("', '", self::$createdSchedulingObjects) . '\')');
        }
    }

    /**
     * Create CalDav calendar
     * @param User $sugarUser
     * @return string
     */
    public static function createCalendar(User $sugarUser)
    {
        /** @var \CalDavCalendar $calendarBean */
        $calendarBean = BeanFactory::getBean('CalDavCalendars');
        $calendar = $calendarBean->createDefaultForUser($sugarUser);
        $calendarBean->retrieve($calendar['id']);
        self::$_createdCalendars[] = $calendar['id'];
        return $calendar['id'];
    }

    public static function deleteAllCreatedCalendars()
    {
        if (self::$_createdCalendars) {
            $assigned = array();
            foreach (self::$_createdCalendars as $calendarId) {
                $calendar = \BeanFactory::getBean('CalDavCalendars', $calendarId);
                $assigned[] = $calendar->assigned_user_id;
            }
            $GLOBALS['db']->query('DELETE FROM caldav_calendars WHERE id IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');

            $GLOBALS['db']->query('DELETE FROM caldav_changes WHERE calendarid IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');

            $GLOBALS['db']->query('DELETE FROM caldav_events WHERE calendar_id IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');

            $GLOBALS['db']->query('DELETE FROM caldav_scheduling WHERE assigned_user_id IN (\'' .
                implode("', '", $assigned) . '\')');

            self::$_createdCalendars = array();
        }
    }

    public static function addCalendarToCreated($calendarID)
    {
        self::$_createdCalendars[] = $calendarID;
    }

    /**
     * Create CalDav event by parameters
     * @param array $eventData Set of object properties
     * @param bool $scheduleLocalDelivery
     * @return \CalDavEventCollection
     */
    public static function createEvent(array $eventData = array(), $scheduleLocalDelivery = false)
    {
        $event = BeanFactory::getBean('CalDavEvents');
        $event->doLocalDelivery = $scheduleLocalDelivery;

        if (isset($eventData['calendardata'])) {
            $event->setData($eventData['calendardata']);
        }

        if (isset($eventData['calendarid'])) {
            $event->setCalendarId($eventData['calendarid']);
        }

        if (isset($eventData['eventURI'])) {
            $event->setCalendarEventURI($eventData['eventURI']);
        }

        $event->processed = true;
        $event->save();
        self::$createdEvents[] = $event;

        return $event;
    }

    /**
     * Gets list of all created objects identifiers
     * @return array
     */
    public static function getCreatedEventsId()
    {
        $createdIDs = array();

        foreach (self::$createdEvents as $event) {
            $createdIDs[] = $event->id;
        }

        return $createdIDs;
    }

    /**
     * Delete all created events
     */
    public static function deleteCreatedEvents()
    {
        $createdID = self::getCreatedEventsId();
        if ($createdID) {
            $uris = array();
            foreach ($createdID as $id) {
                $event = \BeanFactory::getBean('CalDavEvents', $id);
                $uris[] = $event->uri;
            }

            $GLOBALS['db']->query('DELETE FROM caldav_events WHERE id IN (\'' . implode("', '", $createdID) . '\')');
            $GLOBALS['db']->query('DELETE FROM caldav_synchronization WHERE event_id IN (\'' .
                implode("', '", $createdID) . '\')');
            $GLOBALS['db']->query('DELETE FROM caldav_scheduling WHERE uri IN (\'' .
                implode("', '", $uris) . '\')');
        }
    }
}
