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

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;
use Sugarcrm\Sugarcrm\Notification\Dispatcher;

/**
 * Class Emitter
 * Reminder Emitter provides possibility to remind about events.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Reminder
 */
class Emitter implements EmitterInterface
{
    /**
     * Get an Event by a given string.
     *
     * @inheritDoc
     */
    public function getEventPrototypeByString($eventString)
    {
        switch ($eventString) {
            case 'reminder':
                $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Event');
                return new $class();
            default:
                throw new \LogicException("Unsupported eventString:{$eventString}");
        }
    }

    /**
     * Get all event strings for Calls module.
     *
     * @inheritDoc
     */
    public function getEventStrings()
    {
        return array('reminder');
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'Reminder';
    }

    /**
     * Handle event reminder. Reminding about upcoming events(call or meetings)
     *
     * @param \SugarBean $bean
     * @param \User $user
     */
    public function reminder(\SugarBean $bean, \User $user)
    {
        $event = $this->getEventPrototypeByString('reminder');

        $event->setBean($bean)
            ->setUser($user);

        $this->getDispatcher()->dispatch($event);
    }

    /**
     * Return  Dispatcher.
     *
     * @return Dispatcher
     */
    protected function getDispatcher()
    {
        return new Dispatcher();
    }
}
