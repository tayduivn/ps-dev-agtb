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

use Sabre\CalDAV\Backend\BackendInterface;
use Sugarcrm\Sugarcrm\Dav\Base\Helper;

class CalendarData implements BackendInterface
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
     * Get CalDavCalendar bean object
     * @param string $calendarID
     * @return null|\CalDavCalendar
     */
    public function getCalendarBean($calendarID = null)
    {
        return \BeanFactory::getBean('CalDavCalendars', $calendarID);
    }

    /**
     * @inheritdoc
     */
    public function getCalendarsForUser($principalUri)
    {
        $result = array();
        $userHelper = $this->getUserHelper();
        $calendars = $userHelper->getCalendars($principalUri);

        foreach ($calendars as $calendar) {
            $result[] = $calendar->toCalDavArray($this->propertyMap, $userHelper);
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
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getMultipleCalendarObjects($calendarId, array $uris)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        // TODO: Implement createCalendarObject() method.
    }

    /**
     * @inheritdoc
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        // TODO: Implement updateCalendarObject() method.
    }

    /**
     * @inheritdoc
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        // TODO: Implement deleteCalendarObject() method.
    }

    /**
     * @inheritdoc
     */
    public function calendarQuery($calendarId, array $filters)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getCalendarObjectByUID($principalUri, $uid)
    {
        return array();
    }
}
