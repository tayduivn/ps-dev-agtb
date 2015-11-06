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
 * Query builder which filter out users which in team set of $event bean.
 *
 * class Team
 * @package Notification
 */
class Team extends Bean implements SubscriptionFilterInterface
{

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'Team';
    }

    /**
     * Join users which in team set of event Bean and return name of the join table
     *
     * @param EventInterface $event for which bean filter outed users form team set
     * @param \SugarQuery $query which will be filter out users which in team set of $event bean
     * @return string alias of the user table in query
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query)
    {
        parent::filterQuery($event, $query);
        $teams = $query->join('teams', array('team_security' => false));
        $query->joinTable('team_memberships', array('alias' => 'jt3_team_memberships', 'joinType' => 'INNER'))
            ->on()
            ->equalsField('jt3_team_memberships.team_id', "{$teams->joinName()}.id")
            ->equals('jt3_team_memberships.deleted', 0);
        $query->joinTable('users', array('alias' => 'jt4_users', 'joinType' => 'INNER'))
            ->on()
            ->equalsField('jt4_users.id', 'jt3_team_memberships.user_id')
            ->equals('jt4_users.deleted', 0);
        return 'jt4_users';
    }

    /**
     * @inheritdoc
     *
     * Team subscription filterInterface filter should be less prioritized then AssignedToMe,
     * therefore, the function returns 2000
     *
     */
    public function getOrder()
    {
        return 2000;
    }

    /**
     * Function return is Subscription Filter support current event.
     *
     * Check is $event bean uses team security logic
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
        && in_array('team_security', $GLOBALS['dictionary'][$objectName]['templates']);
    }
}
