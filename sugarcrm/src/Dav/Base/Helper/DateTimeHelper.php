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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sabre\VObject;

class DateTimeHelper
{

    /**
     * Convert DURATION format to seconds
     * @see http://tools.ietf.org/html/rfc5545#page-34
     * @param string $duration
     * @return int
     */
    public function durationToSeconds($duration)
    {
        $durationInterval = VObject\DateTimeParser::parseDuration($duration, false);
        $currentDateTime = new \DateTime();
        $durationDateTime = clone $currentDateTime;
        $durationDateTime->add($durationInterval);

        return $durationDateTime->getTimestamp() - $currentDateTime->getTimestamp();
    }

    /**
     * Convert seconds to DURATION format
     * @see http://tools.ietf.org/html/rfc5545#page-34
     * @param int $seconds
     * @return string | null
     */
    public function secondsToDuration($seconds)
    {
        if (!$seconds) {
            return null;
        }
        $parts = array(
            'date' => array('Y' => 'y', 'M' => 'm', 'D' => 'd'),
            'time' => array('H' => 'h', 'M' => 'i', 'S' => 's'),
        );
        $currentDate = new \DateTime();
        $durationDate = clone $currentDate;
        $durationDate->modify($seconds . ' seconds');
        $dateInterval = $currentDate->diff($durationDate);

        $aParts = array();
        foreach ($parts as $group) {
            $tmpPart = '';
            foreach ($group as $part => $key) {
                if ($dateInterval->$key) {
                    $tmpPart .= $dateInterval->$key . $part;
                }
            }
            $aParts[] = $tmpPart;
        }

        return ($dateInterval->invert ? '-' : '') . 'P' . implode($aParts, 'T');
    }

    /**
     * Convert DAV Datetime format to SugarCRM DB format
     * @param VObject\Property\ICalendar\DateTime $vDateTime
     * @return string
     */
    public function davDateToSugar(VObject\Property\ICalendar\DateTime $vDateTime)
    {
        $dt = $vDateTime->getDateTime();
        if ($vDateTime->getValueType() == 'DATE-TIME') {
            $date = $dt->format('Ymd\THis');
        } else {
            $date = $dt->format('Ymd') . 'T000000';
        }

        $dateTime = \SugarDateTime::createFromFormat('Ymd\THis', $date, $dt->getTimezone());

        return $dateTime->asDb();
    }

    /**
     * Create DateTime object with UTC timetone
     * @param $dateTime
     * @return \DateTime
     */
    public function sugarDateToDav($dateTime)
    {
        $tz = new \DateTimeZone('UTC');
        $dt = new \DateTime($dateTime, $tz);
        $dt->setTimeZone($tz);

        return $dt;
    }

    /**
     * Return date in user set format
     * @param string $dateTime
     * @return string
     */
    public function sugarDateToUserDate($dateTime)
    {
        $formattedDate = false;
        $sugarDate = \SugarDateTime::createFromFormat(\TimeDate::DB_DATETIME_FORMAT, $dateTime);
        if ($sugarDate) {
            $formattedDate = $sugarDate->format($GLOBALS['timedate']->get_date_format());
        }
        return $formattedDate;
    }

    /**
     * Return date and time in user set format
     * @param string $dateTime
     * @return string
     */
    public function sugarDateToUserDateTime($dateTime)
    {
        $formattedDate = false;
        $sugarDate = \SugarDateTime::createFromFormat(\TimeDate::DB_DATETIME_FORMAT, $dateTime);
        if ($sugarDate) {
            $formattedDate = $sugarDate->format($GLOBALS['timedate']->get_date_time_format());
        }
        return $formattedDate;
    }

    /**
     * Create DateTime object with UTC timetone
     * @param string $dateTime
     * @return \DateTime
     */
    public function sugarDateToUTC($dateTime)
    {
        $sugarTimeDate = new \TimeDate();
        $dt = $sugarTimeDate->fromDb($dateTime);
        if ($dt) {
            return $dt;
        }

        $currentTimezone = $this->getCurrentUser()->getPreference('timezone');
        if (!$currentTimezone) {
            $currentTimezone = 'UTC';
        }
        $userTimeZone = new \DateTimeZone($currentTimezone);
        $utcTimeZone = new \DateTimeZone('UTC');
        $dt = new \DateTime($dateTime, $userTimeZone);
        $dt->setTimeZone($utcTimeZone);

        return $dt;
    }

    /**
     * Get current user
     * @return \User
     */
    protected function getCurrentUser()
    {
        return $GLOBALS['current_user'];
    }
}
