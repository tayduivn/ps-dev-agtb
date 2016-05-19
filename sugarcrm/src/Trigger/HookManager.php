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

require_once 'src/Trigger/ReminderManager/Helper.php';

/**
 * Class HookManager handles "after_save", "after_delete" and "after_restore"
 * hooks from @see \Call, @see \Meeting and @see \UserPreference classes. When event emits the appropriate
 * methods are called and reminders are set up or deleted.
 * @package Sugarcrm\Sugarcrm\Trigger
 */
class HookManager extends Base
{

    /**
     * Sets reminders when call or meeting was saved.
     * Handles "after_save" hook.
     *
     * @param \SugarBean $bean
     * @param string $event
     * @param array $arguments (isUpdate => bool, dataChanges => array)
     */
    public function afterCallOrMeetingSave(\SugarBean $bean, $event, array $arguments)
    {
        if ($bean instanceof \Call || $bean instanceof \Meeting) {
            $this->setReminders($bean, $arguments['isUpdate']);
        }
    }

    /**
     * Removes reminders when call or meeting was deleted.
     * Handles "after_delete" hook.
     *
     * @param \SugarBean $bean
     * @param string $event
     * @param array $arguments (id => string)
     */
    public function afterCallOrMeetingDelete(\SugarBean $bean, $event, array $arguments)
    {
        if ($bean instanceof \Call || $bean instanceof \Meeting) {
            $this->getReminderManager()->deleteReminders($bean);
        }
    }

    /**
     * Sets reminders when call or meeting was restored.
     * Handles "after_restore" hook.
     *
     * @param \SugarBean $bean
     * @param string $event
     * @param array $arguments (id => string)
     */
    public function afterCallOrMeetingRestore(\SugarBean $bean, $event, array $arguments)
    {
        if ($bean instanceof \Call || $bean instanceof \Meeting) {
            $this->setReminders($bean, false);
        }
    }

    /**
     * Sets or removes reminders when user's preferences was changed.
     * Handles "after_save" hook.
     *
     * @param \SugarBean $bean
     * @param string $event
     * @param array $arguments (isUpdate => bool, dataChanges => array)
     */
    public function afterUserPreferenceSave(\SugarBean $bean, $event, array $arguments)
    {
        if ($bean instanceof \UserPreference && $bean->category === 'global') {
            if ($this->isReminderTimeChanged($arguments['dataChanges'])) {
                $this->submitRecreateUserRemindersJob($bean->assigned_user_id);
            }
        }
    }

    /**
     * Creates task for recreating triggers for user's Calls and Meetings.
     *
     * @param string $userId
     */
    protected function submitRecreateUserRemindersJob($userId)
    {
        $manager = $this->getJobQueueManager();
        $manager->RecreateUserRemindersJob($userId);
    }

    /**
     * Checks is dataChanges contains "reminder_time" preference
     * and it was changed.
     *
     * @param array $dataChanges
     * @return boolean
     */
    protected function isReminderTimeChanged(array $dataChanges)
    {
        if (isset($dataChanges['contents'])) {
            $preferencesBefore = $this->decodePreferences($dataChanges['contents']['before']);
            $preferencesAfter = $this->decodePreferences($dataChanges['contents']['after']);

            if (is_array($preferencesBefore) && is_array($preferencesAfter)) {
                if (isset($preferencesBefore['reminder_time']) && isset($preferencesAfter['reminder_time'])) {
                    if ($preferencesBefore['reminder_time'] != $preferencesAfter['reminder_time']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Decodes and unserializes data.
     *
     * @param string $data
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function decodePreferences($data)
    {
        return unserialize(base64_decode($data));
    }
}
