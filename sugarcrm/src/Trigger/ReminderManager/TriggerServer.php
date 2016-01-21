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
 * It adds trigger to trigger server for user from Call or Meeting.
 * When time comes the trigger server sends HTTP request to @see \ReminderApi.
 *
 * For setting up reminders use @see TriggerServer::addReminderForUser()
 * For deleting reminders use @see TriggerServer::deleteReminders()
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
    public function deleteReminders(\SugarBean $bean)
    {
        $tag = $this->makeTag($bean);
        $this->getTriggerClient()->deleteByTags(array($tag));
    }

    /**
     * Adds one trigger to trigger server.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @param \DateTime $reminderTime
     */
    public function addReminderForUser(\SugarBean $bean, \User $user, \DateTime $reminderTime)
    {
        $id = $bean->id . '-' . $user->id;
        $formattedTime = $reminderTime->format('Y-m-d\TH:i:s');
        $args = $this->prepareTriggerArgs($bean, $user);
        $tags = $this->prepareTags($bean, $user);
        $this->getTriggerClient()->push($id, $formattedTime, 'post', static::CALLBACK_URL, $args, $tags);
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
