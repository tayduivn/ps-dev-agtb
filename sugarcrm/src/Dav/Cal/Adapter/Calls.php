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
     */
    public function prepareForExport(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $insert = false
    )
    {
        $data = parent::prepareForExport($bean, $changedFields, $invitesBefore, $invitesAfter, $insert);
        if ($data && isset($data[1]['location'])) {
            unset($data[1]['location']);
            if (!$data[1] && !$data[2]) {
                return false;
            }
        }

        return $data;
    }

    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData)
    {
        $data = parent::prepareForImport($collection, $previousData);
        if ($data && isset($data[1]['location'])) {
            unset($data[1]['location']);
            if (!$data[1] && !$data[2]) {
                return false;
            }
        }
        return $data;
    }


    /**
     * Updates caldav bean and returns true if anything was changed
     *
     * @param array $data
     * @param \CalDavEventCollection $collection
     * @return bool
     * @throws ExportException if conflict has been found
     */
    public function export(array $data, \CalDavEventCollection $collection)
    {
        $isChanged = false;
        list($beanData, $changedFields, $invites) = $data;
        list($beanModuleName, $beanId, $repeatParentId, $recurringParam, $insert) = $beanData;

        $event = $this->getCurrentEvent($collection, $repeatParentId, $beanId);
        if (!$event) {
            return false;
        }

        // checking before values
        if (!$insert) {
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
            if ($invites && !$this->checkCalDavInvites($invites, $event)) {
                throw new ExportException("Conflict with CalDav Invites");
            }
            if (!$repeatParentId && !$this->checkCalDavRecurring($changedFields, $collection)) {
                throw new ExportException("Conflict with CalDav recurring params");
            }
        }

        // setting values
        if (isset($changedFields['name'])) {
            $isChanged = $isChanged | $this->setCalDavTitle($changedFields['name'][0], $event);
        }
        if (isset($changedFields['description'])) {
            $isChanged = $isChanged | $this->setCalDavDescription($changedFields['description'][0], $event);
        }
        if (isset($changedFields['status'])) {
            $isChanged = $isChanged | $this->setCalDavStatus($changedFields['status'][0], $event);
        }
        if (isset($changedFields['date_start'])) {
            $isChanged = $isChanged | $this->setCalDavStartDate($changedFields['date_start'][0], $event);
        }
        if (isset($changedFields['date_end'])) {
            $isChanged = $isChanged | $this->setCalDavEndDate($changedFields['date_end'][0], $event);
        }
        if ($invites) {
            $isChanged = $isChanged | $this->setCalDavInvites($invites, $event);
        }

        if (!$repeatParentId && $recurringParam) {
            $isChanged = $isChanged | $this->setCalDavRecurring($recurringParam, $collection, $insert);
        }

        return (bool)$isChanged;
    }

    /**
     * Updates bean and returns true if anything was changed
     *
     * @param array $data
     * @param \SugarBean $bean
     * @return bool
     * @throws ImportException if conflict has been found
     */
    public function import(array $data, \SugarBean $bean)
    {
        /**@var \Call $bean*/
        $isChanged = false;
        list($beanData, $changedFields, $invites) = $data;
        list($beanId, $childEventsId, $insert) = $beanData;

        // checking before values
        if (!$insert) {
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
            if ($invites && !$this->checkBeanInvites($invites, $bean)) {
                throw new ImportException("Conflict with Bean Invites");
            }
        }

        $bean->invitesBefore = \CalendarUtils::getInvites($bean);

        // setting values
        if (isset($changedFields['title'])) {
            $isChanged |= $this->setBeanName($changedFields['title'][0], $bean);
        }
        if (isset($changedFields['description'])) {
            $isChanged |= $this->setBeanDescription($changedFields['description'][0], $bean);
        }
        if (isset($changedFields['status'])) {
            $isChanged |= $this->setBeanStatus($changedFields['status'][0], $bean);
        }
        if (isset($changedFields['date_start'])) {
            $isChanged |= $this->setBeanStartDate($changedFields['date_start'][0], $bean);
        }
        if (isset($changedFields['date_end'])) {
            $isChanged |= $this->setBeanEndDate($changedFields['date_end'][0], $bean);
        }
        if ($invites) {
            $isChanged |= $this->setBeanInvites($invites, $bean);
        }

        return $isChanged;
    }
}
