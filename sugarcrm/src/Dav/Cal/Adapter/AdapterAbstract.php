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

use \Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper as ParticipantsHelper;
use \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper as DateTimeHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as CalDavStatus;
use \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException;
use \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException;

/**
 * Abstract class for iCal adapters common functionality
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
abstract class AdapterAbstract
{
    /**
     * @param \Call|\Meeting|\SugarBean $bean
     * @param array $changedFields
     * @param array $invitesBefore
     * @param array $invitesAfter
     * @param bool|false $forceInsert
     * @return mixed
     */
    public function prepareForExport(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $forceInsert = false
    ) {
        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $repeatParentId = $bean->repeat_parent_id;
        $isUpdated = $bean->isUpdate() && !$forceInsert;
        /**
         * null means nothing changed, otherwise child was changed
         */
        $childEventsId = null;

        if (!$repeatParentId) {
            if ((!$isUpdated && $bean->repeat_type)
                ||
                ($isUpdated && $this->isRecurringChanged($changedFields))
            ) {
                $childEventsId = array();
                $calendarEvents = $this->getCalendarEvents();
                $childEvents = $calendarEvents->getChildrenQuery($bean)->execute();
                foreach ($childEvents as $event) {
                    $childEventsId[] = $event->id;
                }
            }
        }

        if ($isUpdated) {
            $changedFields = $this->getFieldsDiff($changedFields);
        } else {
            $changedFields = $this->getBeanFetchedRow($bean);
        }

        $beanData = array(
            $bean->module_name,
            $bean->id,
            $repeatParentId,
            $childEventsId,
            $isUpdated,
        );

        return array($beanData, $changedFields, $participantsHelper->getInvitesDiff($invitesBefore, $invitesAfter));
    }

    /**
     * return true if one of rucurrig rules was changed
     * @param array $changedFields
     * @return bool
     */
    protected function isRecurringChanged($changedFields)
    {
        $fieldList = array(
            'repeat_type',
            'repeat_interval',
            'repeat_count',
            'repeat_until',
            'repeat_dow',
        );

        if (count(array_intersect(array_keys($changedFields), $fieldList))) {
            return true;
        }
        return false;
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
     * Retrieve bean fetched row
     * @param \SugarBean $bean
     * @return array
     */
    protected function getBeanFetchedRow(\SugarBean $bean)
    {
        $dataDiff = array();
        if (!$bean->fetched_row) {
            $bean->retrieve($bean->id);
        }

        foreach ($bean->fetched_row as $name => $value) {
            $dataDiff[$name] = array(
                0 => $value
            );
        }
        return $dataDiff;
    }

    /**
     * set CalDav title
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavTitle(array $fieldValues, \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent)
    {
        if (isset($fieldValues[1]) && $fieldValues[1] != $calDavEvent->getTitle()) {
            throw new ExportException("Name value conflict with CalDav title: " .
                "'{$fieldValues[1]}' isn't equal '{$calDavEvent->getTitle()}'");
        }
        $calDavEvent->setTitle($fieldValues[0]);
    }

    /**
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavDescription(
        array $fieldValues,
        \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
    ) {
        if (isset($fieldValues[1]) && $fieldValues[1] != $calDavEvent->getDescription()) {
            throw new ExportException("Description value conflict with CalDav: ".
                "'{$fieldValues[1]}' isn't equal '{$calDavEvent->getDescription()}'");
        }
        $calDavEvent->setDescription($fieldValues[0]);
    }

    /**
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavLocation(array $fieldValues, \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent)
    {
        if (isset($fieldValues[1]) && $fieldValues[1] != $calDavEvent->getLocation()) {
            throw new ExportException("Location value conflict with CalDav: " .
                "'{$fieldValues[1]}' isn't equal '{$calDavEvent->getLocation()}'");
        }
        $calDavEvent->setLocation($fieldValues[0]);
    }

    /**
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavStatus(array $fieldValues, \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent)
    {
        $eventStatus = new CalDavStatus\EventMap();
        if (isset($fieldValues[1])) {
            $currentStatus = $eventStatus->getSugarValue($calDavEvent->getStatus(), $fieldValues[1]);
            if ($fieldValues[1] != $currentStatus) {
                throw new ExportException("Status value conflict with CalDav: " .
                    "'{$fieldValues[1]}' isn't equal '{$currentStatus}'");
            }

        }
        $calDavEvent->setStatus($eventStatus->getCalDavValue($fieldValues[0], $calDavEvent->getStatus()));
    }

    /**
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavStartDate(array $fieldValues, \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent)
    {
        if (isset($fieldValues[1])) {
            $dateBefore = new \SugarDateTime($fieldValues[1], new \DateTimeZone('UTC'));
            if ($dateBefore != $calDavEvent->getStartDate()) {
                throw new ExportException("Start date value conflict with CalDav: " .
                    "'{$dateBefore->asDb()}' isn't equal '{$calDavEvent->getStartDate()->asDb()}'");
            }
        }
        $calDavEvent->setStartDate(new \SugarDateTime($fieldValues[0], new \DateTimeZone('UTC')));
    }

    /**
     * @param array $fieldValues
     * @param \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    protected function setCalDavEndDate(array $fieldValues, \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event $calDavEvent)
    {
        if (isset($fieldValues[1])) {
            $dateBefore = new \SugarDateTime($fieldValues[1], new \DateTimeZone('UTC'));
            if ($dateBefore != $calDavEvent->getEndDate()) {
                throw new ExportException("Start date value conflict with CalDav: " .
                    "'{$dateBefore->asDb()}' isn't equal '{$calDavEvent->getEndDate()->asDb()}'");
            }
        }
        $calDavEvent->setEndDate(new \SugarDateTime($fieldValues[0], new \DateTimeZone('UTC')));
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanName(array $fieldValues, \SugarBean $bean)
    {
        if (isset($fieldValues[1]) && $fieldValues[1] != $bean->name) {
            throw new ImportException("Name value conflict with {$bean->module_name}: " .
                "'{$fieldValues[1]}' isn't equal '{$bean->name}'");
        }
        $bean->name = $fieldValues[0];
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanDescription(array $fieldValues, \SugarBean $bean)
    {
        if (isset($fieldValues[1]) && $fieldValues[1] != $bean->description) {
            throw new ImportException("Description value conflict with {$bean->module_name}: " .
                "'{$fieldValues[1]}' isn't equal '{$bean->description}'");
        }
        $bean->description = $fieldValues[0];
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanLocation(array $fieldValues, \SugarBean $bean)
    {
        if (isset($fieldValues[1]) && $fieldValues[1] != $bean->location) {
            throw new ImportException("Location value conflict with {$bean->module_name}: " .
                "'{$fieldValues[1]}' isn't equal '{$bean->location}'");
        }
        $bean->location = $fieldValues[0];
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanStatus(array $fieldValues, \SugarBean $bean)
    {
        $eventStatus = new CalDavStatus\EventMap();
        if (isset($fieldValues[1])) {
            $currentStatus = $eventStatus->getCalDavValue($bean->status, $fieldValues[1]);
            if ($fieldValues[1] != $currentStatus) {
                throw new ImportException("Status value conflict with {$bean->module_name}: " .
                    "'{$fieldValues[1]}' isn't equal '{$currentStatus}'");
            }
        }
        $bean->status = $eventStatus->getSugarValue($fieldValues[0], $bean->status);
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanStartDate(array $fieldValues, \SugarBean $bean)
    {
        if (isset($fieldValues[1])) {
            $dateBefore = new \SugarDateTime(
                $bean->date_start,
                new \DateTimeZone($this->getCurrentUser()->getPreference('timezone'))
            );
            if ($fieldValues[1] != $dateBefore) {
                throw new ImportException("Starts date value conflict with {$bean->module_name}: " .
                    "'{$fieldValues[1]}' isn't equal '{$bean->date_start}'");
            }
        }
        $bean->date_start =  $fieldValues[0]->asDb();
    }

    /**
     * @param array $fieldValues
     * @param \SugarBean $bean
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function setBeanEndDate(array $fieldValues, \SugarBean $bean)
    {
        if (isset($fieldValues[1])) {
            $dateBefore = new \SugarDateTime(
                $bean->date_end,
                new \DateTimeZone($this->getCurrentUser()->getPreference('timezone'))
            );
            if ($fieldValues[1] != $dateBefore) {
                throw new ImportException("End date value conflict with {$bean->module_name}: " .
                    "'{$fieldValues[1]}' isn't equal '{$bean->date_end}'");
            }
        }
        $bean->date_end = $fieldValues[0]->asDb();
    }

    /**
     * @param array $relationInvites
     * @param \SugarBean $bean
     */
    protected function setContactsToBean($relationInvites, \SugarBean $bean)
    {
        $ids = $this->prepareSugarInvitees('contacts', $relationInvites, $bean);
        $bean->setContactInvitees($ids);
    }

    /**
     * @param array $relationInvites
     * @param \SugarBean $bean
     */
    protected function setLeadsToBean($relationInvites, \SugarBean $bean)
    {
        $ids = $this->prepareSugarInvitees('leads', $relationInvites, $bean);
        $bean->setLeadInvitees($ids);
    }

    /**
     * @param array $relationInvites
     * @param \SugarBean $bean
     */
    protected function setUsersToBean($relationInvites, \SugarBean $bean)
    {
        $ids = $this->prepareSugarInvitees('users', $relationInvites, $bean);
        $bean->setUserInvitees($ids);
    }

    /**
     * @param array $addressesInvites
     * @param \SugarBean $bean
     */
    protected function setAddressesToBean($addressesInvites, \SugarBean $bean)
    {
        $ids = $this->prepareSugarInvitees('addresses', $addressesInvites, $bean);
        $bean->setAddresseeInvitees($ids);
    }

    /**
     * @param string $relation
     * @param array $relationInvites
     * @param \SugarBean $bean
     * @return array
     * @throws \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ImportException
     */
    protected function prepareSugarInvitees($relation, $relationInvites, \SugarBean $bean)
    {
        $relationsBeans = array();
        $existsBeansIds = array();
        $newRelationsBeansId = array();
        $deletedIds = array();
        if ($bean->load_relationship($relation)) {
            $bean->$relation->resetLoaded();
            $relationsBeans = $bean->$relation->getBeans();
        }
        foreach ($relationsBeans as $bean) {
            $existsBeansIds[] = $bean->id;
        }

        if (isset($relationInvites['added'])) {
            foreach ($relationInvites['added'] as $invite) {
                $beanId = $invite[1];
                if (in_array($beanId, $existsBeansIds)) {
                    throw new ImportException("{$relation} added error: id '{$beanId}' already exists");
                }
                $newRelationsBeansId[] = $beanId;
            }
        }

        if (isset($relationInvites['changed'])) {
            foreach ($relationInvites['changed'] as $invite) {
                $beanId = $invite[1];
                if (!in_array($beanId, $existsBeansIds)) {
                    throw new ImportException("{$relation} changed error: id '{$beanId}' doesn't exists");
                }
                $newRelationsBeansId[] = $beanId;
            }
        }

        if (isset($relationInvites['deleted'])) {
            foreach ($relationInvites['deleted'] as $invite) {
                $beanId = $invite[1];
                if (!in_array($beanId, $existsBeansIds)) {
                    throw new ImportException("{$relation} deleted error: id '{$beanId}' doesn't exists");
                }
                $deletedIds[] = $beanId;
            }
        }

        foreach ($existsBeansIds as $id) {
            if (!in_array($id, $deletedIds) && !in_array($id, $newRelationsBeansId)) {
                $newRelationsBeansId[] = $id;
            }
        }
        return $newRelationsBeansId;
    }

    /**
     * @param array $invites
     * @param \SugarBean $bean
     */
    protected function setInvitesStatuses($invites, $bean)
    {
        $participantStatuses = new CalDavStatus\AcceptedMap();
        if (isset($invites['added'])) {
            foreach (array_values($invites['added']) as $invitesType) {
                foreach ($invitesType as $addedInvite) {
                    list($beanName, $beanId, $beanStatus, $email, $displayName) = $addedInvite;
                    $acceptedStatus = $participantStatuses->getSugarValue($beanStatus);
                    $participant = \BeanFactory::getBean($beanName, $beanId);
                    $bean->set_accept_status($participant, $acceptedStatus);
                }
            }
        }

        if (isset($invites['changed'])) {
            foreach ($invites['changed'] as $invitesType) {
                foreach ($invitesType as $changedInvite) {
                    list($beanName, $beanId, $beanStatus, $email, $displayName) = $changedInvite;
                    $acceptedStatus = $participantStatuses->getSugarValue($beanStatus);
                    $participant = \BeanFactory::getBean($beanName, $beanId);
                    if ($participant->getStatus() != $acceptedStatus) {
                        $bean->set_accept_status($participant, $acceptedStatus);
                    }
                }
            }
        }
    }

    /**
     * @param array $invitees
     * @param string $inviteBeanName
     * @return array
     */
    protected function getChangedInviteesByModule($invitees, $inviteBeanName)
    {
        $changedInvitees = array();
        if (isset($invitees['added'][$inviteBeanName])) {
            $changedInvitees['added'] = $invitees['added'][$inviteBeanName];
        }
        if (isset($invitees['changed'][$inviteBeanName])) {
            $changedInvitees['changed'] = $invitees['changed'][$inviteBeanName];
        }
        if (isset($invitees['deleted'][$inviteBeanName])) {
            $changedInvitees = $invitees['deleted'][$inviteBeanName];
        }
        return $changedInvitees;
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
}
