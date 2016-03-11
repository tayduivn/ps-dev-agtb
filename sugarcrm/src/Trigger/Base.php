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

namespace Sugarcrm\Sugarcrm\Trigger;

use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper;

/**
 * Base class for working with Trigger.
 *
 * Class Base
 * @package Sugarcrm\Sugarcrm\Trigger
 */
abstract class Base
{
    /**
     * Returns suitable Manager.
     * If trigger client is configured its manager will be returned.
     * In other case scheduler manager will be returned.
     *
     * @return Scheduler|TriggerServer|\Sugarcrm\Sugarcrm\Trigger\ReminderManager\Base
     */
    protected function getReminderManager()
    {

        if (TriggerClient::getInstance()->isConfigured()) {
            return $this->getTriggerServerManager();
        } else {
            return $this->getSchedulerManager();
        }
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
     * Loads users beans by array of id.
     *
     * @param string[] $usersIds
     * @return \User[]
     */
    protected function loadUsers(array $usersIds)
    {
        $bean = \BeanFactory::getBean('Users');
        $query = new \SugarQuery();
        $query->from($bean);
        $query->where()->in('id', $usersIds);
        return $bean->fetchFromQuery($query);
    }

    /**
     * Sets reminders for event(call or meeting).
     *
     * @param \Call|\Meeting|\SugarBean $bean event for which will be set reminders.
     * @param boolean $isUpdate If event was added the $isUpdate is false. Otherwise is true.
     */
    public function setReminders(\SugarBean $bean, $isUpdate)
    {
        $reminderManager = $this->getReminderManager();
        if ($isUpdate) {
            $reminderManager->deleteReminders($bean);
        }
        foreach ($this->loadUsers($bean->users_arr) as $user) {
            $reminderTime = Helper::calculateReminderDateTime($bean, $user);
            if ($reminderTime && Helper::isInFuture($reminderTime)) {
                $reminderManager->addReminderForUser($bean, $user, $reminderTime);
            }
        }
    }

    /**
     * Factory method for JobQueue Manager class.
     *
     * @return Manager
     * @codeCoverageIgnore
     */
    protected function getJobQueueManager()
    {
        return new Manager();
    }
}
