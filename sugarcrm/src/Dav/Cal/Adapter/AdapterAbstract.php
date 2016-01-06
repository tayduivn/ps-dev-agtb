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

use Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper as ParticipantsHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper as DateTimeHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as CalDavStatus;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event;

/**
 * Abstract class for iCal adapters common functionality
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
abstract class AdapterAbstract implements AdapterInterface
{
    /**
     * @inheritdoc
     * @param \Call|\Meeting|\SugarBean $bean
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false)
    {
        $changedFields = array();
        $inviteesBefore = array();
        $override = true;
        if ($previousData) {
            list($changedFields, $inviteesBefore, $inviteesAfter) = $previousData;
            $override = false;
        } else {
            $inviteesAfter = \CalendarUtils::getInvites($bean);
        }

        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $recurringParam = null;
        $repeatParentId = $bean->repeat_parent_id;

        if (!$repeatParentId) {
            if (($override && $bean->repeat_type)
                ||
                (!$override && $this->isRecurringChanged($changedFields))
            ) {
                $recurringParam = $this->getRecurringHelper()->beanToArray($bean);
            }
        }

        if ($override) {
            $changedFields = $this->getBeanFetchedRow($bean);
        } else {
            $changedFields = $this->getFieldsDiff($changedFields);
        }
        $changedFields = array_intersect_key($changedFields, array(
            'name' => true,
            'location' => true,
            'description' => true,
            'deleted' => true,
            'date_start' => true,
            'date_end' => true,
            'status' => true,
            'repeat_type' => true,
            'repeat_interval' => true,
            'repeat_dow' => true,
            'repeat_until' => true,
            'repeat_count' => true,
            'repeat_parent_id' => true,
        ));

        $changedInvites = $participantsHelper->getInvitesDiff($inviteesBefore, $inviteesAfter);

        if (!$changedFields && !$changedInvites) {
            return false;
        }

        $beanData = array(
            $bean->module_name,
            $bean->id,
            $repeatParentId,
            $recurringParam,
            $override,
        );

        return array($beanData, $changedFields, $changedInvites);
    }

    /**
     * @inheritDoc
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false)
    {
        if ($previousData) {
            return $collection->getDiffStructure($previousData);
        }
        return $collection->getDiffStructure('');
    }

    /**
     * @inheritDoc
     */
    public function verifyImportAfterExport(array $exportData, array $importData, \CalDavEventCollection $collection)
    {
        if (!$importData) {
            return false;
        }
        list($exportBean, $exportFields, $exportInvites) = $exportData;
        list($importBean, $importFields, $importInvites) = $importData;

        $this->filterFieldsOnVerify($exportFields, $importFields);

        foreach ($importFields as $field => $diff) {
            if (isset($diff[1])) {
                continue;
            }
            if ($diff[0] === null) {
                unset($importFields[$field]);
            }
        }

        foreach ($importInvites as $action => $list) {
            if (empty($exportInvites[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($exportInvites[$action] as $exportInvitee) {
                    $invitee = $exportInvitee;
                    $invitee[2] = $importInvitee[2]; // we don't care about real status
                    if ($importInvitee === $invitee) {
                        unset($importInvites[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$importInvites[$action]) {
                unset($importInvites[$action]);
            }
        }

        if ($importFields || $importInvites) {
            return array(
                $importBean,
                $importFields,
                $importInvites,
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function verifyExportAfterImport(array $importData, array $exportData, \SugarBean $bean)
    {
        list($exportBean, $exportFields, $exportInvites) = $exportData;
        list($importBean, $importFields, $importInvites) = $importData;

        $this->filterFieldsOnVerify($exportFields, $importFields);

        foreach ($exportFields as $field => $diff) {
            if (count($diff) > 1) {
                continue;
            }
            if ($diff[0] === null) {
                unset($exportFields[$field]);
            }
        }

        foreach ($exportInvites as $action => $list) {
            if (empty($importInvites[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($importInvites[$action] as $exportInvitee) {
                    $invitee = $exportInvitee;
                    $invitee[2] = $importInvitee[2]; // we don't care about real status
                    if ($importInvitee === $invitee) {
                        unset($exportInvites[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$exportInvites[$action]) {
                unset($exportInvites[$action]);
            }
        }

        if ($exportFields || $exportInvites) {
            return array(
                $exportBean,
                $exportFields,
                $exportInvites,
            );
        }

        return false;
    }

    /**
     * Filter fields on verifyExportAfterImport and verifyImportAfterExport
     * @param array $exportFields
     * @param array $importFields
     */
    protected function filterFieldsOnVerify(array &$exportFields, array &$importFields)
    {
        if (isset($exportFields['name']) && isset($importFields['title'])) {
            if ($exportFields['name'][0] == $importFields['title'][0]) {
                unset($exportFields['name']);
                unset($importFields['title']);
            }
        }
        if (isset($exportFields['deleted']) && isset($importFields['deleted'])) {
            if ($exportFields['deleted'][0] == $importFields['deleted'][0]) {
                unset($exportFields['deleted']);
                unset($importFields['deleted']);
            }
        }
        if (isset($exportFields['location']) && isset($importFields['location'])) {
            if ($exportFields['location'][0] == $importFields['location'][0]) {
                unset($exportFields['location']);
                unset($importFields['location']);
            }
        }
        if (isset($exportFields['description']) && isset($importFields['description'])) {
            if ($exportFields['description'][0] == $importFields['description'][0]) {
                unset($exportFields['description']);
                unset($importFields['description']);
            }
        }
        if (isset($exportFields['status']) && isset($importFields['status'])) {
            $map = new CalDavStatus\EventMap();
            $status = $map->getSugarValue($importFields['status'][0], $exportFields['status'][0]);
            if ($exportFields['status'][0] == $status) {
                unset($exportFields['status']);
                unset($importFields['status']);
            }
        }
        if (isset($exportFields['date_start']) && isset($importFields['date_start'])) {
            if ($exportFields['date_start'][0] == $importFields['date_start'][0]) {
                unset($exportFields['date_start']);
                unset($importFields['date_start']);
            }
        }
        if (isset($exportFields['date_end']) && isset($importFields['date_end'])) {
            if ($exportFields['date_end'][0] == $importFields['date_end'][0]) {
                unset($exportFields['date_end']);
                unset($importFields['date_end']);
            }
        }

        if (isset($exportFields['repeat_type']) && isset($importFields['rrule']['frequency'])) {
            $frequencyMap = new CalDavStatus\IntervalMap();
            $sugarValue = $frequencyMap->getSugarValue($importFields['rrule']['frequency'][0]);
            if ($exportFields['repeat_type'][0] == $sugarValue) {
                unset($exportFields['repeat_type']);
                unset($importFields['rrule']['frequency']);
            }
        }

        if (isset($exportFields['repeat_count']) && isset($importFields['rrule']['count'])) {
            if ($exportFields['repeat_count'][0] == $importFields['rrule']['count'][0]) {
                unset($exportFields['repeat_count']);
                unset($importFields['rrule']['count']);
            }
        }

        if (isset($exportFields['repeat_interval']) && isset($importFields['rrule']['interval'])) {
            if ($exportFields['repeat_interval'][0] == $importFields['rrule']['interval'][0]) {
                unset($exportFields['repeat_interval']);
                unset($importFields['rrule']['interval']);
            }
        }

        if (isset($exportFields['repeat_until']) && isset($importFields['rrule']['until'])) {
            if ($exportFields['repeat_until'][0] == $importFields['rrule']['until'][0]) {
                unset($exportFields['repeat_until']);
                unset($importFields['rrule']['until']);
            }
        }

        if (isset($exportFields['repeat_dow']) && isset($importFields['rrule']['byday'])) {
            $sugarValue = '';
            $dayMap = new CalDavStatus\DayMap();
            foreach ($importFields['rrule']['byday'][0] as $day) {
                $sugarValue .= $dayMap->getSugarValue($day);
            }

            if ($exportFields['repeat_dow'][0] == $sugarValue) {
                unset($exportFields['repeat_dow']);
                unset($importFields['rrule']['byday']);
            }
        }

        if (isset($importFields['rrule']) && count($importFields['rrule']) == 1) {
            unset($importFields['rrule']);
        }
    }

    /**
     * return true if one of rucurrig rules was changed
     * @param array $changedFields
     * @return bool
     */
    protected function isRecurringChanged($changedFields)
    {
        return (bool)array_intersect(array_keys($changedFields), RecurringHelper::$recurringFieldList);
    }

    /**
     * get fields list with before (if exists) and after values of field
     * @param array $changedFields
     * @return mixed
     */
    protected function getFieldsDiff($changedFields)
    {
        $dataDiff = array();
        foreach ($changedFields as $field => $fieldValues) {
            $dataDiff[$field] = array(
                0 => $fieldValues['after'],
            );
            if ($fieldValues['before']) {
                $dataDiff[$field][1] = $fieldValues['before'];
            }
        }
        return $dataDiff;
    }

    /**
     * Get Event to work
     * @param \CalDavEventCollection $collection
     * @param $repeatParentId
     * @param $beanId
     * @return null|Event
     */
    protected function getCurrentEvent(\CalDavEventCollection $collection, $repeatParentId, $beanId)
    {
        if (!$repeatParentId) {
            return $collection->getParent();
        }

        $sugarChildrenIds = $collection->getSugarChildrenOrder();
        $eventIndex = array_search($beanId, $sugarChildrenIds);

        if ($eventIndex === false) {
            return null;
        }

        $davChildren = array_values($collection->getAllChildrenRecurrenceIds());
        if (!isset($davChildren[$eventIndex])) {
            return null;
        }

        return $collection->getChild($davChildren[$eventIndex]);
    }

    /**
     * Get bean (call or meeting) to work
     * @param \SugarBean $bean
     * @param \CalDavEventCollection $calDavBean
     * @param array $processedData
     * @return \SugarBean
     */
    public function getBeanForImport(\SugarBean $bean, \CalDavEventCollection $calDavBean, array $processedData)
    {
        list($beanData, $changedFields, $invites) = $processedData;
        list($beanId, $childEventsId, $recurrenceId, $recurrenceIndex, $insert) = $beanData;

        if (is_null($recurrenceIndex)) {
            return $bean;
        }

        if (!$childEventsId) {
            $childEventsId = $calDavBean->getSugarChildrenOrder();
        }

        if ($childEventsId) {
            return \BeanFactory::getBean(
                $bean->module_name,
                $childEventsId[$recurrenceIndex],
                array('strict_retrieve' => true)
            );
        }

        return null;
    }

    /**
     * Retrieve bean fetched row
     * If bean not saved yet we should make array from bean
     * @param \SugarBean $bean
     * @return array
     */
    protected function getBeanFetchedRow(\SugarBean $bean)
    {
        $dataDiff = array();
        $fetchedRow = $bean->fetched_row;
        if (!$fetchedRow) {
            if ($bean->isUpdate() && $bean->retrieve($bean->id)) {
                $fetchedRow = $bean->fetched_row;
            } else {
                $fetchedRow = $bean->toArray(true);
            }
        }

        foreach ($fetchedRow as $name => $value) {
            $dataDiff[$name] = array(
                0 => $value
            );
        }
        return $dataDiff;
    }

    /**
     * Checks that title matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavTitle($value, Event $event)
    {
        return $event->getTitle() == $value;
    }

    /**
     * Checks that description matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavDescription($value, Event $event)
    {
        return $event->getDescription() == $value;
    }

    /**
     * Checks that location matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavLocation($value, Event $event)
    {
        return $event->getLocation() == $value;
    }

    /**
     * Checks that status matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavStatus($value, Event $event)
    {
        $map = new CalDavStatus\EventMap();
        return $event->getStatus() == $map->getCalDavValue($value, $event->getStatus());
    }

    /**
     * Checks that start date matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavStartDate($value, Event $event)
    {
        return $event->getStartDate() == new \SugarDateTime($value, new \DateTimeZone('UTC'));
    }

    /**
     * Checks that end date matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavEndDate($value, Event $event)
    {
        return $event->getEndDate() == new \SugarDateTime($value, new \DateTimeZone('UTC'));
    }

    /**
     * Checks that invites are applicable to current ones.
     *
     * @param array $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavInvites($value, Event $event)
    {
        if (isset($value['added'])) {
            foreach ($value['added'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[3]) != -1) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[3]) == - 1) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[3]) == -1) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Checks that recurring rules matches to current
     * @param $value
     * @param \CalDavEventCollection $collection
     * @return bool
     */
    protected function checkCalDavRecurring($value, \CalDavEventCollection $collection)
    {
        $currentRule = $collection->getRRule();
        if (!$currentRule) {
            return true;
        }

        $frequencyMap = new CalDavStatus\IntervalMap();
        $dayMap = new CalDavStatus\DayMap();

        if (isset($value['repeat_type'][1]) && ($currentRule->getFrequency() !=
                $frequencyMap->getCalDavValue($value['repeat_type'][1], $currentRule->getFrequency()))
        ) {
            return false;
        }

        if (isset($value['repeat_interval'][1]) && ($currentRule->getInterval() != $value['repeat_interval'][1])) {
            return false;
        }

        if (isset($value['repeat_count'][1]) && ($currentRule->getCount() != $value['repeat_count'][1])) {
            return false;
        }

        if (isset($value['repeat_until'][1]) && ($value['repeat_until'][1] != $currentRule->getUntil()->asDbDate())) {
            return false;
        }

        if (isset($value['repeat_dow'][1])) {
            $converted = array();
            if ($value['repeat_dow'][1]) {
                $aDow = str_split($value['repeat_dow'][1]);
                foreach ($aDow as $value) {
                    $converted[] = $dayMap->getCalDavValue($value);
                }
            }
            if (array_diff($converted, $currentRule->getByDay())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets title to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavTitle($value, Event $event)
    {
        return $event->setTitle($value);
    }

    /**
     * Sets description to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavDescription($value, Event $event)
    {
        return $event->setDescription($value);
    }

    /**
     * Sets location to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavLocation($value, Event $event)
    {
        return $event->setLocation($value);
    }

    /**
     * Maps and sets sugar status to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavStatus($value, Event $event)
    {
        $map = new CalDavStatus\EventMap();
        $value = $map->getCalDavValue($value, $event->getStatus());
        return $event->setStatus($value);
    }

    /**
     * Sets start date to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavStartDate($value, Event $event)
    {
        $value = new \SugarDateTime($value, new \DateTimeZone('UTC'));
        return $event->setStartDate($value);
    }

    /**
     * Sets end date to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavEndDate($value, Event $event)
    {
        $value = new \SugarDateTime($value, new \DateTimeZone('UTC'));
        return $event->setEndDate($value);
    }

    /**
     * Sets provided invites to specified event.
     *
     * @param array $value
     * @param Event $event
     * @param bool $override
     * @return bool
     */
    protected function setCalDavInvites(array $value, Event $event, $override = false)
    {
        $result = false;
        $participantHelper = $this->getParticipantHelper();

        if ($override) {
            $indexes = array();
            $value['changed'] = array();
            $value['deleted'] = array();
            foreach ($value['added'] as $k => $invitee) {
                $index = $event->findParticipantsByEmail($invitee[3]);
                if ($index != -1) {
                    $indexes[] = $index;
                    $value['changed'][] = $invitee;
                    unset($value['added'][$k]);
                }
            }
            foreach ($event->getParticipants() as $k => $participant) {
                if (!in_array($k, $indexes)) {
                    $value['deleted'][] = $participantHelper->participantToInvite($participant);
                }
            }
            $value = array_filter($value);
        }

        if (isset($value['added'])) {
            foreach ($value['added'] as $invitee) {
                $result |= $event->setParticipant($participantHelper->inviteToParticipant($invitee));
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invitee) {
                $result |= $event->setParticipant($participantHelper->inviteToParticipant($invitee));
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invitee) {
                $result |= $event->deleteParticipant($invitee[3]);
            }
        }
        if (!$event->getOrganizer() && $GLOBALS['current_user'] instanceof \User) {
            $email = $GLOBALS['current_user']->emailAddress->getPrimaryAddress($GLOBALS['current_user']);
            $participant = $event->findParticipantsByEmail($email);
            if ($participant == -1) {
                $participant = $participantHelper->inviteToParticipant(array(
                    $GLOBALS['current_user']->module_name,
                    $GLOBALS['current_user']->id,
                    'accept',
                    $email,
                    $GLOBALS['current_user']->full_name,
                ));
            } else {
                $participants = $event->getParticipants();
                $participant = $participants[$participant];
            }
            $event->setOrganizer($participant);
        }

        return $result;
    }

    /**
     * Set recurring to caldav
     * @param array $value
     * @param \CalDavEventCollection $collection
     * @return bool
     */
    protected function setCalDavRecurring(array $value, \CalDavEventCollection $collection)
    {
        if (empty($value['repeat_type'])) {
            return $collection->setRRule(null);
        }

        $rRule = $this->getRecurringHelper()->arrayToRRule($value);

        return $collection->setRRule($rRule);
    }

    /**
     * Checks that name matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanName($value, \SugarBean $bean)
    {
        return $bean->name == $value;
    }

    /**
     * Checks that description matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanDescription($value, \SugarBean $bean)
    {
        return $bean->description == $value;
    }

    /**
     * Checks that location matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting $bean
     * @return bool
     */
    protected function checkBeanLocation($value, \SugarBean $bean)
    {
        return $bean->location == $value;
    }

    /**
     * Checks that status matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanStatus($value, \SugarBean $bean)
    {
        $map = new CalDavStatus\EventMap();
        return $bean->status == $map->getSugarValue($value, $bean->status);
    }

    /**
     * Checks that start date matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanStartDate($value, \SugarBean $bean)
    {
        $beanDate = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start);
        return $beanDate->asDb() == $value;
    }

    /**
     * Checks that end date matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanEndDate($value, \SugarBean $bean)
    {
        $beanDate = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end);
        return $beanDate->asDb() == $value;
    }

    /**
     * Checks that invites are applicable to current ones.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanInvites($value, \SugarBean $bean)
    {
        $definitions = \VardefManager::getFieldDefs($bean->module_name);
        if (isset($definitions['invitees']['links'])) {
            $links = $definitions['invitees']['links'];
        } else {
            $links = array();
        }

        $existingLinks = array();
        foreach ($links as $link) {
            if ($bean->load_relationship($link)) {
                foreach ($bean->$link->getBeans() as $existingBean) {
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }
            }
        }

        if (isset($value['added'])) {
            foreach ($value['added'] as $invitee) {
                if (isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invitee) {
                if (!isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invitee) {
                if (!isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sets name to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanName($value, \SugarBean $bean)
    {
        if ($value != $bean->name) {
            $bean->name = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets description to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanDescription($value, \SugarBean $bean)
    {
        if ($value != $bean->description) {
            $bean->description = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets location to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting $bean
     * @return bool
     */
    protected function setBeanLocation($value, \SugarBean $bean)
    {
        if ($value != $bean->location) {
            $bean->location = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets status to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanStatus($value, \SugarBean $bean)
    {
        $map = new CalDavStatus\EventMap();
        $value = $map->getSugarValue($value, $bean->status);

        if ($value != $bean->status) {
            $bean->status = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets start date to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanStartDate($value, \SugarBean $bean)
    {
        if ($value != $bean->date_start) {
            $bean->date_start = $value;
            if ($bean->date_end) {
                $beanDateStart = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start);
                $beanDateEnd = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end);
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->i;
            }
            return true;
        }
        return false;
    }

    /**
     * Sets end date to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanEndDate($value, \SugarBean $bean)
    {
        if ($value != $bean->date_end) {
            $bean->date_end = $value;
            if ($bean->date_start) {
                $beanDateStart = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start);
                $beanDateEnd = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end);
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->i;
            }
            return true;
        }
        return false;
    }

    /**
     * Check bean recurring for conflicts
     * @param array $value
     * @param \SugarBean $bean
     * @return bool
     */
    protected function checkBeanRecurrence(array $value, \SugarBean $bean)
    {
        $frequencyMap = new CalDavStatus\IntervalMap();
        $dayMap = new CalDavStatus\DayMap();

        if (isset($value['frequency'][1]) && $bean->repeat_type !=  $frequencyMap->getSugarValue($value['frequency'][1])) {
            return false;
        }

        if (isset($value['interval'][1]) && $bean->repeat_interval != $value['interval'][1]) {
            return false;
        }

        if (isset($value['count'][1]) && $bean->repeat_count != $value['count'][1]) {
            return false;
        }

        if (isset($value['until'][1]) && $bean->repeat_until != $value['until'][1]) {
            return false;
        }

        if (isset($value['byday'][1])) {
            $sugarValue = '';
            foreach ($value['byday'][1] as $day) {
                $sugarValue .= $dayMap->getSugarValue($day);
            }

            if ($bean->repeat_dow != $sugarValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set bean recurring rule
     * @param array $value
     * @param \SugarBean $bean
     * @return bool
     */
    protected function setBeanRecurrence(array $value, \SugarBean $bean)
    {
        $calendarEvents = $this->getCalendarEvents();
        if ($value['action'] == 'deleted') {
            $bean->repeat_type = '';
            $bean->repeat_interval = 0;
            $bean->repeat_count = 0;
            $bean->repeat_until = '';
            $bean->repeat_dow = '';
            $calendarEvents->markRepeatDeleted($bean);
            return true;
        }

        $frequencyMap = new CalDavStatus\IntervalMap();
        $dayMap = new CalDavStatus\DayMap();

        if (isset($value['frequency'])) {
            $bean->repeat_type =  $frequencyMap->getSugarValue($value['frequency'][0]);
        }

        if (isset($value['interval'])) {
            $bean->repeat_interval = $value['interval'][0];
        }

        if (isset($value['count'])) {
            $bean->repeat_count = $value['count'][0];
        }

        if (isset($value['until'])) {
            $bean->repeat_until = $value['until'][0];
        }

        if (isset($value['byday'])) {
            $sugarValue = '';
            foreach ($value['byday'][0] as $day) {
                $sugarValue .= $dayMap->getSugarValue($day);
            }

            $bean->repeat_dow = $sugarValue;
        }

        $calendarEvents->saveRecurringEvents($bean);

        return true;
    }

    /**
     * Sets provided invites to specified bean.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @param bool $override
     * @return bool
     */
    protected function setBeanInvites(array $value, \SugarBean $bean, $override = false)
    {
        $result = false;

        $definitions = \VardefManager::getFieldDefs($bean->module_name);
        if (isset($definitions['invitees']['links'])) {
            $links = $definitions['invitees']['links'];
        } else {
            $links = array();
        }

        $existingLinks = array();
        foreach ($links as $link) {
            if ($bean->load_relationship($link)) {
                foreach ($bean->$link->getBeans() as $existingBean) {
                    if (!isset($existingLinks[$existingBean->module_name])) {
                        $existingLinks[$existingBean->module_name] = array();
                    }
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }
            }
        }
        if ($override) {
            $value['changed'] = array();
            $value['deleted'] = array();
            $indexes = array();
            foreach ($value['added'] as $k => $invitee) {
                if (isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    $indexes[] = array($invitee[0], $invitee[1]);
                    $value['changed'][] = $invitee;
                    unset($value['added'][$k]);
                }
            }
            foreach ($existingLinks as $moduleName => $ids) {
                foreach ($ids as $id => $_) {
                    $found = false;
                    foreach ($indexes as $index) {
                        if ($index[0] == $moduleName && $index[1] == $id) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $value['deleted'][] = array($moduleName, $id);
                    }
                }
            }
            $value = array_filter($value);
        }

        $map = new CalDavStatus\AcceptedMap();
        if (isset($value['added'])) {
            foreach ($value['added'] as $invitee) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invitee;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                    $result = true;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invitee) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invitee;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                    $result = true;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invitee) {
                if (isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    unset($existingLinks[$invitee[0]][$invitee[1]]);
                    $result = true;
                }
            }
            foreach ($existingLinks as $module => $ids) {
                $objectName = \BeanFactory::getObjectName($module);
                if (!$objectName || !method_exists($bean, 'set' . $objectName . 'Invitees')) {
                    continue;
                }
                call_user_func_array(array($bean, 'set' . $objectName . 'Invitees'), array(
                    array_keys($ids),
                    array(
                        0 => true, // trick to delete everybody if $ids is empty
                    ),
                ));
            }
        }

        return $result;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
     */
    protected function getParticipantHelper()
    {
        return new ParticipantsHelper();
    }

    /**
     * @return \CalendarEvents
     */
    protected function getCalendarEvents()
    {
        return new \CalendarEvents();
    }

    /**
     * @return DateTimeHelper
     */
    protected function getDateTimeHelper()
    {
        return new DateTimeHelper();
    }

    /**
     * @return RecurringHelper
     */
    protected function getRecurringHelper()
    {
        return new RecurringHelper();
    }
}
