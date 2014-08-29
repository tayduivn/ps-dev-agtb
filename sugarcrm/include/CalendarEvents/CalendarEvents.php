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

class CalendarEvents
{
    /**
     * Schedulable calendar events (modules) supported
     */
    public static $calendarEventModules = array(
        "Meetings",
        "Calls",
        "Tasks",
    );

    /**
     * @param SugarBean $bean
     * @return bool
     * @throws SugarException
     */
    public function isEventRecurring(SugarBean $bean)
    {
        if (!in_array($bean->module_name, static::$calendarEventModules)) {
            $logmsg = "Recurring Calendar Event - Module Unexpected: " . $bean->module_name;
            $GLOBALS['log']->error($logmsg);
            throw new SugarException("LBL_CALENDAR_EVENT_RECURRENCE_MODULE_NOT_SUPPORTED", array($bean->module_name));
        }

        return (!empty($bean->repeat_type) && !empty($bean->date_start));
    }

    /**
     * Return Configured recurrence limit.
     * @return int
     */
    public function getRecurringLimit()
    {
        return SugarConfig::getInstance()->get('calendar.max_repeat_count', 1000);
    }

    /**
     * Rebuild the FreeBusy Vcal Cache for specified user
     */
    public function rebuildFreeBusyCache(User $user)
    {
        vCal::cache_sugar_vcal($user);
    }

    /**
     * @param SugarBean $parentBean
     * @param bool $checkLimit
     * @return array events saved
     * @throws SugarException
     */
    public function saveRecurringEvents(SugarBean $parentBean, $checkLimit = true)
    {
        global $timedate;

        if (!$this->isEventRecurring($parentBean)) {
            $logmsg = "SaveRecurringEvents() : Event is not a Recurring Event";
            $GLOBALS['log']->error($logmsg);
            throw new SugarException("LBL_CALENDAR_EVENT_NOT_A_RECURRING_EVENT", array($parentBean->object_name));
        }

        if (!empty($parentBean->repeat_parent_id)) {
            $logmsg = "SaveRecurringEvents() : Event received is not the Parent Occcurrence";
            $GLOBALS['log']->error($logmsg);
            throw new SugarException("LBL_CALENDAR_EVENT_IS_NOT_A_PARENT_OCCURRENCE", array($parentBean->object_name));
        }

        $dateStart = $this->formatDateTime("datetime", $parentBean->date_start, "user");

        $params = array();
        $params['type'] = $parentBean->repeat_type;
        $params['interval'] = $parentBean->repeat_interval;
        $params['count'] = $parentBean->repeat_count;
        $params['until'] = $this->formatDateTime("date", $parentBean->repeat_until, "user");
        $params['dow'] = $parentBean->repeat_dow;

        $repeatDateTimeArray = $this->buildRecurringSequence($dateStart, $params);

        if ($checkLimit) {
            $limit = $this->getRecurringLimit();
            if (count($repeatDateTimeArray) > ($limit - 1)) {
                $logMessage = sprintf(
                    "Calendar Events (%d) exceed Event Limit: (%d)",
                    count($repeatDateTimeArray),
                    $limit
                );
                $GLOBALS['log']->warning($logMessage);
                throw new SugarException("LBL_CALENDAR_EVENT_LIMIT_EXCEEDED", array($parentBean->object_name));
            }
        }

        $this->markRepeatDeleted($parentBean);
        return $this->saveRecurring($parentBean, $repeatDateTimeArray);
    }

    /**
     * Mark recurring meeting deleted
     * @param string Start Date
     * @param array  Repeat Occurrence Fields: 'type', 'interval', 'count' 'until' 'dow'
     * @return array Start DateTimes
     */
    protected function buildRecurringSequence($dateStart, array $params)
    {
        return CalendarUtils::buildRecurringSequence($dateStart, $params);
    }

    /**
     * Mark recurring meeting deleted
     * @param SugarBean parent Bean
     */
    protected function markRepeatDeleted(SugarBean $parentBean)
    {
        CalendarUtils::markRepeatDeleted($parentBean);
    }

    /**
     * @param SugarBean $parentBean
     * @param array $repeatDateTimeArray
     * @param array $args
     * @return array events saved
     */
    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray, array $args = array())
    {
        Activity::disable();

        $recurringEvents = array();
        $clone = clone $parentBean;
        foreach ($repeatDateTimeArray as $dateStart) {
            $clone->id = "";
            $clone->date_start = $dateStart;
            $date = SugarDateTime::createFromFormat($GLOBALS['timedate']->get_date_time_format(), $dateStart);
            $date = $date->get("+{$parentBean->duration_hours} Hours")->get("+{$parentBean->duration_minutes} Minutes");
            $date_end = $date->format($GLOBALS['timedate']->get_date_time_format());
            $clone->date_end = $date_end;
            $clone->recurring_source = "Sugar";
            $clone->repeat_parent_id = $parentBean->id;
            $clone->update_vcal = false;
            $clone->save(false);

            if ($clone->id) {
                $recurringEvents[$clone->id] = $clone->date_start;
            }
        }

        Activity::enable();

        $this->rebuildFreeBusyCache($GLOBALS['current_user']);

        return $recurringEvents;
    }

    /**
     * Convert A Date, Time  or DateTime String from one format to Another
     * @param string type of the second argument : one of "date", "time", "datetime", "datetimecombo"
     * @param string formatted date, time or datetime field in DB, ISO, or User Format
     * @param string output format - one of: "db", "iso" or "user"
     * @return string formatted result
     */
    public function formatDateTime($type, $dtm, $toFormat)
    {
        $result = '';
        $sugarDateTime = $this->getSugarDateTime($type, $dtm);
        if (!empty($sugarDateTime)) {
            $result = $sugarDateTime->formatDateTime($type, $toFormat, $GLOBALS['current_user']);
        }
        return $result;
    }

    /**
     * Return a SugarDateTime Object given any Date to Time Format
     * @param string type of the second argument : one of "date", "time", "datetime", "datetimecombo"
     * @param string  formatted date, time or datetime field in DB, ISO, or User Format
     * @return SugarDateTime
     */
    public function getSugarDateTime($type, $dtm)
    {
        global $timedate;
        $sugarDateTime = null;
        if (!empty($dtm)) {
            $sugarDateTime = $timedate->fromUserType($dtm, $type);
            if (empty($sugarDateTime)) {
                $sugarDateTime = $timedate->fromDBType($dtm, $type);
            }
            if (empty($sugarDateTime)) {
                switch($type) {
                    case "date":
                        $sugarDateTime = $timedate->fromIso($dtm);
                        break;
                    case 'time':
                        $sugarDateTime = $timedate->fromIsoTime($dtm);
                        break;
                    case 'datetime':
                    case 'datetimecombo':
                    default:
                        $sugarDateTime = $timedate->fromIso($dtm);
                        break;
                }
            }
        }
        return $sugarDateTime;
    }
}
