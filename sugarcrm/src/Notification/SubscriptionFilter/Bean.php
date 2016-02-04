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

namespace Sugarcrm\Sugarcrm\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Base SubscriptionFilter class for bean events, contains base functionality.
 *
 * Class SubscriptionFilter/Bean
 * @package Notification
 */
abstract class Bean
{

    /**
     * Base function prepare $query to filter out in child class, should be overridden/augmented.
     *
     * Set $event bean as a $query from bean
     *
     * @param EventInterface $event for which will be filter outed users
     * @param \SugarQuery $query which will be filter out users by some reference
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query)
    {
        if ($query->getFromBean()) {
            throw new \LogicException('From bean should not be set', 1);
        }

        $query->from($event->getBean(), array('team_security' => false));
        $query->where()->equals('id', $event->getBean()->id, $event->getBean());
    }

    /**
     * Base function which only check is event instance of class BeanEvent, should be overridden.
     *
     * @param EventInterface $event for checking is subscription filter support event
     * @return bool is event instance of class BeanEvent
     */
    public function supports(EventInterface $event)
    {
        return $event instanceof BeanEvent;
    }
}
