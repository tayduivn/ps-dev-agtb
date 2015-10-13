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

namespace Sugarcrm\Sugarcrm\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Query builder which filter out users by on which assigned bean from event.
 *
 * class SubscriptionFilter/AssignedToMe
 * @package Notification
 */
class AssignedToMe extends Bean implements SubscriptionFilterInterface
{

    /**
     * Function return string label from $app_string in which hold subscription filter name.
     *
     * @return string label from app_string
     */
    public function __toString()
    {
        return 'AssignedToMe';
    }

    /**
     * Join users in which assigned event Bean and return name of the join table
     *
     * @param EventInterface $event for which bean filter outed assigned users
     * @param \SugarQuery $query which will be filter out users assigned to $event bean
     * @return string alias of the user table in query
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query)
    {
        parent::filterQuery($event, $query);

        $assignedUserLink = $query->join('assigned_user_link', array('team_security' => false));
        return $assignedUserLink->joinName();
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 1000;
    }

    /**
     * Function return is Subscription Filter support current event.
     *
     * In this case we check is $event bean can be assigned to user
     *
     * @param EventInterface $event for checking is subscription filter support event
     * @return bool is event supported by subscription filter
     */
    public function supports(EventInterface $event)
    {
        if (!parent::supports($event)) {
            return false;
        }
        $objectName = $event->getBean()->object_name;
        return array_key_exists('templates', $GLOBALS['dictionary'][$objectName])
        && is_array($GLOBALS['dictionary'][$objectName]['templates'])
        && in_array('assignable', $GLOBALS['dictionary'][$objectName]['templates']);
    }
}
