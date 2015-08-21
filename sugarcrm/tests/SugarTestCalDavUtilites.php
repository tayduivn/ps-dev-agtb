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

    /**
     * Create CalDav calendar
     * @param User $sugarUser
     * @return string
     */
    public static function createCalendar(User $sugarUser)
    {
        $calendarBean = BeanFactory::getBean('CalDavCalendars');
        $calendar = $calendarBean->createDefaultForUser($sugarUser);
        $calendar->retrieve($calendar->id);
        self::$_createdCalendars[] = $calendar->id;
        return $calendar->id;
    }

    public static function deleteAllCreatedCalendars()
    {
        if (self::$_createdCalendars) {
            $GLOBALS['db']->query('DELETE FROM caldav_calendars WHERE id IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');

            $GLOBALS['db']->query('DELETE FROM caldav_changes WHERE calendarid IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');

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
     * @return SugarBean
     */
    public static function createEvent(array $eventData = array())
    {
        $event = BeanFactory::getBean('CalDavEvents');

        if (isset($eventData['calendardata'])) {
            $event->setCalendarEventData($eventData['calendardata']);
        }

        if (isset($eventData['calendarid'])) {
            $event->setCalendarId($eventData['calendarid']);
        }

        if (isset($eventData['eventURI'])) {
            $event->setCalendarEventURI($eventData['eventURI']);
        }

        $event->processed = true;
        $event->save();
        $event->retrieve($event->id);
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
            $GLOBALS['db']->query('DELETE FROM caldav_events WHERE id IN (\'' . implode("', '", $createdID) . '\')');
        }
    }
}
