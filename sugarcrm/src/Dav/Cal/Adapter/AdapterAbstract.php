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
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as CalDavStatus;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event;

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
     * @param bool $insert
     * @return mixed
     */
    public function prepareForExport(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $insert = false
    ) {
        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $repeatParentId = $bean->repeat_parent_id;
        /**
         * null means nothing changed, otherwise child was changed
         */
        $childEventsId = null;

        if (!$repeatParentId) {
            if (($insert && $bean->repeat_type)
                ||
                (!$insert && $this->isRecurringChanged($changedFields))
            ) {
                $childEventsId = array();
                $calendarEvents = $this->getCalendarEvents();
                $childEvents = $calendarEvents->getChildrenQuery($bean)->execute();
                foreach ($childEvents as $event) {
                    $childEventsId[] = $event->id;
                }
            }
        }

        if (!$insert) {
            $changedFields = $this->getFieldsDiff($changedFields);
        } else {
            $changedFields = $this->getBeanFetchedRow($bean);
        }

        $beanData = array(
            $bean->module_name,
            $bean->id,
            $repeatParentId,
            $childEventsId,
            $insert,
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
            foreach ($value['added'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) != -1) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) == - 1) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) == -1) {
                    return false;
                }
            }
        }
        return false;
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
        if ($value != $event->getTitle()) {
            $event->setTitle($value);
            return true;
        }
        return false;
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
        if ($value != $event->getDescription()) {
            $event->setDescription($value);
            return true;
        }
        return false;
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
        if ($value != $event->getLocation()) {
            $event->setLocation($value);
            return true;
        }
        return false;
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

        if ($value != $event->getStatus()) {
            $event->setStatus($value);
            return true;
        }
        return false;
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

        if ($value != $event->getStartDate()) {
            $event->setStartDate($value);
            return true;
        }
        return false;
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

        if ($value != $event->getEndDate()) {
            $event->setEndDate($value);
            return true;
        }
        return false;
    }

    /**
     * Sets provided invites to specified event.
     *
     * @param array $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavInvites(array $value, Event $event)
    {
        $result = false;
        $participantHelper = $this->getParticipantHelper();

        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                $event->setParticipant($participantHelper->inviteToParticipant($invite));
                $result = true;
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                $event->setParticipant($participantHelper->inviteToParticipant($invite));
                $result = true;
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                $event->deleteParticipant($invite[3]);
                $result = true;
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
        $beanDate = new \SugarDateTime(
            $bean->date_start,
            new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
        );
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
        $beanDate = new \SugarDateTime(
            $bean->date_end,
            new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
        );
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
                $bean->$link->resetLoaded();
                foreach ($bean->$link->getBeans() as $existingBean) {
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }

            }
        }

        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                if (isset($existingLinks[$invite[0]][$invite[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                if (!isset($existingLinks[$invite[0]][$invite[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if (!isset($existingLinks[$invite[0]][$invite[1]])) {
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
                $beanDateStart = new \SugarDateTime(
                    $value,
                    new \DateTimeZone('UTC')
                );
                $beanDateEnd = new \SugarDateTime(
                    $bean->date_end,
                    new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
                );
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->m;
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
                $beanDateStart = new \SugarDateTime(
                    $bean->date_start,
                    new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
                );
                $beanDateEnd = new \SugarDateTime(
                    $value,
                    new \DateTimeZone('UTC')
                );
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->m;
            }
            return true;
        }
        return false;
    }

    /**
     * Sets provided invites to specified bean.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanInvites(array $value, \SugarBean $bean)
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
                $bean->$link->resetLoaded();
                foreach ($bean->$link->getBeans() as $existingBean) {
                    if (!isset($existingLinks[$existingBean->module_name])) {
                        $existingLinks[$existingBean->module_name] = array();
                    }
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }
            }
        }

        $map = new CalDavStatus\AcceptedMap();
        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invite;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invite;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if (isset($existingLinks[$invite[0]][$invite[1]])) {
                    unset($existingLinks[$invite[0]][$invite[1]]);
                    $result = true;
                }
            }
            foreach ($existingLinks as $module => $ids) {
                if (method_exists($bean, 'set' . substr($module, 0, -1) . 'invitees')) {
                    call_user_func(array($bean, 'set' . substr($module, 0, -1) . 'invitees'), array_keys($ids));
                }
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
}
