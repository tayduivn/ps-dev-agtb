<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Trigger\Job;

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Trigger\Client;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper;

/**
 * Class RecreateUserRemindersJob deletes all reminders by specified user
 * and sets it again with new "reminder_time" value. This job runs when user changes
 * "reminder_time" in preferences.
 * @package Sugarcrm\Sugarcrm\Trigger\Job
 */
class RecreateUserRemindersJob implements RunnableInterface
{
    protected $userId;

    /**
     * @param string $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Deletes call's and meeting's reminders by user
     * and sets them with new reminder time. It's not
     * affected to calls and meetings in which user is author.
     *
     * @return string
     */
    public function run()
    {
        $beans = array_merge(
            $this->loadBeans('Calls', $this->userId),
            $this->loadBeans('Meetings', $this->userId)
        );
        $user = $this->getBean('Users', $this->userId);
        $this->recreateReminders($beans, $user);
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * Deletes reminders by user and sets reminders to specified user again.
     *
     * @param \Call[]|\Meeting[] $beans
     * @param \User $user
     */
    protected function recreateReminders(array $beans, \User $user)
    {
        $manager = $this->getReminderManager();
        $manager->deleteReminders($user);
        foreach ($beans as $item) {
            $reminderTime = Helper::calculateReminderDateTime($item, $user);
            if ($reminderTime && Helper::isInFuture($reminderTime)) {
                $manager->addReminderForUser($item, $user, $reminderTime);
            }
        }
    }

    /**
     * Returns Scheduler or TriggerServer depends on is trigger server configured.
     *
     * @return Scheduler|TriggerServer
     */
    protected function getReminderManager()
    {
        if ($this->getTriggerClient()->isConfigured()) {
            return $this->getTriggerServerManager();
        } else {
            return $this->getSchedulerManager();
        }
    }

    /**
     * Returns the array of calls or meetings needed to recreate user's reminders.
     *
     * @param string $module
     * @param string $userId
     * @return \Call[]|\Meeting[]
     */
    protected function loadBeans($module, $userId)
    {
        $bean = $this->getBean($module);
        $query = $this->makeLoadBeansSugarQuery($bean, $userId);
        $objects = $bean->fetchFromQuery($query);
        return $objects;
    }

    /**
     * Makes SugarQuery for finding calls or meetings.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param string $userId
     * @return \SugarQuery
     */
    protected function makeLoadBeansSugarQuery(\SugarBean $bean, $userId)
    {
        $query = $this->getSugarQuery();
        $query->from($bean);
        $usersAlias = $query->join('users')->joinName();
        $query->where()
            ->queryAnd()
            ->equals($usersAlias . '.id', $userId)
            ->notEquals('assigned_user_id', $userId)
            ->gt('date_start', $this->getTimeDate()->getNow()->asDb());

        return $query;
    }

    /**
     * Factory method for Client class.
     *
     * @return Client
     * @codeCoverageIgnore
     */
    protected function getTriggerClient()
    {
        return Client::getInstance();
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

    /**
     * Factory method for \SugarBean class.
     *
     * @param string $module
     * @param string $beanId
     * @return \Call|\Meeting|\User
     * @codeCoverageIgnore
     */
    protected function getBean($module, $beanId = null)
    {
        return \BeanFactory::getBean($module, $beanId);
    }

    /**
     * Factory method for TriggerServer class.
     *
     * @return TriggerServer
     * @codeCoverageIgnore
     */
    protected function getTriggerServerManager()
    {
        return new TriggerServer();
    }

    /**
     * Factory method for Scheduler class.
     *
     * @return Scheduler
     * @codeCoverageIgnore
     */
    protected function getSchedulerManager()
    {
        return new Scheduler();
    }

    /**
     * Access method for \TimeDate object.
     * @return \TimeDate
     * @codeCoverageIgnore
     */
    protected function getTimeDate()
    {
        return \TimeDate::getInstance();
    }
}
