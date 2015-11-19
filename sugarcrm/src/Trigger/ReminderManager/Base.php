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

namespace Sugarcrm\Sugarcrm\Trigger\ReminderManager;

/**
 * Class Base contains common methods for
 * reminder manager implementations. For using you needed to
 * implement Base::setReminders and
 * Base::deleteReminders methods.
 * @package Sugarcrm\Sugarcrm\Trigger\ReminderManager
 */
abstract class Base
{
    /**
     * Sets reminders.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param boolean $isUpdate If call or meeting was added the $isUpdate is false. Otherwise is true.
     */
    abstract public function setReminders(\SugarBean $bean, $isUpdate);

    /**
     * Deletes reminders.
     *
     * @param \Call|\Meeting|\User|\SugarBean $bean
     */
    abstract public function deleteReminders(\SugarBean $bean);

    /**
     * Adds reminder for certain user.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     */
    abstract public function addReminderForUser(\SugarBean $bean, \User $user);

    /**
     * Creates tag by bean class and id.
     *
     * @param \SugarBean $bean
     * @return string
     */
    protected function makeTag(\SugarBean $bean)
    {
        return strtolower($bean->object_name) . '-' . $bean->id;
    }

    /**
     * Gets reminder time.
     * If user is calls or meetings author
     * the Call::reminder_time or Meeting::reminder_time
     * value will be used. Otherwise the value from
     * User::getPreference('reminder_time') will be used.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @return int
     */
    protected function getReminderTime(\SugarBean $bean, \User $user)
    {
        if ($bean->assigned_user_id == $user->id) {
            return (int)$bean->reminder_time;
        }
        return (int)$user->getPreference('reminder_time');
    }

    /**
     * Calculates reminder date and time for trigger server.
     * It converts result to specified timezone.
     *
     * @param string $dateStart Call or Meeting start datetime
     * @param int $reminderTime Reminder time in seconds
     * @return \DateTime
     */
    protected function prepareReminderDateTime($dateStart, $reminderTime)
    {
        $reminderDateTime = new \DateTime($dateStart, new \DateTimeZone('UTC'));
        $reminderDateTime->modify('- ' . $reminderTime . ' seconds');
        return $reminderDateTime;
    }

    /**
     * Prepares args.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @return array
     */
    protected function prepareTriggerArgs(\SugarBean $bean, \User $user)
    {
        return array(
            'module' => $bean->module_name,
            'beanId' => $bean->id,
            'userId' => $user->id
        );
    }

    /**
     * Loads users beans by array of id.
     *
     * @param string[] $usersIds
     * @return \User[]
     */
    protected function loadUsers(array $usersIds)
    {
        $bean = $this->getBean('Users');
        $query = $this->makeLoadUsersSugarQuery($bean, $usersIds);
        return $bean->fetchFromQuery($query);
    }

    /**
     * Makes SugarQuery for loading users by array of id.
     *
     * @param \User $bean
     * @param string[] $usersIds
     * @return \SugarQuery
     */
    protected function makeLoadUsersSugarQuery(\User $bean, array $usersIds)
    {
        $query = $this->getSugarQuery();
        $query->from($bean);
        $query->where()->in('id', $usersIds);
        return $query;
    }

    /**
     * Factory method for \SugarBean class.
     *
     * @param string $module
     * @param string $beanId
     * @return \Call|\Meeting|\User|\SchedulersJob|\SugarBean
     * @codeCoverageIgnore
     */
    protected function getBean($module, $beanId = null)
    {
        return \BeanFactory::getBean($module, $beanId);
    }

    /**
     * Factory method for \SugarQuery class.
     *
     * @return \SugarQuery
     * @codeCoverageIgnore
     */
    protected function getSugarQuery()
    {
        return new \SugarQuery();
    }
}
