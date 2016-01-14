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

/**
 * Class for processing Calls by iCal protocol
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Calls extends AdapterAbstract
{
    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     * @param \Call|\SugarBean $bean
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false)
    {
        $data = parent::prepareForExport($bean, $previousData);
        if ($data) {
            foreach ($data as &$item) {
                if (isset($item[1]['location'])) {
                    unset($item[1]['location']);
                    if (!$item[1] && !$item[2]) {
                        $item = null;
                    }
                }
            }
            unset($item);
            $data = array_filter($data);
        }
        return $data;
    }

    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     * @param \Call|\SugarBean $bean
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false)
    {
        $data = parent::prepareForImport($collection, $previousData);
        if ($data) {
            foreach ($data as &$item) {
                if (isset($item[1]['location'])) {
                    unset($item[1]['location']);
                    if (!$item[1] && !$item[2]) {
                        $item = null;
                    }
                }
            }
            unset($item);
            $data = array_filter($data);
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function export(&$data, \CalDavEventCollection $collection)
    {
        $isChanged = false;
        list($beanData, $changedFields, $invitees) = $data;
        list($beanModuleName, $beanId, $repeatParentId, $recurringParam, $action) = $beanData;

        if ($action == 'delete' && !$repeatParentId) {
            return static::DELETE;
        }
        if ($collection->deleted) {
            return static::NOTHING;
        }

        $event = $this->getCurrentEvent($collection, $repeatParentId, $beanId);
        if (!$event) {
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
            if (!$repeatParentId && !$this->checkCalDavRecurring($changedFields, $collection)) {
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
        if (!$repeatParentId && $recurringParam) {
            if ($this->setCalDavRecurring($recurringParam, $collection)) {
                $isChanged = true;
            } else {
                $data[0][3] = null;
                unset($data[1]['repeat_type']);
                unset($data[1]['repeat_interval']);
                unset($data[1]['repeat_dow']);
                unset($data[1]['repeat_until']);
                unset($data[1]['repeat_count']);
                unset($data[1]['repeat_parent_id']);
            }
        }

        if ($isChanged) {
            return static::SAVE;
        }
        return static::NOTHING;
    }

    /**
     * @inheritDoc
     */
    public function import(&$data, \SugarBean $bean)
    {
        /**@var \Call $bean*/
        $isChanged = false;
        list($beanData, $changedFields, $invitees) = $data;
        list($beanId, $childEventsId, $recurrenceId, $recurrenceIndex, $action) = $beanData;

        if ($action == 'delete' && !$recurrenceId) {
            return static::DELETE;
        }
        if ($bean->deleted) {
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
            } else {
                unset($data[1]['title']);
            }
        }
        if (isset($changedFields['description'])) {
            if ($this->setBeanDescription($changedFields['description'][0], $bean)) {
                $isChanged = true;
            } else {
                unset($data[1]['description']);
            }
        }
        if (isset($changedFields['status'])) {
            if ($this->setBeanStatus($changedFields['status'][0], $bean)) {
                $isChanged = true;
            } else {
                unset($data[1]['status']);
            }
        }
        if (isset($changedFields['date_start'])) {
            if ($this->setBeanStartDate($changedFields['date_start'][0], $bean)) {
                $isChanged = true;
            } else {
                unset($data[1]['date_start']);
            }
        }
        if (isset($changedFields['date_end'])) {
            if ($this->setBeanEndDate($changedFields['date_end'][0], $bean)) {
                $isChanged = true;
            } else {
                unset($data[1]['date_end']);
            }
        }
        $changes = $this->setBeanInvitees($invitees, $bean, $action == 'override');
        if ($changes) {
            $isChanged = true;
            $data[2] = $changes;
        } else {
            $data[2] = array();
        }
        if (isset($changedFields['rrule'])) {
            if ($this->setBeanRecurrence($changedFields['rrule'], $bean)) {
                $isChanged = true;
            } else {
                unset($data[1]['rrule']);
            }
        }

        if ($isChanged) {
            return static::SAVE;
        }
        return static::NOTHING;
    }
}
