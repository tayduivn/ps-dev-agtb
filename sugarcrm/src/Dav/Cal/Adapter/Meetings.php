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

use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract as CalDavAbstractAdapter;

/**
 * Class for processing Meetings by iCal protocol
 *
 * Class Meetings
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Meetings extends CalDavAbstractAdapter implements AdapterInterface
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
            throw new AdapterInvalidArgumentException('Bean must be an instance of Meeting. Instance of '. get_class($sugarBean) .' given');
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
        if (!($sugarBean instanceof \Meeting)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of Meeting. Instance of '. get_class($sugarBean) .' given');
        }
        $isBeanChanged = false;
        $oldAttributes = $this->getCurrentAttributes($sugarBean);
        /**@var \CalDavEvent $calDavBean */
        $calDavBean = $this->getNotCachedBean($calDavBean);

        if (!$sugarBean->assigned_user_id) {
            $sugarBean->assigned_user_id = $this->getCurrentUserId();
            $isBeanChanged = true;
        }

        /**@var \Meeting $sugarBean */
        if ($this->setBeanProperties($sugarBean, $calDavBean, $this->importBeanDataMap)) {
            $isBeanChanged = true;
        }

        $participants = $calDavBean->getParticipants();

        if ($participants) {
            if (!empty($participants['Users'])) {
                $usersParticipants = $participants['Users'];
                $sugarBean->users_arr = array_keys($usersParticipants);
                if (!$sugarBean->id) {
                    $sugarBean->id = create_guid();
                    $sugarBean->new_with_id = true;
                    $isBeanChanged = true;
                }
                $meetingUsers = $this->arrayIndex('id', $sugarBean->get_meeting_users());
                foreach ($usersParticipants as $userId => $partipientInfo) {
                    if ($partipientInfo['accept_status']) {
                        if (!array_key_exists($userId, $meetingUsers) ||
                            $meetingUsers[$userId]->accept_status != $partipientInfo['accept_status']
                        ) {
                            $user = \BeanFactory::getBean('Users', $userId);
                            $sugarBean->set_accept_status($user, $partipientInfo['accept_status']);
                            $isBeanChanged = true;
                        }
                    }
                }
            }

            if (!empty($participants['Contacts'])) {
                $isBeanChanged |= $this->addNonUsersParticipants(
                    $participants['Contacts'],
                    $sugarBean,
                    'contacts',
                    'setContactInvitees'
                );
            }

            if (!empty($participants['Leads'])) {
                $isBeanChanged |= $this->addNonUsersParticipants(
                    $participants['Leads'],
                    $sugarBean,
                    'leads',
                    'setLeadInvitees'
                );
            }
        }

        $reminders = $calDavBean->getReminders();

        if ($reminders) {
            if ($this->setReminders($reminders, $sugarBean)) {
                $isBeanChanged = true;
            }
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
        $calendarEvents = $this->getCalendarEvents();
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

    /**
     * @param \Meeting $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function setRecurringRulesToCalDav(\Meeting $sugarBean, \CalDavEvent $calDavBean)
    {
        $dateTimeHelper = $this->getDateTimeHelper();
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

        $calendarEvents = $this->getCalendarEvents();
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
}
