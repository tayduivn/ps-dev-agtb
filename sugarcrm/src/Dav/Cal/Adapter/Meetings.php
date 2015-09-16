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
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;

/**
 * Class Meetings
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Meetings implements AdapterInterface
{
    /**
     * map for associations beetwen Meeting bean and CalDavEvent objects
     * @var array 'Meeting bean property name' => 'function that return data for property'
     */
    protected $importBeanDataMap = array(
        'name' => 'getTitle',
        'description' => 'getDescription',
        'location' => 'getLocation',
        'duration_hours' => 'getDurationHours',
        'duration_minutes' => 'getDurationMinutes'
    );

    protected $importRecurringEventsDataMap = array(
        'name' => 'getTitle',
        'description' => 'getDescription',
        'location' => 'getLocation',
        'duration_hours' => 'getDurationHours',
        'duration_minutes' => 'getDurationMinutes'
    );

    protected $exportBeanDataMap = array(
        'setTitle' => 'name',
        'setDescription' => 'description',
        'setLocation' => 'location'
    );

    public function export(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        if (!($sugarBean instanceof \Meeting)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of SugarBean. Instance of '. get_class($sugarBean) .' given');
        }
        $dateTimeHelper = new DateTimeHelper();
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
        if (!($sugarBean instanceof \Meeting)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of SugarBean. Instance of '. get_class($sugarBean) .' given');
        }
        $isBeanChanged = false;
        $oldAttributes = $this->getCurrentAttributes($sugarBean);
        /**@var \CalDavEvent $calDavBean */
        $calDavBean = $this->getNotCachedBean($calDavBean);
        /**@var \Meeting $sugarBean */
        if ($this->setBeanProperties($sugarBean, $calDavBean, $this->importBeanDataMap)) {
            $isBeanChanged = true;
        }

        $participants = $calDavBean->getParticipants();

        if ($participants) {
            $sugarBean->users_arr = array_keys($participants);
            if (!$sugarBean->id) {
                $sugarBean->id = create_guid();
                $sugarBean->new_with_id = true;
                $isBeanChanged = true;
            }
            $meetingUsers = $this->arrayIndex('id', $sugarBean->get_meeting_users());
            foreach ($participants as $userId => $partipientInfo) {
                if ($partipientInfo['accept_status']) {
                    if (!array_key_exists($userId, $meetingUsers) || $meetingUsers[$userId]->accept_status != $partipientInfo['accept_status']) {
                        $user = \BeanFactory::getBean('Users', $userId);
                        $sugarBean->set_accept_status($user, $partipientInfo['accept_status']);
                        $isBeanChanged = true;
                    }
                }
            }
        }

        $reminders = $calDavBean->getReminders();

        if ($reminders) {
            if ($this->setReminders($reminders, $sugarBean)) {
                $isBeanChanged = true;
            }
        }

        if (!$sugarBean->assigned_user_id && $sugarBean->assigned_user_id != $this->getCurrentUserId()) {
            $sugarBean->assigned_user_id = $this->getCurrentUserId();
            $isBeanChanged = true;
        }

        $recurringRule = $calDavBean->getRRule();
        if ($recurringRule) {
            if ($this->setRecurring($recurringRule, $sugarBean, $calDavBean)) {
                $isBeanChanged = true;
            }
        }

        if (!$isBeanChanged) {
            if (array_diff_assoc($oldAttributes, $this->getCurrentAttributes($sugarBean))) {
                $isBeanChanged = true;
            }
        }

        return $isBeanChanged;
    }

    /**
     * @param array $recurringRule
     * @param \Meeting $sugarBean
     * @param \CalDavEvent $calDavBean
     * @throws \SugarException
     */
    protected function setRecurring($recurringRule, $sugarBean, $calDavBean)
    {
        $isRecurringChanged = false;
        /**@var \CalendarEvents $calendarEvents */
        $calendarEvents = new \CalendarEvents();
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
                if ($date_start == $this->getDateTimeStart($sugarBean->date_start)) {
                    continue;
                }

                if (array_key_exists($date_start, $childEventsDateMap)) {
                    $event = $childEventsDateMap[$date_start];
                } else {
                    $generatedId = \CalendarUtils::saveRecurring($sugarBean, array($date_start));
                    if (isset($generatedId[0]['id'])) {
                        /** @var \Meeting $event */
                        $event = \BeanFactory::getBean('Meetings', $generatedId[0]['id'], array('cache' => false));
                        $event->repeat_parent_id = $sugarBean->id;
                    }
                }

                if ($event) {
                    if ($this->setMappedBeanProperties($event, $calDavBean, $this->importRecurringEventsDataMap)) {
                        $isRecurringChanged = true;
                    }
                    if ($this->isRecurringRulesChangedForBean($event, $recurringRule)) {
                        $this->setRecurringRulesToBean($event, $recurringRule);
                        $isRecurringChanged = true;
                    }
                    $event->save();
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

    public function setRecurringRulesToCalDav(\Meeting $sugarBean, \CalDavEvent $calDavBean)
    {
        $dateTimeHelper = new DateTimeHelper();
        $isChanged = false;
        $recurringRule = $recurringRuleOriginal = $calDavBean->getRRule();
        if ($recurringRule === null) {
            $recurringRule = $recurringRuleOriginal = array();
        }
        if (!isset($recurringRuleOriginal['children'])) {
            $recurringRuleOriginal['children'] = array();
        }
        if (!isset($recurringRule['type']) || $sugarBean->repeat_type != $recurringRule['type']) {
            $recurringRule['type'] = $sugarBean->repeat_type;
            $isChanged = true;
        }
        if (!isset($recurringRule['interval']) || $sugarBean->repeat_type != $recurringRule['interval']) {
            $recurringRule['interval'] = $sugarBean->repeat_interval;
            $isChanged = true;
        }
        if (!isset($recurringRule['count']) || $sugarBean->repeat_type != $recurringRule['count']) {
            $recurringRule['count'] = $sugarBean->repeat_count;
            $isChanged = true;
        }
        if (!isset($recurringRule['until']) || $sugarBean->repeat_until != $this->getUntilDate($recurringRule['until'])) {
            $recurringRule['until'] = $dateTimeHelper->sugarDateToDav($sugarBean->repeat_until)->format(\TimeDate::DB_DATE_FORMAT);
            $isChanged = true;
        }
        if (!isset($recurringRule['dow']) || $sugarBean->repeat_type != $recurringRule['dow']) {
            $recurringRule['dow'] = $sugarBean->repeat_dow;
            $isChanged = true;
        }

        $calendarEvents = new \CalendarEvents();
        $childQuery = $calendarEvents->getChildrenQuery($sugarBean);
        $childEvents = $sugarBean->fetchFromQuery($childQuery);
        $recurringRule['children'] = array();
        foreach ($childEvents as $event) {
            $recurringRule['children'][$event->date_start] = $event;
        }

        if (!array_diff(array_keys($recurringRule['children']), array_keys($recurringRuleOriginal['children']))) {
            $isChanged = true;
        }
        if (!isset($recurringRule['parent']) || $recurringRule['parent']->id != $sugarBean->id) {
            $recurringRule['parent'] = $sugarBean;
            $isChanged = true;
        }
        $calDavBean->setRRule($recurringRule);
        return $isChanged;
    }

    /**
     * @param string $untilDate
     * @return string
     */
    protected function getUntilDate($untilDate)
    {
        $dateTimeHelper = new DateTimeHelper;
        return $dateTimeHelper->sugarDateToUserDate($untilDate);
    }

    /**
     * @param string $date
     * @return string
     */
    protected function getDateTimeStart($date)
    {
        $dateTimeHelper = new DateTimeHelper;
        return $dateTimeHelper->sugarDateToUserDateTime($date);
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
        if (isset($RecurringRule['count'])) {
            $sugarBean->repeat_count = $RecurringRule['count'];
        }
        if (isset($RecurringRule['until'])) {
            $sugarBean->repeat_until = $this->getUntilDate($RecurringRule['until']);
        }
        if (isset($RecurringRule['dow'])) {
            $sugarBean->repeat_dow = $RecurringRule['dow'];
        }
    }

    /**
     * @param \SugarBean $sugarBean
     * @param \SugarBean $sourceBean
     * @param array $dataMap
     */
    protected function setBeanProperties($sugarBean, $sourceBean, $dataMap)
    {
        $dateTimeHelper = new DateTimeHelper();
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
     * set reminder properties to Bean
     * @param array $reminders
     * @param \Meeting $meetingBean
     * @return bool
     */
    protected function setReminders($reminders, $meetingBean)
    {
        $isChanged = false;
        $reminderValues = $this->getReminderTimeValues();
        foreach ($reminders as $key => $reminder) {
            switch ($key) {
                case 'DISPLAY':
                    if ($meetingBean->reminder_time != $reminder['duration']) {
                        if (in_array($reminder['duration'], $reminderValues)) {
                            $meetingBean->reminder_time = $reminder['duration'];
                        } else {
                            $meetingBean->reminder_time = -1;
                        }
                        $meetingBean->reminder_checked = $meetingBean->reminder_time == -1 ? false : true;
                        $isChanged = true;
                    }
                    break;
                case 'EMAIL':
                    if ($meetingBean->email_reminder_time != $reminder['duration']) {
                        if (in_array($reminder['duration'], $reminderValues)) {
                            $meetingBean->email_reminder_time = $reminder['duration'];
                        } else {
                            $meetingBean->email_reminder_time = -1;
                        }
                        $meetingBean->email_reminder_checked = $meetingBean->email_reminder_time == -1 ? false : true;
                        $isChanged = true;
                    }
                    break;
            }
        }
        return $isChanged;
    }

    protected function setExportReminders($sugarBean, \CalDavEvent $calDavEvent, $sabreComponent)
    {
        $isReminderChange = false;
        $calDavReminderValues = $calDavEvent->getReminders();
        if ($sugarBean->reminder_checked === true
            || (isset($calDavReminderValues['DISPLAY']) && $sugarBean->reminder_time != $calDavReminderValues['DISPLAY']['duration'])
            ) {
            $calDavEvent->setReminder($sugarBean->reminder_time, $sabreComponent, 'DISPLAY');
            $isReminderChange = true;
        }

        if ($sugarBean->email_reminder_checked === true
            || (isset($calDavReminderValues['EMAIL']) && $sugarBean->email_reminder_time != $calDavReminderValues['EMAIL']['duration'])
            ) {
            $calDavEvent->setReminder($sugarBean->email_reminder_time, $sabreComponent, 'EMAIL');
            $isReminderChange = true;
        }

        return $isReminderChange;
    }

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
        if (isset($RecurringRule['until']) && $sugarBean->repeat_until != $this->getUntilDate($RecurringRule['until'])) {
            return true;
        }
        if (isset($RecurringRule['dow']) && $sugarBean->repeat_dow != $RecurringRule['dow']) {
            return true;
        }
        return false;
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
}
