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

use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper;

/**
 * Class Reminder is entry point to notify user about call or meeting.
 * @package Sugarcrm\Sugarcrm\Trigger
 */
class Reminder
{

    /**
     * Maximum offset from the specified time
     */
    const MAX_TIME_DIFF = 900;

    /**
     * Do remind.
     *
     * @param string $module
     * @param string $beanId
     * @param string $userId
     */
    public function remind($module, $beanId, $userId)
    {
        $bean = $this->getBean($module, $beanId);
        $user = $this->getBean('Users', $userId);

        if ($this->validate($bean, $user)) {
            $this->getEmitterRegistry()
                ->getModuleEmitter($module)
                ->reminder($bean, $user);
        }
    }

    /**
     * Checks bean and user properties and call's/meeting's date start.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @return bool
     */
    protected function validate(\SugarBean $bean, \User $user)
    {
        $isValid = false;
        $reminderDateTime = Helper::calculateReminderDateTime($bean, $user);
        if ($reminderDateTime) {
            $now = \TimeDate::getInstance()->getNow();
            $diff = abs($reminderDateTime->getTimestamp() - $now->getTimestamp());
            $isValid = ($diff <= self::MAX_TIME_DIFF);
        }

        return $isValid;
    }

    /**
     * Factory method for \Call or \Meeting or \User classes.
     *
     * @param string $module
     * @param string $id
     * @return \Call|\Meeting|\User
     * @codeCoverageIgnore
     */
    protected function getBean($module, $id)
    {
        return \BeanFactory::getBean(
            $module,
            $id,
            array('strict_retrieve' => true, 'disable_row_level_security' => true)
        );
    }

    /**
     * Return emitter registry.
     *
     * @return EmitterRegistry emitter registry
     * @codeCoverageIgnore
     */
    protected function getEmitterRegistry()
    {
        return EmitterRegistry::getInstance();
    }
}
