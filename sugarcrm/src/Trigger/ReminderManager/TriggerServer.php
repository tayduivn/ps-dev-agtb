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

use Sugarcrm\Sugarcrm\Trigger\Client;

/**
 * Class TriggerServer manages reminders by trigger server.
 * It adds trigger to trigger server for every user from Call or Meeting.
 * Method uses @see \Call::users_arr or @see \Meeting::users_arr as source of users.
 * When time comes the trigger server sends HTTP request to @see \ReminderApi.
 *
 * For setting up reminders use @see TriggerServer::setReminders()
 *
 * For deleting reminders use @see TriggerServer::deleteReminders()
 *
 * Examples:
 * <code>
 * // instantiate manager
 * $manager = new TriggerServer();
 *
 * // set new reminders from Call
 * $manager->setReminders($call, false);
 *
 * // delete old reminders and set new reminders from Call
 * $manager->setReminders($call, true);
 *
 * // delete reminders from Call
 * $manager->deleteReminders($call);
 *
 * </code>
 *
 * @package Sugarcrm\Sugarcrm\Trigger\ReminderManager
 */
class TriggerServer extends Base
{
    const CALLBACK_URL = 'rest/v10/reminder';

    /**
     * @var Client
     */
    private $triggerClient;

    /**
     * @inheritdoc
     */
    public function setReminders(\SugarBean $bean, $isUpdate)
    {
        if ($isUpdate) {
            $this->deleteReminders($bean);
        }
        $this->addReminders($bean);
    }

    /**
     * @inheritdoc
     */
    public function deleteReminders(\SugarBean $bean)
    {
        $this->deleteByTag($this->makeTag($bean));
    }

    /**
     * @inheritdoc
     */
    public function addReminderForUser(\SugarBean $bean, \User $user)
    {
        $reminderTime = $this->getReminderTime($bean, $user);

        if ($reminderTime > 0) {
            $this->createReminder($bean, $user, $reminderTime);
        }
    }

    /**
     * Adds triggers to trigger server. Method adds one trigger for every user.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     */
    protected function addReminders(\SugarBean $bean)
    {
        foreach ($this->loadUsers($bean->users_arr) as $user) {
            $this->addReminderForUser($bean, $user);
        }
    }

    /**
     * Adds one trigger to trigger server.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @param int $reminderTime
     */
    protected function createReminder(\SugarBean $bean, \User $user, $reminderTime)
    {
        $id = $bean->id . '-' . $user->id;
        $time = $this->prepareReminderDateTime($bean->date_start, $reminderTime)
            ->format('Y-m-d\TH:i:s');
        $args = $this->prepareTriggerArgs($bean, $user);
        $tags = $this->prepareTags($bean, $user);

        $this->getTriggerClient()->push($id, $time, 'post', static::CALLBACK_URL, $args, $tags);
    }

    /**
     * Removes trigger from trigger server by trigger tag.
     *
     * @param string $tag
     */
    protected function deleteByTag($tag)
    {
        $this->getTriggerClient()->deleteByTags(array($tag));
    }

    /**
     * Prepares trigger tags.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @return array
     */
    protected function prepareTags(\SugarBean $bean, \User $user)
    {
        return array(
            $this->makeTag($bean),
            $this->makeTag($user),
        );
    }

    /**
     * Factory method for Client class.
     *
     * @return Client
     */
    protected function getTriggerClient()
    {
        if (!$this->triggerClient) {
            $this->triggerClient = Client::getInstance();
        }

        return $this->triggerClient;
    }
}
