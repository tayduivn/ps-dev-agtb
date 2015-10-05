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

use Sabre\VObject\Component\VCalendar as CalendarEvent;
use Sabre\VObject\Property\ICalendar\DateTime;
use Sabre\VObject\Recur\EventIterator;
use Sabre\VObject\Component as DavComponent;

use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as StatusMapper;

/**
 * Provide methods to convert Dav recurring to array and set Dav recurring from array
 * Class RecurringHelper
 * @package Sugarcrm\Sugarcrm\Dav\Base\Helper
 */
class RecurringHelper
{
    /**
     * Day map with indexes
     * @var array
     */
    protected $dayMap = array(
        'SU' => 0,
        'MO' => 1,
        'TU' => 2,
        'WE' => 3,
        'TH' => 4,
        'FR' => 5,
        'SA' => 6,
    );

    /**
     * @var DateTimeHelper
     */
    protected $dateTimeHelper;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\IntervalMap
     */
    protected $intervalMapper;

    public function __construct()
    {
        $this->dateTimeHelper = new DateTimeHelper();
        $this->intervalMapper = new StatusMapper\IntervalMap();
    }

    /**
     * Determines recurring event or not
     * @param \CalDavEvent $event
     * @param CalendarEvent $calendarEvent
     * @return bool
     */
    protected function isRecurring(\CalDavEvent $event, CalendarEvent $calendarEvent)
    {
        $component = $event->getComponent($calendarEvent);

        if (!$component) {
            return false;
        }

        if ($component->RRULE) {
            return true;
        }

        return false;
    }

    /**
     * CalDav RRRULE have more options than SugarCRM RRULE.
     * We cannot do anything with event If unsupported options found
     * @param array $rRule
     *
     * @return bool
     */
    public function isUnsupported(array $rRule)
    {
        return
            !empty($rRule['BYMONTH']) ||
            !empty($rRule['WKST']) ||
            !empty($rRule['BYMONTHDAY']) ||
            !empty($rRule['BYYEARDAY']) ||
            !empty($rRule['BYWEEKNO']) ||
            !empty($rRule['BYSETPOS']) ||
            !empty($rRule['BYHOUR']) ||
            $rRule['FREQ'] == 'MINUTELY' ||
            $rRule['FREQ'] == 'HOURLY';
    }

    /**
     * Get new CalDavEvent object
     * @return null|\CalDavEvent
     */
    protected function getEventBean()
    {
        return \BeanFactory::getBean('CalDavEvents');
    }

    /**
     * Create new CalendarEvents object
     * @return \CalendarEvents
     */
    protected function getCalendarEventsObject()
    {
        return new \CalendarEvents();
    }

    /**
     * Get recurring event info and all children from CalDav object.
     *
     * Return array:
     *      [type] =>       Recurring type (Daily, Weekly, Monthly e t.c)
     *      [interval] =>   Recurring events interval
     *      [until] =>      Recurring end
     *      [count] =>      Repeating count
     *      [dow] =>        Days of week (1234567)
     *      [children] =>   All recurring child items, Array of \CalDavEvent
     *      [deleted] =>    Deleted child items
     *
     * @param \CalDavEvent $event
     * @return array|null See above
     */
    public function getRecurringInfo(\CalDavEvent $event)
    {
        $calendarEvent = $event->getVCalendarEvent();
        if ($this->isRecurring($event, $calendarEvent)) {
            $component = $event->getComponent($calendarEvent);

            $currentRule = $component->RRULE->getParts();
            if (!$currentRule || $this->isUnsupported($currentRule)) {
                return null;
            }

            $intervalMap = $this->intervalMapper->getMapping($event);

            $result = array();
            if (isset($currentRule['FREQ']) && isset($intervalMap[$currentRule['FREQ']])) {
                $result['type'] = $intervalMap[$currentRule['FREQ']];
            } else {
                $result['type'] = '';
            }
            if (isset($currentRule['INTERVAL'])) {
                $result['interval'] = $currentRule['INTERVAL'];
            } else {
                $result['interval'] = 1;
            }
            if (isset($currentRule['COUNT'])) {
                $result['count'] = $currentRule['COUNT'];
            } elseif (isset($currentRule['UNTIL'])) {
                $dateTime = new \SugarDateTime($currentRule['UNTIL']);
                $result['until'] = $dateTime->asDb();
            }

            if ($result['type'] == 'Weekly') {
                $result['dow'] = date('w', strtotime($component->DTSTART));
            }

            if (isset($currentRule['BYDAY'])) {
                if (!is_array($currentRule['BYDAY'])) {
                    $currentRule['BYDAY'] = array($currentRule['BYDAY']);
                }
                $result['type'] = 'Weekly';
                $result['dow'] = implode('', array_intersect_key($this->dayMap, array_flip($currentRule['BYDAY'])));
            }

            $it = new EventIterator($calendarEvent, $component->UID);

            $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;

            $endDate = clone $component->DTSTART->getDateTime();
            $endDate->modify('+' . $maxRecur . ' day');
            $end = $it->getDtEnd();

            $result['children'] = array();
            $result['deleted'] = array();

            if ($component->EXDATE) {
                foreach ($component->EXDATE as $exDate) {
                    $result['deleted'][] = $this->dateTimeHelper->davDateToSugar($exDate);
                }
            }

            while ($it->valid() && $end < $endDate) {
                $child = $it->getEventObject();
                if ($child) {
                    $bean = $this->getEventBean();
                    $event = $bean->getVCalendarEvent();
                    if ($event) {
                        $event->add($child);
                        $bean->setCalendarEventData($event->serialize());
                        $result['children'][$bean->getStartDate()] = $bean;
                    }
                }
                $end = $it->getDtEnd();
                $it->next();
            }

            return $result;
        }

        return null;
    }

    /**
     * Update CalDav event recurring info with children
     *      [type] =>       Recurring type (Daily, Weekly, Monthly e t.c). Bean repeat_type
     *      [interval] =>   Recurring events interval. Bean repeat_interval
     *      [until] =>      Recurring end. Bean repeat_until
     *      [count] =>      Repeating count. Bean repeat_count
     *      [dow] =>        Days of week (1234567). Bean repeat_dow
     *      [parent] =>     Main recurring item \SugarBean
     *      [children] =>   All recurring child items, Array of \SugarBean
     *      [deleted] =>    Deleted child items
     * $recurringInfo format
     *
     * @param \CalDavEvent $event
     * @param array $recurringInfo See above
     * @return bool
     * @throws \SugarException
     */
    public function setRecurringInfo(\CalDavEvent $event, array $recurringInfo)
    {
        $calendarEvents = $this->getCalendarEventsObject();

        if (empty($recurringInfo['parent']) || !($recurringInfo['parent'] instanceof \SugarBean)) {
            return false;
        }

        $recurringBean = $recurringInfo['parent'];

        if (!$calendarEvents->isEventRecurring($recurringBean)) {
            return false;
        }

        $currentEvent = $event->getVCalendarEvent();
        $component = $event->setComponent($event->getComponentTypeName());

        if (!$component->RRULE) {
            $rRule = $currentEvent->createProperty('RRULE');
            $component->add($rRule);
        } else {
            $rRule = $component->RRULE;
        }

        $currentRules = $rRule->getParts();

        if ($currentRules && $this->isUnsupported($currentRules)) {
            return false;
        }
        $intervalMap = array_flip($this->intervalMapper->getMapping($event));
        $newRules = array();

        if (!empty($recurringInfo['type']) && isset($intervalMap[$recurringInfo['type']])) {
            $newRules['FREQ'] = $intervalMap[$recurringInfo['type']];
        }

        if (!empty($recurringInfo['interval'])) {
            if ($recurringInfo['interval'] == 1) {
                unset($recurringInfo['interval']);
            } else {
                $newRules['INTERVAL'] = (string)strtoupper($recurringInfo['interval']);
            }
        }

        if (!empty($recurringInfo['count'])) {
            $newRules['COUNT'] = (string)$recurringInfo['count'];
        }

        if (!empty($recurringInfo['until'])) {
            if (isset($currentRules['UNTIL'])) {
                $untilDate = $this->dateTimeHelper->sugarDateToDav($currentRules['UNTIL']);
                $newDate =
                    $this->dateTimeHelper->sugarDateToDav($recurringInfo['until'] . ' ' . $untilDate->format('H:i:s'));
            } else {
                $newDate = $this->dateTimeHelper->sugarDateToDav($recurringInfo['until'] . ' 23:59:59');
            }
            $newRules['UNTIL'] = $newDate->format('Ymd\THis\Z');
            if (isset($newRules['COUNT'])) {
                unset($newRules['COUNT']);
            }
        }

        if (!empty($recurringInfo['dow']) && $recurringInfo['type'] == 'Weekly') {
            $newRules['BYDAY'] =
                array_intersect_key(array_flip($this->dayMap), array_flip(str_split($recurringInfo['dow'])));
            if (!is_array($currentRules['BYDAY'])) {
                $newRules['BYDAY'] = implode(',', $newRules['BYDAY']);
            }
        }

        $isChanged = false;
        if ($newRules != $currentRules) {
            $rRule->setParts($newRules);

            $components = $currentEvent->getComponents();
            foreach ($components as $component) {
                if (!empty($component->{'RECURRENCE-ID'})) {
                    $currentEvent->remove($component);
                }
            }
            $isChanged = true;
        } elseif (!empty($recurringInfo['children'])) {
            $isChanged = $this->updateRecurringChildren($recurringBean, $event, $recurringInfo['children']);
        }

        return $isChanged;
    }

    /**
     * Update CalDav event recurring children
     * Returns true if children was updated
     * @param \SugarBean $recurringBean
     * @param \CalDavEvent $recurringEvent
     * @param array $children
     * @return bool
     */
    protected function updateRecurringChildren(
        \SugarBean $recurringBean,
        \CalDavEvent $recurringEvent,
        array $children
    ) {
        $currentRecirrung = $this->getRecurringInfo($recurringEvent);
        $result = array();
        foreach ($children as $child) {
            $event = clone $recurringEvent;
            $event->clearVCalendarEvent();
            $event->setBean($child);
            $component = $event->setComponent($event->getComponentTypeName());
            if ($component->RRULE) {
                $component->remove('RRULE');
            }
            if ($component->EXDATE) {
                $component->remove('EXDATE');
            }
            $isChanged = false;
            $startDate = $this->dateTimeHelper->sugarDateToUTC($child->date_start)->format(\TimeDate::DB_DATETIME_FORMAT);
            if (isset($currentRecirrung['children'][$startDate])) {
                $event = clone $currentRecirrung['children'][$startDate];
                $event->clearVCalendarEvent();
                $component = $event->setComponent($event->getComponentTypeName());
                $result[$child->date_start] = $component;
            }
            $isChanged |= $event->setTitle($child->name, $component);
            $isChanged |= $event->setDescription($child->description, $component);
            $isChanged |= $event->setDuration($child->duration_hours, $child->duration_minutes, $component);
            $isChanged |= $event->setLocation($child->location, $component);
            $isChanged |= $event->setStatus($child->status, $component);
            $isChanged |= $event->setStartDate($child->date_start, $component);
            $isChanged |= $event->setEndDate($child->date_end, $component);

            if ($isChanged) {
                $component->isModifed = true;
                $result[$child->date_start] = $component;
            }
        }

        $isUpdated = false;

        if ($result) {
            $currentEvent = $recurringEvent->getVCalendarEvent();
            $recurringComponent = array_shift($currentEvent->getBaseComponents('VEVENT'));
            $components = $currentEvent->getComponents();
            $currentComponents = array();
            foreach ($components as $component) {
                if (!empty($component->{'RECURRENCE-ID'})) {
                    $startDate = $this->dateTimeHelper->davDateToSugar($component->DTSTART);
                    if (!isset($result[$startDate])) {
                        $isUpdated = true;
                        $currentEvent->remove($component);
                    } else {
                        $currentComponents[$startDate] = $component;
                    }
                }
            }
            foreach ($result as $startDate => $event) {
                if (isset($currentComponents[$startDate])) {
                    $replacedComponent = $currentComponents[$startDate];
                    if ($replacedComponent->serialize() !== $event->serialize()) {
                        $currentEvent->remove($replacedComponent);
                        $this->createRecurringChild($event, $recurringComponent);
                        $isUpdated = true;
                    }
                    unset($result[$startDate]);
                } elseif (!$event->isModifed) {
                    unset($result[$startDate]);
                }
                unset($event->isModifed);
            }

            foreach ($result as $component) {
                $isUpdated = true;
                $this->createRecurringChild($component, $recurringComponent);
            }
        }

        return $isUpdated;
    }

    /**
     * Create recurring component properties and add it to calendar
     * @param DavComponent $component
     * @param DavComponent $recurringComponent
     */
    protected function createRecurringChild(DavComponent $component, DavComponent $recurringComponent)
    {
        $componentDateTime = $component->DTSTART->getDateTime();
        $recurringDateTimeString = $this->dateTimeHelper->davDateToSugar($recurringComponent->DTSTART);
        $recurringDateTime = new \DateTime($recurringDateTimeString, new \DateTimeZone('UTC'));
        $componentDate = $componentDateTime->format('Ymd') . 'T' . $recurringDateTime->format('His') . 'Z';
        $component->add(
            'RECURRENCE-ID',
            $componentDate
        );
        if ($component->UID) {
            $component->remove('UID');
        }
        $component->add(
            'UID',
            $recurringComponent->UID->getValue(),
            $recurringComponent->UID->parameters()
        );

        $recurringComponent->parent->add($component);
    }
}
