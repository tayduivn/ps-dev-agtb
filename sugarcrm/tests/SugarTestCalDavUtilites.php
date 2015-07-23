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

    /**
     * Create CalDav calendar
     * @param User $sugarUser
     * @param array $properties
     * @return string
     */
    public static function createCalendar(User $sugarUser)
    {
        $calendarBean = BeanFactory::getBean('CalDavCalendars');
        $calendar = $calendarBean->createDefaultForUser($sugarUser);
        self::$_createdCalendars[] = $calendar->id;
        return $calendar->id;
    }

    public static function deleteAllCreatedCalendars()
    {
        if (self::$_createdCalendars) {
            $GLOBALS['db']->query('DELETE FROM caldav_calendars WHERE id IN (\'' .
                implode("', '", self::$_createdCalendars) . '\')');
            self::$_createdCalendars = array();
        }
    }

    public static function addToCreated($calendarID)
    {
        self::$_createdCalendars[] = $calendarID;
    }
}
