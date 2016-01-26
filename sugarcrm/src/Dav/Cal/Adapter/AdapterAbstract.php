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
     * @inheritDoc
     * @param \Call|\Meeting|\SugarBean $bean
     * @param array|false $previousData
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false)
    {
        $changedFields = array();
        $inviteesBefore = array();
        $inviteesAfter = array();
        $action = 'override';
        if ($previousData) {
            $action = array_shift($previousData);
        }

        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $recurringParam = null;
        $rootBeanId = null;
        if (!$bean->updateAllChildren && $this->getCalendarEvents()->isEventRecurring($bean)) {
            $rootBeanId = $bean->repeat_root_id;
        }

        if (!$rootBeanId) {
            switch ($action) {
                case 'override' :
                    if ($bean->repeat_type) {
                        $recurringParam = $this->getRecurringHelper()->beanToArray($bean);
                    }
                    break;
                case 'update' :
                    list($changedFields, $inviteesBefore, $inviteesAfter) = $previousData;
                    if ($this->isRecurringChanged($changedFields)) {
                        $recurringParam = $this->getRecurringHelper()->beanToArray($bean);
                        $action = 'override';
                    } elseif ($bean->updateAllChildren && $this->getCalendarEvents()->isEventRecurring($bean)) {
                        $action = 'override';
                    }
                    break;
            }
        }

        switch ($action) {
            case 'override' :
                $inviteesAfter = \CalendarUtils::getInvitees($bean);
                $changedFields = $this->getBeanFetchedRow($bean);
                if (!$changedFields['repeat_type'][0]) {
                    $changedFields['repeat_interval'][0] = null;
                }
                break;
            case 'update' :
                $changedFields = $this->getFieldsDiff($changedFields);
                if ($bean->updateAllChildren) { // no validation is needed in that case
                    foreach ($changedFields as $field => $value) {
                        if (count($value) == 2) {
                            unset($changedFields[$field][1]);
                        }
                    }
                }
                break;
        }

        $beanData = array(
            $action,
            $bean->module_name,
            $bean->id,
            $rootBeanId,
            $recurringParam,
        );

        if ($action == 'delete') {
            return array(array($beanData, array(), array()));
        }

        $changedFieldsFilter = array(
            'name' => true,
            'location' => true,
            'description' => true,
            'date_start' => true,
            'date_end' => true,
            'status' => true,
            'repeat_parent_id' => true,
        );

        if (!$rootBeanId) {
            $changedFieldsFilter =
                array_merge($changedFieldsFilter, array_fill_keys(RecurringHelper::$recurringFieldList, true));
        }

        $changedFields = array_intersect_key($changedFields, $changedFieldsFilter);

        $changedInvitees = $participantsHelper->getInviteesDiff($inviteesBefore, $inviteesAfter);

        if (!$changedFields && !$changedInvitees) {
            return false;
        }

        return array(array($beanData, $changedFields, $changedInvitees));
    }
    /**
     * @inheritDoc
     */
    public function export(&$data, \CalDavEventCollection $collection)
    {
        $isChanged = false;
        list($beanData, $changedFields, $invitees) = $data;
        list($action, $beanModuleName, $beanId, $rootBeanId, $recurringParam) = $beanData;

        if ($action == 'delete' && !$rootBeanId) {
            return static::DELETE;
        }
        if ($action == 'delete' && $rootBeanId) {
            $index = array_search($beanId, $collection->getSugarChildrenOrder());
            if ($index === false) {
                throw new ExportException('Can not find recurrence-id');
            }
            $recurrenceId = $collection->getAllChildrenRecurrenceIds();
            $recurrenceId = array_splice($recurrenceId, $index, 1);
            $recurrenceId = current($recurrenceId);
            if ($collection->deleteChild($recurrenceId)) {
                return static::SAVE;
            }
            return static::NOTHING;
        }
        if ($collection->deleted) {
            return static::NOTHING;
        }

        $event = $this->getCurrentEvent($collection, $rootBeanId, $beanId);
        if (!$event || $event->isDeleted()) {
            return static::NOTHING;
        }

        // checking before values
        if ($action == 'update') {
            if (isset($changedFields['name']) && count($changedFields['name']) == 2 && !$this->checkCalDavTitle($changedFields['name'][1], $event)) {
                throw new ExportException("Conflict with CalDav Title field");
            }
            if (isset($changedFields['description']) && count($changedFields['description']) == 2 && !$this->checkCalDavDescription($changedFields['description'][1], $event)) {
                throw new ExportException("Conflict with CalDav Description field");
            }
            if (isset($changedFields['location']) && count($changedFields['location']) == 2 && !$this->checkCalDavLocation($changedFields['location'][1], $event)) {
                throw new ExportException("Conflict with CalDav Location field");
            }
            if (isset($changedFields['status']) && count($changedFields['status']) == 2 && !$this->checkCalDavStatus($changedFields['status'][1], $event)) {
                throw new ExportException("Conflict with CalDav Status field");
            }
            if (isset($changedFields['date_start']) && count($changedFields['date_start']) == 2 && !$this->checkCalDavStartDate($changedFields['date_start'][1], $event)) {
                throw new ExportException("Conflict with CalDav Start Date field");
            }
            if (isset($changedFields['date_end']) && count($changedFields['date_end']) == 2 && !$this->checkCalDavEndDate($changedFields['date_end'][1], $event)) {
                throw new ExportException("Conflict with CalDav End Date field");
            }
            if ($invitees && !$this->checkCalDavInvitees($invitees, $event)) {
                throw new ExportException("Conflict with CalDav Invitees");
            }
            if (!$rootBeanId && !$this->checkCalDavRecurring($changedFields, $collection)) {
                throw new ExportException("Conflict with CalDav recurring params");
            }
        }

        // setting values
        if (isset($changedFields['name'])) {
            if ($this->setCalDavTitle($changedFields['name'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['name']);
            }
        }
        if (isset($changedFields['description'])) {
            if ($this->setCalDavDescription($changedFields['description'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['description']);
            }
        }
        if (isset($changedFields['location'])) {
            if ($this->setCalDavLocation($changedFields['location'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['location']);
            }
        }
        if (isset($changedFields['status'])) {
            if ($this->setCalDavStatus($changedFields['status'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['status']);
            }
        }
        if (isset($changedFields['date_start'])) {
            if ($this->setCalDavStartDate($changedFields['date_start'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['date_start']);
            }
        }
        if (isset($changedFields['date_end'])) {
            if ($this->setCalDavEndDate($changedFields['date_end'][0], $event)) {
                $isChanged = true;
            } else {
                unset($data[1]['date_end']);
            }
        }
        $changes = $this->setCalDavInvitees($invitees, $event, $action == 'override');
        if ($changes) {
            $isChanged = true;
            $data[2] = $changes;
        } else {
            $data[2] = array();
        }
        if (!$rootBeanId) {
            if ($recurringParam) {
                if ($this->setCalDavRecurring($recurringParam, $collection)) {
                    $isChanged = true;
                } else {
                    $data[0][3] = null;
                    foreach (RecurringHelper::$recurringFieldList as $recurringFieldName) {
                        unset($data[1][$recurringFieldName]);
                    }
                    unset($data[1]['repeat_parent_id']);
                }
            } elseif ($collection->getRRule()) {
                $collection->resetChildrenChanges();
            }
        }

        if ($isChanged) {
            return static::SAVE;
        }
        return static::NOTHING;
    }

    /**
     * @inheritDoc
     * @param array|false $previousData
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false)
    {
        $action = 'override';
        if ($previousData) {
            $action = array_shift($previousData);
            if ($previousData) {
                return $collection->getDiffStructure(current($previousData));
            }
        }
        if ($action == 'delete') {
            $sugarChildren = $collection->getSugarChildrenOrder();
            if ($sugarChildren) {
                $recurrenceIds = $collection->getAllChildrenRecurrenceIds();
                $result = array();
                foreach ($sugarChildren as $position => $sugarId) {
                    $recurrenceId = array_shift($recurrenceIds);
                    $result[] = array(
                        array(
                            $action,
                            $collection->id,
                            $sugarChildren,
                            $recurrenceId->asDb(),
                            $position,
                        ),
                        array(),
                        array(),
                    );
                }
                return $result;
            } else {
                return array(
                    array(
                        array(
                            $action,
                            $collection->id,
                            null,
                            null,
                            null,
                        ),
                        array(),
                        array(),
                    ),
                );
            }
        }
        return $collection->getDiffStructure('');
    }

    /**
     * @inheritDoc
     */
    public function import(&$data, \SugarBean $bean)
    {
        /**@var \Meeting $bean*/
        $isChanged = false;
        list($beanData, $changedFields, $invitees) = $data;
        list($action, $beanId, $childEventsId, $recurrenceId, $recurrenceIndex) = $beanData;

        if ($action == 'delete' && !$bean->deleted) {
            return static::DELETE;
        }
        if ($action != 'restore' && $bean->deleted) {
            return static::NOTHING;
        }

        // checking before values
        if ($action == 'update') {
            if (isset($changedFields['title']) && count($changedFields['title']) == 2 && !$this->checkBeanName($changedFields['title'][1], $bean)) {
                throw new ImportException("Conflict with Bean Name field");
            }
            if (isset($changedFields['description']) && count($changedFields['description']) == 2 && !$this->checkBeanDescription($changedFields['description'][1], $bean)) {
                throw new ImportException("Conflict with Bean Description field");
            }
            if (isset($changedFields['location']) && count($changedFields['location']) == 2 && !$this->checkBeanLocation($changedFields['location'][1], $bean)) {
                throw new ImportException("Conflict with Bean Location field");
            }
            if (isset($changedFields['status']) && count($changedFields['status']) == 2 && !$this->checkBeanStatus($changedFields['status'][1], $bean)) {
                throw new ImportException("Conflict with Bean Status field");
            }
            if (isset($changedFields['date_start']) && count($changedFields['date_start']) == 2 && !$this->checkBeanStartDate($changedFields['date_start'][1], $bean)) {
                throw new ImportException("Conflict with Bean Start Date field");
            }
            if (isset($changedFields['date_end']) && count($changedFields['date_end']) == 2 && !$this->checkBeanEndDate($changedFields['date_end'][1], $bean)) {
                throw new ImportException("Conflict with Bean End Date field");
            }
            if ($invitees && !$this->checkBeanInvitees($invitees, $bean)) {
                throw new ImportException("Conflict with Bean Invitees");
            }
            if (isset($changedFields['rrule']) && !$this->checkBeanRecurrence($changedFields['rrule'], $bean)) {
                throw new ImportException("Conflict with Bean recurrence");
            }
        }

        $bean->inviteesBefore = \CalendarUtils::getInvitees($bean);

        // setting values
        if (isset($changedFields['title'])) {
            if ($this->setBeanName($changedFields['title'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['title']);
            }
        }
        if (isset($changedFields['description'])) {
            if ($this->setBeanDescription($changedFields['description'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['description']);
            }
        }
        if (isset($changedFields['location'])) {
            if ($this->setBeanLocation($changedFields['location'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['location']);
            }
        }
        if (isset($changedFields['status'])) {
            if ($this->setBeanStatus($changedFields['status'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['status']);
            }
        }
        if (isset($changedFields['date_start'])) {
            if ($this->setBeanStartDate($changedFields['date_start'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['date_start']);
            }
        }
        if (isset($changedFields['date_end'])) {
            if ($this->setBeanEndDate($changedFields['date_end'][0], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['date_end']);
            }
        }
        $changes = $this->setBeanInvitees($invitees, $bean, $action == 'override' || $action == 'restore');
        if ($changes) {
            $isChanged = true;
            if ($action != 'restore') {
                $data[2] = $changes;
            }
        } elseif ($action != 'restore') {
            $data[2] = array();
        }
        if (isset($changedFields['rrule'])) {
            if ($this->setBeanRecurrence($changedFields['rrule'], $bean)) {
                $isChanged = true;
            } elseif ($action != 'restore') {
                unset($data[1]['rrule']);
            }
        } elseif (!$recurrenceId && $bean->repeat_type) {
            $calendarEvents = $this->getCalendarEvents();
            $calendarEvents->saveRecurringEvents($bean);
        }

        if ($action == 'restore' && $bean->deleted) {
            return static::RESTORE;
        }
        if ($isChanged) {
            return static::SAVE;
        }
        return static::NOTHING;
    }

    /**
     * @inheritDoc
     */
    public function verifyImportAfterExport($exportData, $importData, \CalDavEventCollection $collection)
    {
        if (!$importData) {
            return false;
        }
        list($exportBean, $exportFields, $exportInvitees) = $exportData;
        list($importBean, $importFields, $importInvitees) = $importData;

        if ($exportBean[0] == 'delete' && $importBean[0] == 'delete') {
            return false;
        }

        $this->filterFieldsOnVerify($exportFields, $importFields);

        foreach ($importFields as $field => $diff) {
            if (isset($diff[1])) {
                continue;
            }
            if ($diff[0] === null) {
                unset($importFields[$field]);
            }
        }

        foreach ($importInvitees as $action => $list) {
            if (empty($exportInvitees[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($exportInvitees[$action] as $exportInvitee) {
                    if ($exportInvitee[0] == $importInvitee[0] && $exportInvitee[1] == $importInvitee[1] && $exportInvitee[2] == $importInvitee[2]) {
                        unset($importInvitees[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$importInvitees[$action]) {
                unset($importInvitees[$action]);
            }
        }

        if ($importFields || $importInvitees) {
            return array(
                $importBean,
                $importFields,
                $importInvitees,
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function verifyExportAfterImport($importData, $exportData, \SugarBean $bean)
    {
        list($exportBean, $exportFields, $exportInvitees) = $exportData;
        list($importBean, $importFields, $importInvitees) = $importData;

        if ($importBean[0] == 'delete' && $exportBean[0] == 'delete') {
            return false;
        }

        $this->filterFieldsOnVerify($exportFields, $importFields);

        foreach ($exportFields as $field => $diff) {
            if (count($diff) > 1) {
                continue;
            }
            if ($diff[0] === null) {
                unset($exportFields[$field]);
            }
        }

        foreach ($exportInvitees as $action => $list) {
            if (empty($importInvitees[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($importInvitees[$action] as $exportInvitee) {
                    if ($exportInvitee[0] == $importInvitee[0] && $exportInvitee[1] == $importInvitee[1] && $exportInvitee[2] == $importInvitee[2]) {
                        unset($exportInvitees[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$exportInvitees[$action]) {
                unset($exportInvitees[$action]);
            }
        }

        if ($exportFields || $exportInvitees) {
            return array(
                $exportBean,
                $exportFields,
                $exportInvitees,
            );
        }

        return false;
    }

    /**
     * Filter fields on verifyExportAfterImport and verifyImportAfterExport.
     *
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

        if (isset($exportFields['repeat_unit']) && isset($importFields['rrule']['byday'])) {
            $monthlyDayMap = new CalDavStatus\MonthlyDayMap();
            if (count($importFields['rrule']['byday'][0]) == 1) {
                $weekDay = substr($importFields['rrule']['byday'][0][0], -2);
                if ($exportFields['repeat_unit'][0] == $monthlyDayMap->getSugarValue($weekDay)) {
                    unset($exportFields['repeat_unit']);
                    unset($importFields['rrule']['byday']);
                }
            } else {
                unset($exportFields['repeat_unit']);
                unset($importFields['rrule']['byday']);
            }
        }

        if (isset($exportFields['repeat_days']) && isset($importFields['rrule']['bymonthday'])) {
            $aDays = explode(',', $exportFields['repeat_days'][0]);
            if ($aDays == $importFields['rrule']['bymonthday'][0]) {
                unset($exportFields['repeat_days']);
                unset($importFields['rrule']['bymonthday']);
            }
        }

        if (isset($exportFields['repeat_ordinal']) && isset($importFields['rrule']['bysetpos'])) {
            $map = new CalDavStatus\DayPositionMap();
            if ($exportFields['repeat_ordinal'][0] == $map->getSugarValue($importFields['rrule']['bysetpos'][0][0])) {
                unset($exportFields['repeat_ordinal']);
                unset($importFields['rrule']['bysetpos']);
            }
        }
        unset($exportFields['repeat_selector']);

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
            if (array_key_exists('before', $fieldValues)) {
                $dataDiff[$field][1] = $fieldValues['before'];
            }
        }
        return $dataDiff;
    }

    /**
     * Get Event to work
     * @param \CalDavEventCollection $collection
     * @param $rootBeanId
     * @param $beanId
     * @return null|Event
     */
    protected function getCurrentEvent(\CalDavEventCollection $collection, $rootBeanId, $beanId)
    {
        if (!$rootBeanId) {
            return $collection->getParent();
        }

        $sugarChildrenIds = $collection->getSugarChildrenOrder();
        $eventIndex = array_search($beanId, $sugarChildrenIds);

        if ($eventIndex === false) {
            return null;
        }

        $recurrenceIds = array_values($collection->getAllChildrenRecurrenceIds());
        if (count($recurrenceIds) < $eventIndex) {
            return null;
        }
        $recurrenceId = array_slice($recurrenceIds, $eventIndex, 1);
        $recurrenceId = current($recurrenceId);

        return $collection->getChild($recurrenceId);
    }

    /**
     * @inheritDoc
     */
    public function getBeanForImport(\SugarBean $bean, \CalDavEventCollection $calDavBean, $importData)
    {
        list($beanData, $changedFields, $invitees) = $importData;
        list($action, $beanId, $childEventsId, $recurrenceId, $recurrenceIndex) = $beanData;

        if (is_null($recurrenceIndex)) {
            return $bean;
        }

        if (!$childEventsId) {
            $childEventsId = $calDavBean->getSugarChildrenOrder();
        }

        if ($childEventsId) {
            return \BeanFactory::getBean($bean->module_name, $childEventsId[$recurrenceIndex], array(
                'strict_retrieve' => true,
                'deleted' => false,
            ));
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
     * Checks that invitees are applicable to current ones.
     *
     * @param array $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavInvitees($value, Event $event)
    {
        if (isset($value['added'])) {
            foreach ($value['added'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[2]) != -1) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[2]) == - 1) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invitee) {
                if ($event->findParticipantsByEmail($invitee[2]) == -1) {
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

        if (isset($value['repeat_days'][1])) {
            $aDays = explode(',', $value['repeat_days'][1]);
            if ($aDays != $currentRule->getByMonthDay()) {
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
     * Sets provided invitees to specified event.
     *
     * @param array $value
     * @param Event $event
     * @param bool $override
     * @return bool
     */
    protected function setCalDavInvitees(array $value, Event $event, $override = false)
    {
        $result = false;
        $participantHelper = $this->getParticipantHelper();

        if ($override) {
            $indexes = array();
            $value['changed'] = array();
            $value['deleted'] = array();
            if (isset($value['added'])) {
                foreach ($value['added'] as $k => $invitee) {
                    $index = $event->findParticipantsByEmail($invitee[2]);
                    if ($index != - 1) {
                        $indexes[] = $index;
                        $value['changed'][] = $invitee;
                        unset($value['added'][$k]);
                    }
                }
            }
            foreach ($event->getParticipants() as $k => $participant) {
                if (!in_array($k, $indexes)) {
                    $value['deleted'][] = array($participant->getBeanName(), $participant->getBeanId(), $participant->getEmail());
                }
            }
            $value = array_filter($value);
        }

        if (isset($value['added'])) {
            foreach ($value['added'] as $k => $invitee) {
                if ($event->setParticipant($participantHelper->sugarArrayToParticipant($invitee))) {
                    $result = true;
                } else {
                    unset($value['added'][$k]);
                }
            }
            if ($value['added']) {
                $value['added'] = array_values($value['added']);
            } else {
                unset($value['added']);
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $k => $invitee) {
                if ($event->setParticipant($participantHelper->sugarArrayToParticipant($invitee))) {
                    $result = true;
                } else {
                    unset($value['changed'][$k]);
                }
            }
            if ($value['changed']) {
                $value['changed'] = array_values($value['changed']);
            } else {
                unset($value['changed']);
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $k => $invitee) {
                if ($event->deleteParticipant($invitee[2])) {
                    $result = true;
                } else {
                    unset($value['deleted'][$k]);
                }
            }
            if ($value['deleted']) {
                $value['deleted'] = array_values($value['deleted']);
            } else {
                unset($value['deleted']);
            }
        }
        $participantsCount = count($event->getParticipants());
        if (!$event->getOrganizer() && $participantsCount && $GLOBALS['current_user'] instanceof \User) {
            $email = $GLOBALS['current_user']->emailAddress->getPrimaryAddress($GLOBALS['current_user']);
            $organizer = $participantHelper->sugarArrayToParticipant(array(
                $GLOBALS['current_user']->module_name,
                $GLOBALS['current_user']->id,
                $email,
                'accept',
                $GLOBALS['current_user']->full_name,
            ));
            $event->setOrganizer($organizer);
        }

        if ($result) {
            return $value;
        }
        return false;
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
            $collection->resetChildrenChanges();
            $collection->setSugarChildrenOrder(array());
            return $collection->setRRule(null);
        }

        $rRule = $this->getRecurringHelper()->arrayToRRule($value);
        $isChanged = $collection->setRRule($rRule);
        if ($isChanged) {
            $collection->resetChildrenChanges();
        }
        return $isChanged;
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
     * Checks that invitees are applicable to current ones.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanInvitees($value, \SugarBean $bean)
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
        $dateStart = null;
        if ($bean->date_start) {
            $dateStart = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start)->asDb();
        }
        if ($value != $dateStart) {
            $bean->date_start = $value;
            if ($bean->date_end) {
                $beanDateStart = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start);
                $beanDateEnd = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end);
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + $diff->days * 24;
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
        $dateEnd = null;
        if ($bean->date_end) {
            $dateEnd = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end)->asDb();
        }
        if ($value != $dateEnd) {
            $bean->date_end = $value;
            if ($bean->date_start) {
                $beanDateStart = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_start);
                $beanDateEnd = $this->getDateTimeHelper()->sugarDateToUTC($bean->date_end);
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + $diff->days * 24;
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
        $dayPositionMap = new CalDavStatus\DayPositionMap();
        $monthlyDayMap = new CalDavStatus\MonthlyDayMap();

        if (isset($value['frequency'][1]) && $bean->repeat_type !=  $frequencyMap->getSugarValue($value['frequency'][1])) {
            return false;
        }

        if (isset($value['interval'][1]) && $bean->repeat_interval != $value['interval'][1]) {
            return false;
        }

        if (isset($value['count'][1]) && $bean->repeat_count != $value['count'][1]) {
            return false;
        }

        $untilDate =
            $bean->repeat_until ? $this->getDateTimeHelper()->sugarDateToUTC($bean->repeat_until)->asDbDate() : '';
        if (isset($value['until'][1]) && $untilDate != $value['until'][1]) {
            return false;
        }

        if (isset($value['byday'][1])) {
            if ($bean->repeat_type == 'Monthly') {
                $daysData = $value['byday'][1];
                if (count($daysData) == 1) {
                    $weekDay = substr($daysData[0], - 2);
                    $dayPosition = substr($daysData[0], 0, strlen($daysData[0]) - 2);
                    if ($bean->repeat_ordinal != $dayPositionMap->getSugarValue($dayPosition) ||
                        $bean->repeat_unit != $monthlyDayMap->getSugarValue($weekDay)
                    ) {
                        return false;
                    }

                }
            } else {
                $sugarValue = '';
                foreach ($value['byday'][1] as $day) {
                    $sugarValue .= $dayMap->getSugarValue($day);
                }
                if ($bean->repeat_dow != $sugarValue) {
                    return false;
                }
            }
        }

        if (isset($value['bymonthday'][1])) {
            $aDays = $bean->repeat_days ? explode(',', $bean->repeat_days) : array();
            if ($aDays != $value['bymonthday'][1]) {
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

        $this->getRecurringHelper()->arrayToBean($value, $bean);

        $calendarEvents->saveRecurringEvents($bean);

        return true;
    }

    /**
     * Sets provided invitees to specified bean.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @param bool $override
     * @return array|false applied changes to $value or false is nothing was changed
     */
    protected function setBeanInvitees(array $value, \SugarBean $bean, $override = false)
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
                    $existingLinks[$existingBean->module_name][$existingBean->id] = $existingBean->emailAddress->getPrimaryAddress($existingBean);
                }
            }
        }
        if ($override) {
            $value['changed'] = array();
            $value['deleted'] = array();
            $indexes = array();
            if (isset($value['added'])) {
                foreach ($value['added'] as $k => $invitee) {
                    if (isset($existingLinks[$invitee[0]][$invitee[1]])) {
                        $indexes[] = array($invitee[0], $invitee[1]);
                        $value['changed'][] = $invitee;
                        unset($value['added'][$k]);
                    }
                }
            }
            foreach ($existingLinks as $moduleName => $ids) {
                foreach ($ids as $id => $email) {
                    $found = false;
                    foreach ($indexes as $index) {
                        if ($index[0] == $moduleName && $index[1] == $id) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $value['deleted'][] = array($moduleName, $id, $email);
                    }
                }
            }
            $value = array_filter($value);
        }

        $map = new CalDavStatus\AcceptedMap();
        if (isset($value['added'])) {
            foreach ($value['added'] as $k => $invitee) {
                $participant = \BeanFactory::getBean($invitee[0], $invitee[1], array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($invitee[3]));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                    $result = true;
                } else {
                    unset($value['added'][$k]);
                }
            }
            if ($value['added']) {
                $value['added'] = array_values($value['added']);
            } else {
                unset($value['added']);
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $k => $invitee) {
                $participant = \BeanFactory::getBean($invitee[0], $invitee[1], array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($invitee[3]));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                    $result = true;
                } else {
                    unset($value['changed'][$k]);
                }
            }
            if ($value['changed']) {
                $value['changed'] = array_values($value['changed']);
            } else {
                unset($value['changed']);
            }
        }

        if (!$bean->assigned_user_id && $GLOBALS['current_user'] instanceof \User) {
            $bean->assigned_user_id = $GLOBALS['current_user']->id;
        }

        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $k => $invitee) {
                if (isset($existingLinks[$invitee[0]][$invitee[1]])) {
                    unset($existingLinks[$invitee[0]][$invitee[1]]);
                    $result = true;
                } else {
                    unset($value['deleted'][$k]);
                }
            }
            if ($value['deleted']) {
                $value['deleted'] = array_values($value['deleted']);
            } else {
                unset($value['deleted']);
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

        if ($result) {
            return $value;
        }
        return false;
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
