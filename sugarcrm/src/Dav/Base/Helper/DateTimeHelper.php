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
        // we need to calculate seconds of interval, right way to get difference of timestamp but we should
        // use static date and timezone without daylight for proper calculation
        $currentDateTime = new \DateTime('2015-06-01 00:00:00', new \DateTimeZone('UTC'));
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
        // we need to calculate seconds of interval, right way to get difference of timestamp but we should
        // use static date and timezone without daylight for proper calculation
        $currentDate = new \DateTime('2015-06-01 00:00:00', new \DateTimeZone('UTC'));
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
     * @return \SugarDateTime
     */
    public function davDateToSugar(VObject\Property\ICalendar\DateTime $vDateTime)
    {
        $tz = null;
        if (!$vDateTime->hasTime()) {
            $tz = new \DateTimeZone($this->getCurrentUser()->getPreference('timezone'));
        }

        $dt = $vDateTime->getDateTime($tz);
        return new \SugarDateTime($dt->format('Ymd\THis'), $dt->getTimezone());
    }

    /**
     * Create DateTime object with selected timezone based on UTC
     * @param string $dateTime datetime string in UTC format
     * @param \DateTimeZone $tz expected timezone in event
     * @return \DateTime
     */
    public function sugarDateToDav(\SugarDateTime $dateTime, \DateTimeZone $tz = null)
    {
        $dt = new \DateTime($dateTime, new \DateTimeZone('UTC'));
        if (!$tz) {
            $tz = new \DateTimeZone('UTC');
        }
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
     * @return \SugarDateTime
     */
    public function sugarDateToUTC($dateTime)
    {
        $sugarTimeDate = new \TimeDate();
        $dt = $sugarTimeDate->fromDb($dateTime);
        if ($dt) {
            return $dt;
        }

        $dt = $sugarTimeDate->fromUser($dateTime, $this->getCurrentUser());
        $dt->setTimeZone(new \DateTimeZone('UTC'));

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
