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

use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;

/**
 * Query builder which filter out users by on which should be reminded.
 *
 * class SubscriptionFilter/Reminder
 * @package Notification
 */
class Reminder extends Bean implements SubscriptionFilterInterface
{
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'Reminder';
    }

    /**
     * @inheritDoc
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query)
    {
        parent::filterQuery($event, $query);

        $usersLink = $query->join('users', array('team_security' => false));

        $query->where()->equals($usersLink->joinName() . '.id', $event->getUser()->id, $event->getUser());
        return $usersLink->joinName();
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 500;
    }

    /**
     * @inheritDoc
     */
    public function supports(EventInterface $event)
    {
        return $event instanceof ReminderEvent;
    }
}
