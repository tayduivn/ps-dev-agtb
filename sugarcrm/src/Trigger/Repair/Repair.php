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

namespace Sugarcrm\Sugarcrm\Trigger\Repair;

use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;
use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Trigger\Base;
use Sugarcrm\Sugarcrm\Util\Runner\RunnableInterface;

/**
 * Repair & rebuild task to reset triggers.
 *
 * Class Repair
 * @package Sugarcrm\Sugarcrm\Trigger\Repair
 */
class Repair extends Base implements RunnableInterface
{
    const EVENT_STRING_REMINDER = 'reminder';

    /**
     * Return traversable list of events bean for rebuild.
     *
     * @return \Traversable
     */
    public function getBeans()
    {
        $appendIterator = new \AppendIterator();
        foreach ($this->getReminderModules() as $module) {
            $appendIterator->append($this->getBeanIterator($module));
        }

        return $appendIterator;
    }

    /**
     * Returns list of modules with reminder event.
     *
     * @return string[] list of modules.
     */
    protected function getReminderModules()
    {
        $reminderModules = array();
        $emitterRegistry = $this->getEmitterRegistry();
        foreach ($emitterRegistry->getModuleEmitters() as $module) {
            $emitter = $emitterRegistry->getModuleEmitter($module);
            $eventStrings = $emitter->getEventStrings();
            if (in_array(static::EVENT_STRING_REMINDER, $eventStrings)) {
                $event = $emitter->getEventPrototypeByString(static::EVENT_STRING_REMINDER);
                if ($event instanceof ReminderEvent) {
                    $reminderModules[] = $module;
                }
            }
        }
        return $reminderModules;
    }

    /**
     * Get object of EmitterRegistry.
     *
     * @return EmitterRegistry
     */
    protected function getEmitterRegistry()
    {
        return EmitterRegistry::getInstance();
    }

    /**
     * Create new BeanIterator.
     *
     * @param string $module
     * @return BeanIterator
     */
    protected function getBeanIterator($module)
    {
        return new BeanIterator($module);
    }

    /**
     * Deletes legacy reminders and re-add in server that's has been setup.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     */
    public function execute(\SugarBean $bean)
    {
        if (TriggerClient::getInstance()->isConfigured()) {
            $this->getSchedulerManager()->deleteReminders($bean);
        }
        $this->setReminders($bean, true);
    }
}
