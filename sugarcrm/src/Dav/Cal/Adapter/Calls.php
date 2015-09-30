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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract as CalDavAbstractAdapter;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;

/**
 * Class for processing Calls by iCal protocol
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Calls extends CalDavAbstractAdapter implements AdapterInterface
{
    public function export(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        if (!($sugarBean instanceof \Call)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of Call. Instance of '. get_class($sugarBean) .' given');
        }
        $dateTimeHelper = $this->getDateTimeHelper();
        $isEventChanged = false;
        $dateStart = $dateEnd = '';
        $sugarBean = $this->getNotCachedBean($sugarBean);
        if (!$calDavBean->calendarid) {
            $calendars = $this->getUserCalendars();
            if ($calendars !== null) {
                $calDavBean->setCalendarId(key($calendars));
            } else {
                return false;
            }
        }

        $calendarEvent = $calDavBean->getVCalendarEvent();

        $calendarComponent = $calDavBean->setComponent($calDavBean->getComponentTypeName());
        foreach ($this->exportBeanDataMap as $functionName => $field) {
            if ($calDavBean->$functionName($sugarBean->$field, $calendarComponent)) {
                $isEventChanged = true;
            }
        }

        if ($sugarBean->date_start) {
            $dateStart = $dateTimeHelper->sugarDateToUTC($sugarBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if (!$dateStart || $dateStart !== $calDavBean->getStartDate()) {
            $calDavBean->setStartDate($dateStart, $calendarComponent);
            $isEventChanged = true;
        }

        if ($sugarBean->date_end) {
            $dateEnd = $dateTimeHelper->sugarDateToUTC($sugarBean->date_end)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if (!$dateEnd || $dateEnd !== $calDavBean->getEndDate()) {
            $calDavBean->setEndDate($dateEnd, $calendarComponent);
            $isEventChanged = true;
        }

        if ($calDavBean->setDuration($sugarBean->duration_hours, $sugarBean->duration_minutes, $calendarComponent)) {
            $isEventChanged = true;
        }
        if ($calDavBean->setOrganizer($calendarComponent)) {
            $isEventChanged = true;
        }
        if ($this->setExportReminders($sugarBean, $calDavBean, $calendarComponent)) {
            $isEventChanged = true;
        }
        if ($calDavBean->setParticipants($calendarComponent)) {
            $isEventChanged = true;
        }
        if ($this->setRecurringRulesToCalDav($sugarBean, $calDavBean)) {
            $isEventChanged = true;
        }
        $calDavBean->setCalendarEventData($calendarEvent->serialize());

        return $isEventChanged;
    }

    /**
     * set meeting bean property
     * @param \SugarBean $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function import(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        return false;
    }
}
