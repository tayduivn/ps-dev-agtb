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

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter as ReminderEmitter;

/**
 * Class MeetingEmitter
 * MeetingEmitter provides possibility to detect event which has happened in meeting module.
 *
 * @method reminder(SugarBean $bean, User $user)
 */
class MeetingEmitter implements EmitterInterface
{
    /**
     * Reminder Emitter which handle remind events.
     * @var \Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter
     */
    protected $reminderEmitter;

    /**
     * @param ReminderEmitter|null $reminderEmitter
     */
    public function __construct(ReminderEmitter $reminderEmitter = null)
    {
        if (is_null($reminderEmitter)) {
            $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Emitter');
            $reminderEmitter = new $class();
        }
        $this->reminderEmitter = $reminderEmitter;
    }

    /**
     * Return name of module in which emitter work.
     *
     * @return string name of module
     */
    public function __toString()
    {
        return 'Meetings';
    }

    /**
     * Get an Event by a given string for Meetings module.
     *
     * {@inheritdoc}
     */
    public function getEventPrototypeByString($eventString)
    {
        return $this->reminderEmitter->getEventPrototypeByString($eventString);
    }

    /**
     * Get all event strings for Meetings module.
     *
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        return $this->reminderEmitter->getEventStrings();
    }

    /**
     * Throw an method call to the reminder Emitter.
     *
     * @param string $name method name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array(array($this->reminderEmitter, $name), $arguments);
    }
}
