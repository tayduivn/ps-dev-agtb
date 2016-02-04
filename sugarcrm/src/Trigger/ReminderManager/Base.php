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

namespace Sugarcrm\Sugarcrm\Trigger\ReminderManager;

/**
 * Class Base contains common methods for
 * reminder manager implementations. For using you needed to
 * implement Base::addReminderForUser and
 * Base::deleteReminders methods.
 * @package Sugarcrm\Sugarcrm\Trigger\ReminderManager
 */
abstract class Base
{
    /**
     * Deletes reminders for event(call or meeting) or for user.
     *
     * @param \Call|\Meeting|\User|\SugarBean $bean
     */
    abstract public function deleteReminders(\SugarBean $bean);

    /**
     * Adds reminder for certain user.
     *
     * @param \Call|\Meeting|\SugarBean $bean event for which will be set reminder.
     * @param \User $user user for which will be set reminder.
     * @param \DateTime $reminderTime on which time reminder will be set.
     */
    abstract public function addReminderForUser(\SugarBean $bean, \User $user, \DateTime $reminderTime);

    /**
     * Creates tag by bean class and id.
     *
     * @param \SugarBean $bean
     * @return string generated tag.
     */
    protected function makeTag(\SugarBean $bean)
    {
        return strtolower($bean->object_name) . '-' . $bean->id;
    }

    /**
     * Prepares args.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @return array prepared arguments.
     */
    protected function prepareTriggerArgs(\SugarBean $bean, \User $user)
    {
        return array(
            'module' => $bean->module_name,
            'beanId' => $bean->id,
            'userId' => $user->id
        );
    }
}
