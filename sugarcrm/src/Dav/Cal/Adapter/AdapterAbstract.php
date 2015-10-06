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

use Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper;

/**
 * Abstract class for iCal adapters common functionality
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
abstract class AdapterAbstract
{
    /**
     * map for associations beetwen SugarBean bean and CalDavEvent objects
     * @var array 'Meeting bean property name' => 'function that return data for property'
     */
    protected $importBeanDataMap = array(
        'name' => 'getTitle',
        'description' => 'getDescription',
        'location' => 'getLocation',
        'duration_hours' => 'getDurationHours',
        'duration_minutes' => 'getDurationMinutes'
    );

    /**
     * map for associations beetwen recurring SugarBean bean and CalDavEvent objects
     * @var array 'Meeting bean property name' => 'function that return data for property'
     */
    protected $importRecurringEventsDataMap = array(
        'name' => 'getTitle',
        'description' => 'getDescription',
        'location' => 'getLocation',
        'duration_hours' => 'getDurationHours',
        'duration_minutes' => 'getDurationMinutes'
    );

    /** map for association beetwen CalDavEvent objects and Suagr bean. Uses in export
     * @var array
     */
    protected $exportBeanDataMap = array(
        'setTitle' => 'name',
        'setDescription' => 'description',
        'setLocation' => 'location'
    );


    /**
     * Check if recurring rules was changed for bean
     * @param \SugarBean $sugarBean
     * @param array $RecurringRule
     * @return bool
     */
    protected function isRecurringRulesChangedForBean($sugarBean, $RecurringRule)
    {
        if (isset($RecurringRule['type']) && $sugarBean->repeat_type != $RecurringRule['type']) {
            return true;
        }
        if (isset($RecurringRule['interval']) && $sugarBean->repeat_interval != $RecurringRule['interval']) {
            return true;
        }
        if (isset($RecurringRule['count']) && $sugarBean->repeat_count != $RecurringRule['count']) {
            return true;
        }
        if (isset($RecurringRule['until']) &&
            strtotime($sugarBean->repeat_until) != strtotime($this->getUntilDate($RecurringRule['until']))
        ) {
            return true;
        }
        if (isset($RecurringRule['dow']) && $sugarBean->repeat_dow != $RecurringRule['dow']) {
            return true;
        }
        return false;
    }

    /**
     * @param \SugarBean $sugarBean
     * @param \SugarBean $sourceBean
     * @param array $dataMap
     * @return bool
     */
    protected function setMappedBeanProperties($sugarBean, $sourceBean, $dataMap)
    {
        $isBeanChanged = false;
        foreach ($dataMap as $beanProperty => $calDavMethod) {
            if (method_exists($sourceBean, $calDavMethod)) {
                if ($sugarBean->$beanProperty != $sourceBean->$calDavMethod()) {
                    $sugarBean->$beanProperty = $sourceBean->$calDavMethod();
                    $isBeanChanged = true;
                }
            }
        }
        return $isBeanChanged;
    }

    /**
     * return bean without cache
     * @param \SugarBean $calDavBean
     * @return null|\SugarBean
     */
    protected function getNotCachedBean(\SugarBean $calDavBean)
    {
        return $calDavBean = \BeanFactory::getBean($calDavBean->module_name, $calDavBean->id, array('use_cache' => false));
    }

    /**
     * Indexes an array according to a specified key
     * @param midex $indexKey
     * @param array $object
     * @return array
     */
    protected function arrayIndex($indexKey, $object)
    {
        $result = array();
        foreach ($object as $item) {
            if (is_object($item)) {
                $key = $item->$indexKey;
            } elseif (is_array($item)) {
                $key = $item[$indexKey];
            }

            if ($key) {
                $result[$key] = $item;
            }
        }
        return $result;
    }


    /**
     * return possibilities values for reminders time
     * @param bool|false $force
     * @return array
     */
    protected function getReminderTimeValues($force = false)
    {
        static $reminderKeys = array();
        if (!$reminderKeys || $force) {
            $localAppStrings = return_app_list_strings_language($GLOBALS['current_language']);
            $reminderValues = isset($localAppStrings['reminder_time_options']) ? $localAppStrings['reminder_time_options'] : array();
            $reminderKeys = array_keys($reminderValues);
        }
        return $reminderKeys;
    }

    /**
     * @return string
     */
    protected function getCurrentUserId()
    {
        return $GLOBALS['current_user']->id;
    }

    /**
     * @return string
     */
    protected function getCurrentUserName()
    {
        return $GLOBALS['current_user']->user_name;
    }

    /**
     * @return array|null
     */
    protected function getUserCalendars()
    {
        $userHelper = new UserHelper();
        return $userHelper->getCalendars($this->getCurrentUserName());
    }

    /**
     * return DateTimeHelper
     * @return DateTimeHelper
     */
    protected function getDateTimeHelper()
    {
        return new DateTimeHelper();
    }

    /**
     * @return \CalendarEvents
     */
    protected function getCalendarEvents()
    {
        return new \CalendarEvents();
    }

    /**
     * @param string $untilDate
     * @return string
     */
    protected function getUntilDate($untilDate)
    {
        $dateTimeHelper = $this->getDateTimeHelper();
        return $dateTimeHelper->sugarDateToUserDate($untilDate);
    }

    /**
     * @param string $date
     * @return string
     */
    protected function getDateTimeStart($date)
    {
        $dateTimeHelper = $this->getDateTimeHelper();
        return $dateTimeHelper->sugarDateToUserDateTime($date);
    }

    /**
     * @param \SugarBean $sugarBean
     * @param \CalDavEvent $calDavEvent
     * @param $sabreComponent
     * @return bool
     */
    protected function setExportReminders($sugarBean, \CalDavEvent $calDavEvent, $sabreComponent)
    {
        $isReminderChange = false;
        $calDavReminderValues = $calDavEvent->getReminders();
        if ($sugarBean->reminder_checked === true
            || (isset($calDavReminderValues['DISPLAY']))
        ) {
            if ($calDavEvent->setReminder($sugarBean->reminder_time, $sabreComponent, 'DISPLAY')) {
                $isReminderChange = true;
            }
        }

        if ($sugarBean->email_reminder_checked === true
            || (isset($calDavReminderValues['EMAIL']))
        ) {
            if ($calDavEvent->setReminder($sugarBean->email_reminder_time, $sabreComponent, 'EMAIL')) {
                $isReminderChange = true;
            }
        }

        return $isReminderChange;
    }

    /**
     * set reminder properties to Bean
     * @param array $reminders
     * @param \SugarBean $sugarBeen
     * @return bool
     */
    protected function setReminders($reminders, $sugarBeen)
    {
        $isChanged = false;
        $reminderValues = $this->getReminderTimeValues();
        foreach ($reminders as $key => $reminder) {
            switch ($key) {
                case 'DISPLAY':
                    if ($sugarBeen->reminder_time != $reminder['duration']) {
                        if (in_array($reminder['duration'], $reminderValues)) {
                            $sugarBeen->reminder_time = $reminder['duration'];
                        } else {
                            $sugarBeen->reminder_time = -1;
                        }
                        $sugarBeen->reminder_checked = $sugarBeen->reminder_time == -1 ? false : true;
                        $isChanged = true;
                    }
                    break;
                case 'EMAIL':
                    if ($sugarBeen->email_reminder_time != $reminder['duration']) {
                        if (in_array($reminder['duration'], $reminderValues)) {
                            $sugarBeen->email_reminder_time = $reminder['duration'];
                        } else {
                            $sugarBeen->email_reminder_time = -1;
                        }
                        $sugarBeen->email_reminder_checked = $sugarBeen->email_reminder_time == -1 ? false : true;
                        $isChanged = true;
                    }
                    break;
            }
        }
        return $isChanged;
    }

    /**
     * @param \SugarBean $sugarBean
     * @param \SugarBean $sourceBean
     * @param array $dataMap
     */
    protected function setBeanProperties($sugarBean, $sourceBean, $dataMap)
    {
        $dateTimeHelper = $this->getDateTimeHelper();
        $dateStart = $dateEnd = '';
        $isBeanChanged = false;
        if ($this->setMappedBeanProperties($sugarBean, $sourceBean, $dataMap)) {
            $isBeanChanged = true;
        }

        if ($sugarBean->date_start) {
            $dateStart = $dateTimeHelper->sugarDateToUTC($sugarBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if (!$dateStart || $dateStart !== $sourceBean->getStartDate()) {
            $sugarBean->date_start = $sourceBean->getStartDate();
            $isBeanChanged = true;
        }

        if ($sugarBean->date_end) {
            $dateEnd = $dateTimeHelper->sugarDateToUTC($sugarBean->date_end)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if (!$dateEnd || $dateEnd !== $sourceBean->getEndDate()) {
            $sugarBean->date_end = $sourceBean->getEndDate();
            $isBeanChanged = true;
        }
        return $isBeanChanged;
    }

    /**
     * @param \SugarBean $sugarBean
     * @param array $RecurringRule
     */
    protected function setRecurringRulesToBean($sugarBean, $RecurringRule)
    {
        if (isset($RecurringRule['type'])) {
            $sugarBean->repeat_type = $RecurringRule['type'];
        }
        if (isset($RecurringRule['interval'])) {
            $sugarBean->repeat_interval = $RecurringRule['interval'];
        }
        if (!empty($RecurringRule['count'])) {
            $sugarBean->repeat_count = $RecurringRule['count'];
            $sugarBean->repeat_until = null;
        } elseif (!empty($RecurringRule['until'])) {
            $sugarBean->repeat_until = $this->getUntilDate($RecurringRule['until']);
            $sugarBean->repeat_count = null;
        }
        if (isset($RecurringRule['dow'])) {
            $sugarBean->repeat_dow = $RecurringRule['dow'];
        }
    }

    /**
     * @param $bean
     * @return array
     */
    protected function getCurrentAttributes($bean)
    {
        $beanAttributes = array();
        foreach ($this->importBeanDataMap as $attrKey => $calDavFunction) {
            $beanAttributes[$attrKey] = $bean->$attrKey;
        }
        return $beanAttributes;
    }

    /**
     * @param array $recurringRule
     * @param \SugarBean $sugarBean should be \Meeting or \Call instance for today
     * @param \CalDavEvent $calDavBean
     * @throws \SugarException
     */
    protected function setRecurring($recurringRule, $sugarBean, $calDavBean)
    {
        $isRecurringChanged = false;
        /**@var \CalendarEvents $calendarEvents */
        $calendarEvents = $this->getCalendarEvents();
        $dateTimeHelper = $this->getDateTimeHelper();
        if (!$calDavBean->parent_id) {
            if (!$sugarBean->id) {
                $sugarBean->id = create_guid();
                $sugarBean->new_with_id = true;
            }

            $this->setRecurringRulesToBean($sugarBean, $recurringRule);
            $calendarEvents->saveRecurringEvents($sugarBean);
            $isRecurringChanged = true;
        } else {
            /**
             * check if recurring rules have been changed
             */
            if ($this->isRecurringRulesChangedForBean($sugarBean, $recurringRule)) {
                \CalendarUtils::markRepeatDeleted($sugarBean);
                $this->setRecurringRulesToBean($sugarBean, $recurringRule);
                $calendarEvents->saveRecurringEvents($sugarBean);
                $isRecurringChanged = true;
            }

            $childQuery = $calendarEvents->getChildrenQuery($sugarBean);
            $childEvents = $sugarBean->fetchFromQuery($childQuery);
            $childEventsDateMap = array();
            foreach ($childEvents as $event) {
                $childEventsDateMap[$this->getDateTimeStart($event->date_start)] = $event;
            }

            foreach ($recurringRule['children'] as $calDavChild) {
                $date_start = $this->getDateTimeStart($calDavChild->getStartDate());
                $utcDateStart = $dateTimeHelper->sugarDateToUTC($sugarBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT);
                if ($date_start == $this->getDateTimeStart($utcDateStart)) {
                    continue;
                }

                if (array_key_exists($date_start, $childEventsDateMap)) {
                    $event = $childEventsDateMap[$date_start];
                } else {
                    $generatedId = \CalendarUtils::saveRecurring($sugarBean, array($date_start));
                    if (isset($generatedId[0]['id'])) {
                        $event = \BeanFactory::getBean($sugarBean->module_name, $generatedId[0]['id'], array('cache' => false));
                        $event->repeat_parent_id = $sugarBean->id;
                    }
                }

                if ($event) {
                    if ($this->setMappedBeanProperties($event, $calDavChild, $this->importRecurringEventsDataMap)) {
                        $isRecurringChanged = true;
                    }
                    if ($this->isRecurringRulesChangedForBean($event, $recurringRule)) {
                        $this->setRecurringRulesToBean($event, $recurringRule);
                        $isRecurringChanged = true;
                    }
                    if ($isRecurringChanged) {
                        $event->save();
                    }
                }
            }

            foreach ($recurringRule['deleted'] as $deletedEventDate) {
                $date = $this->getDateTimeStart($deletedEventDate);
                if (array_key_exists($date, $childEventsDateMap)) {
                    $event = $childEventsDateMap[$date];
                    $event->mark_deleted($event->id);
                    $isRecurringChanged = true;
                }
            }

        } //if (!$calDavBean->parent_id)
        return $isRecurringChanged;
    }

    /**
     * @param \SugarBean $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function setRecurringRulesToCalDav(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        $recurringRule['type'] = $sugarBean->repeat_type;
        $recurringRule['interval'] = $sugarBean->repeat_interval;
        $recurringRule['count'] = $sugarBean->repeat_count;
        $recurringRule['until'] = $sugarBean->repeat_until;
        $recurringRule['dow'] = $sugarBean->repeat_dow;

        $calendarEvents = $this->getCalendarEvents();
        $childQuery = $calendarEvents->getChildrenQuery($sugarBean);
        $childEvents = $sugarBean->fetchFromQuery($childQuery);
        $recurringRule['parent'] = $sugarBean;
        $recurringRule['children'] = array();
        foreach ($childEvents as $event) {
            $recurringRule['children'][] = $event;
        }

        if ($calDavBean->setRRule($recurringRule)) {
            return true;
        }

        return false;
    }

    /**
     * Add Contacts or Leads participiants
     * @param array $davParticipants
     * @param \SugarBean $sugarBean
     * @param string $bealRelation
     * @param string $beanMethod
     *
     * @return bool
     */
    protected function addNonUsersParticipants($davParticipants, $sugarBean, $bealRelation, $beanMethod)
    {
        $davParticipants = array_keys($davParticipants);
        sort($davParticipants);
        if ($sugarBean->load_relationship($bealRelation)) {
            $beanParticipants = $sugarBean->$bealRelation->get();
            sort($beanParticipants);

            if ($davParticipants != $beanParticipants && method_exists($sugarBean, $beanMethod)) {
                $sugarBean->$beanMethod($davParticipants);
                return true;
            }
        }

        return false;
    }
}
